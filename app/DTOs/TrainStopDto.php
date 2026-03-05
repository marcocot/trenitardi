<?php

namespace App\DTOs;

use Webmozart\Assert\Assert;

readonly class TrainStopDto
{
    public function __construct(
        public string $station,
        public string $id,
        public string $stopType,
        public ?int $scheduledTime,
        public ?int $actualTime,
        public ?int $actualArrivalTime,
        public int $delay,
        public int $departureDelay,
        public int $arrivalDelay,
        public ?string $actualDeparturePlatform,
        public ?string $actualArrivalPlatform,
        public ?string $scheduledDeparturePlatform,
        public ?string $scheduledArrivalPlatform,
        public int $stopState,
        public int $sequence,
    ) {
        Assert::notEmpty($station, 'Station name must not be empty.');
        Assert::notEmpty($id, 'Stop ID must not be empty.');
        Assert::oneOf($stopType, ['P', 'F', 'A'], 'Stop type must be one of P (origin), F (intermediate), A (destination).');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            station: $data['stazione'] ?? '',
            id: $data['id'] ?? '',
            stopType: $data['tipoFermata'] ?? 'F',
            scheduledTime: isset($data['programmata']) ? (int) $data['programmata'] : null,
            actualTime: isset($data['effettiva']) ? (int) $data['effettiva'] : null,
            actualArrivalTime: isset($data['arrivoReale']) && is_numeric($data['arrivoReale']) ? (int) $data['arrivoReale'] : null,
            delay: (int) ($data['ritardo'] ?? 0),
            departureDelay: (int) ($data['ritardoPartenza'] ?? 0),
            arrivalDelay: (int) ($data['ritardoArrivo'] ?? 0),
            actualDeparturePlatform: $data['binarioEffettivoPartenzaDescrizione'] ?? null,
            actualArrivalPlatform: $data['binarioEffettivoArrivoDescrizione'] ?? null,
            scheduledDeparturePlatform: $data['binarioProgrammatoPartenzaDescrizione'] ?? null,
            scheduledArrivalPlatform: $data['binarioProgrammatoArrivoDescrizione'] ?? null,
            stopState: (int) ($data['actualFermataType'] ?? 0),
            sequence: (int) ($data['progressivo'] ?? 0),
        );
    }

    public function isPassed(): bool
    {
        return $this->stopState === 1;
    }

    public function isPending(): bool
    {
        return $this->stopState === 0;
    }

    public function isOrigin(): bool
    {
        return $this->stopType === 'P';
    }

    public function isDestination(): bool
    {
        return $this->stopType === 'A';
    }
}
