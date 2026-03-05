<?php

namespace App\DTOs;

use Webmozart\Assert\Assert;

readonly class TrainStatusDto
{
    /**
     * @param  TrainStopDto[]  $stops
     * @param  string[]  $delayLabels
     */
    public function __construct(
        public int $trainNumber,
        public string $trainLabel,
        public string $origin,
        public string $destination,
        public string $trainType,
        public bool $isRunning,
        public bool $hasArrived,
        public bool $hasNotDeparted,
        public int $delay,
        public string $scheduledDeparture,
        public string $scheduledArrival,
        public ?string $lastDetectedStation,
        public ?string $lastDetectedTime,
        public array $delayLabels,
        public array $stops,
        public ?int $lastDetectedTimestamp,
    ) {
        Assert::positiveInteger($trainNumber, 'Train number must be a positive integer.');
        Assert::notEmpty($origin, 'Origin station must not be empty.');
        Assert::notEmpty($destination, 'Destination station must not be empty.');
        Assert::allIsInstanceOf($stops, TrainStopDto::class, 'All stops must be instances of TrainStopDto.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $stops = array_map(
            fn (array $stop) => TrainStopDto::fromArray($stop),
            $data['fermate'] ?? [],
        );

        $compRitardo = $data['compRitardo'] ?? [];
        $delayLabel = is_array($compRitardo) && isset($compRitardo[0])
            ? html_entity_decode($compRitardo[0])
            : 'in orario';

        return new self(
            trainNumber: (int) ($data['numeroTreno'] ?? 0),
            trainLabel: trim($data['compNumeroTreno'] ?? ''),
            origin: $data['origine'] ?? '',
            destination: $data['destinazione'] ?? '',
            trainType: $data['compTipologiaTreno'] ?? '',
            isRunning: (bool) ($data['circolante'] ?? false),
            hasArrived: (bool) ($data['arrivato'] ?? false),
            hasNotDeparted: (bool) ($data['nonPartito'] ?? false),
            delay: (int) ($data['ritardo'] ?? 0),
            scheduledDeparture: $data['compOrarioPartenzaZero'] ?? '',
            scheduledArrival: $data['compOrarioArrivoZero'] ?? '',
            lastDetectedStation: $data['stazioneUltimoRilevamento'] ?? null,
            lastDetectedTime: $data['compOraUltimoRilevamento'] ?? null,
            delayLabels: [$delayLabel],
            stops: $stops,
            lastDetectedTimestamp: $data['oraUltimoRilevamento'] ?? null,
        );
    }

    public function getStatusLabel(): string
    {
        if ($this->hasArrived) {
            return 'Arrivato';
        }

        if ($this->hasNotDeparted) {
            return 'Non partito';
        }

        if (! $this->isRunning) {
            return 'Non circolante';
        }

        return $this->delayLabels[0] ?? 'in orario';
    }

    public function getDelayClass(): string
    {
        if ($this->delay <= 0) {
            return 'on-time';
        }

        if ($this->delay <= 5) {
            return 'slight-delay';
        }

        if ($this->delay <= 15) {
            return 'moderate-delay';
        }

        return 'severe-delay';
    }

    public function getLastPassedStop(): ?TrainStopDto
    {
        $passed = array_filter($this->stops, fn (TrainStopDto $s) => $s->isPassed());

        return ! empty($passed) ? end($passed) : null;
    }

    public function getNextStop(): ?TrainStopDto
    {
        foreach ($this->stops as $stop) {
            if ($stop->isPending()) {
                return $stop;
            }
        }

        return null;
    }
}
