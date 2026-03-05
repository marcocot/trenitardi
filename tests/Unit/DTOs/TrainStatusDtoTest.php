<?php

namespace Tests\Unit\DTOs;

use App\DTOs\TrainStatusDto;
use App\DTOs\TrainStopDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TrainStatusDtoTest extends TestCase
{
    private function baseApiData(array $overrides = []): array
    {
        return array_merge([
            'numeroTreno' => 9642,
            'compNumeroTreno' => ' FR 9642',
            'origine' => 'REGGIO DI CALABRIA CENTRALE',
            'destinazione' => 'TORINO PORTA NUOVA',
            'compTipologiaTreno' => 'nazionale',
            'circolante' => true,
            'arrivato' => false,
            'nonPartito' => false,
            'ritardo' => 0,
            'compOrarioPartenzaZero' => '08:40',
            'compOrarioArrivoZero' => '19:18',
            'stazioneUltimoRilevamento' => 'BATTIPAGLIA',
            'compOraUltimoRilevamento' => '12:32',
            'compRitardo' => ['in orario'],
            'oraUltimoRilevamento' => 1772710320000,
            'fermate' => [],
        ], $overrides);
    }

    private function makeStop(array $overrides = []): array
    {
        return array_merge([
            'stazione' => 'ROMA TERMINI',
            'id' => 'S08409',
            'tipoFermata' => 'F',
            'programmata' => 1772718120000,
            'effettiva' => null,
            'ritardo' => 0,
            'ritardoPartenza' => 0,
            'ritardoArrivo' => 0,
            'binarioEffettivoPartenzaDescrizione' => null,
            'binarioEffettivoArrivoDescrizione' => null,
            'binarioProgrammatoPartenzaDescrizione' => null,
            'binarioProgrammatoArrivoDescrizione' => null,
            'actualFermataType' => 0,
            'progressivo' => 1,
        ], $overrides);
    }

    public function test_from_array_maps_all_top_level_fields(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData());

        $this->assertSame(9642, $dto->trainNumber);
        $this->assertSame('FR 9642', $dto->trainLabel);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $dto->origin);
        $this->assertSame('TORINO PORTA NUOVA', $dto->destination);
        $this->assertSame('nazionale', $dto->trainType);
        $this->assertTrue($dto->isRunning);
        $this->assertFalse($dto->hasArrived);
        $this->assertFalse($dto->hasNotDeparted);
        $this->assertSame(0, $dto->delay);
        $this->assertSame('08:40', $dto->scheduledDeparture);
        $this->assertSame('19:18', $dto->scheduledArrival);
        $this->assertSame('BATTIPAGLIA', $dto->lastDetectedStation);
        $this->assertSame('12:32', $dto->lastDetectedTime);
        $this->assertSame(1772710320000, $dto->lastDetectedTimestamp);
        $this->assertSame(['in orario'], $dto->delayLabels);
    }

    public function test_from_array_decodes_html_entities_in_delay_label(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'compRitardo' => ['p&#252;nktlich'],
        ]));

        $this->assertSame('pünktlich', $dto->delayLabels[0]);
    }

    public function test_from_array_uses_default_delay_label_when_comp_ritardo_empty(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['compRitardo' => []]));

        $this->assertSame('in orario', $dto->delayLabels[0]);
    }

    public function test_from_array_uses_default_delay_label_when_missing(): void
    {
        $data = $this->baseApiData();
        unset($data['compRitardo']);

        $dto = TrainStatusDto::fromArray($data);

        $this->assertSame('in orario', $dto->delayLabels[0]);
    }

    public function test_from_array_maps_stops_as_dto_instances(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'fermate' => [$this->makeStop(), $this->makeStop(['stazione' => 'FIRENZE', 'id' => 'S002'])],
        ]));

        $this->assertCount(2, $dto->stops);
        $this->assertInstanceOf(TrainStopDto::class, $dto->stops[0]);
        $this->assertSame('ROMA TERMINI', $dto->stops[0]->station);
        $this->assertSame('FIRENZE', $dto->stops[1]->station);
    }

    public function test_from_array_handles_null_optional_fields(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'stazioneUltimoRilevamento' => null,
            'compOraUltimoRilevamento' => null,
            'oraUltimoRilevamento' => null,
        ]));

        $this->assertNull($dto->lastDetectedStation);
        $this->assertNull($dto->lastDetectedTime);
        $this->assertNull($dto->lastDetectedTimestamp);
    }

    public function test_constructor_throws_when_train_number_is_not_positive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrainStatusDto::fromArray($this->baseApiData(['numeroTreno' => 0]));
    }

    public function test_constructor_throws_when_origin_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrainStatusDto::fromArray($this->baseApiData(['origine' => '']));
    }

    public function test_constructor_throws_when_destination_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TrainStatusDto::fromArray($this->baseApiData(['destinazione' => '']));
    }

    public function test_get_status_label_returns_arrivato(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['arrivato' => true]));

        $this->assertSame('Arrivato', $dto->getStatusLabel());
    }

    public function test_get_status_label_returns_non_partito(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['nonPartito' => true]));

        $this->assertSame('Non partito', $dto->getStatusLabel());
    }

    public function test_get_status_label_returns_non_circolante(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['circolante' => false]));

        $this->assertSame('Non circolante', $dto->getStatusLabel());
    }

    public function test_get_status_label_returns_delay_label_when_running(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'circolante' => true,
            'compRitardo' => ['in ritardo'],
        ]));

        $this->assertSame('in ritardo', $dto->getStatusLabel());
    }

    public function test_get_status_label_returns_in_orario_when_delay_labels_empty(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'circolante' => true,
            'compRitardo' => [],
        ]));

        $this->assertSame('in orario', $dto->getStatusLabel());
    }

    public function test_get_delay_class_on_time_for_zero_delay(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['ritardo' => 0]));

        $this->assertSame('on-time', $dto->getDelayClass());
    }

    public function test_get_delay_class_on_time_for_negative_delay(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['ritardo' => -5]));

        $this->assertSame('on-time', $dto->getDelayClass());
    }

    public function test_get_delay_class_slight_delay_for_five_minutes(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['ritardo' => 5]));

        $this->assertSame('slight-delay', $dto->getDelayClass());
    }

    public function test_get_delay_class_moderate_delay_for_fifteen_minutes(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['ritardo' => 15]));

        $this->assertSame('moderate-delay', $dto->getDelayClass());
    }

    public function test_get_delay_class_severe_delay_for_over_fifteen_minutes(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData(['ritardo' => 16]));

        $this->assertSame('severe-delay', $dto->getDelayClass());
    }

    public function test_get_last_passed_stop_returns_most_recent_passed(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'fermate' => [
                $this->makeStop(['stazione' => 'PRIMA', 'id' => 'S001', 'actualFermataType' => 1]),
                $this->makeStop(['stazione' => 'SECONDA', 'id' => 'S002', 'actualFermataType' => 1]),
                $this->makeStop(['stazione' => 'TERZA', 'id' => 'S003', 'actualFermataType' => 0]),
            ],
        ]));

        $last = $dto->getLastPassedStop();

        $this->assertNotNull($last);
        $this->assertSame('SECONDA', $last->station);
    }

    public function test_get_last_passed_stop_returns_null_when_none_passed(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'fermate' => [
                $this->makeStop(['stazione' => 'PRIMA', 'id' => 'S001', 'actualFermataType' => 0]),
            ],
        ]));

        $this->assertNull($dto->getLastPassedStop());
    }

    public function test_get_last_passed_stop_returns_null_when_no_stops(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData());

        $this->assertNull($dto->getLastPassedStop());
    }

    public function test_get_next_stop_returns_first_pending(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'fermate' => [
                $this->makeStop(['stazione' => 'PASSATA', 'id' => 'S001', 'actualFermataType' => 1]),
                $this->makeStop(['stazione' => 'PROSSIMA', 'id' => 'S002', 'actualFermataType' => 0]),
                $this->makeStop(['stazione' => 'FUTURA', 'id' => 'S003', 'actualFermataType' => 0]),
            ],
        ]));

        $next = $dto->getNextStop();

        $this->assertNotNull($next);
        $this->assertSame('PROSSIMA', $next->station);
    }

    public function test_get_next_stop_returns_null_when_all_passed(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData([
            'fermate' => [
                $this->makeStop(['stazione' => 'PASSATA', 'id' => 'S001', 'actualFermataType' => 1]),
            ],
        ]));

        $this->assertNull($dto->getNextStop());
    }

    public function test_get_next_stop_returns_null_when_no_stops(): void
    {
        $dto = TrainStatusDto::fromArray($this->baseApiData());

        $this->assertNull($dto->getNextStop());
    }
}
