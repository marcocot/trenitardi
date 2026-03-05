<?php

namespace Tests\Unit\DTOs;

use App\DTOs\TrainStopDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TrainStopDtoTest extends TestCase
{
    private function makeStop(array $overrides = []): TrainStopDto
    {
        return TrainStopDto::fromArray(array_merge([
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
            'binarioProgrammatoPartenzaDescrizione' => '3',
            'binarioProgrammatoArrivoDescrizione' => null,
            'actualFermataType' => 0,
            'progressivo' => 89,
        ], $overrides));
    }

    public function test_from_array_maps_all_fields_correctly(): void
    {
        $stop = TrainStopDto::fromArray([
            'stazione' => 'NAPOLI CENTRALE',
            'id' => 'S09899',
            'tipoFermata' => 'P',
            'programmata' => 1772696400000,
            'effettiva' => 1772697270000,
            'ritardo' => 15,
            'ritardoPartenza' => 15,
            'ritardoArrivo' => 0,
            'binarioEffettivoPartenzaDescrizione' => '1',
            'binarioEffettivoArrivoDescrizione' => '2',
            'binarioProgrammatoPartenzaDescrizione' => '1',
            'binarioProgrammatoArrivoDescrizione' => '3',
            'actualFermataType' => 1,
            'progressivo' => 1,
        ]);

        $this->assertSame('NAPOLI CENTRALE', $stop->station);
        $this->assertSame('S09899', $stop->id);
        $this->assertSame('P', $stop->stopType);
        $this->assertSame(1772696400000, $stop->scheduledTime);
        $this->assertSame(1772697270000, $stop->actualTime);
        $this->assertSame(15, $stop->delay);
        $this->assertSame(15, $stop->departureDelay);
        $this->assertSame(0, $stop->arrivalDelay);
        $this->assertSame('1', $stop->actualDeparturePlatform);
        $this->assertSame('2', $stop->actualArrivalPlatform);
        $this->assertSame('1', $stop->scheduledDeparturePlatform);
        $this->assertSame('3', $stop->scheduledArrivalPlatform);
        $this->assertSame(1, $stop->stopState);
        $this->assertSame(1, $stop->sequence);
    }

    public function test_from_array_handles_null_optional_fields(): void
    {
        $stop = $this->makeStop([
            'programmata' => null,
            'effettiva' => null,
            'binarioEffettivoPartenzaDescrizione' => null,
            'binarioEffettivoArrivoDescrizione' => null,
            'binarioProgrammatoPartenzaDescrizione' => null,
            'binarioProgrammatoArrivoDescrizione' => null,
        ]);

        $this->assertNull($stop->scheduledTime);
        $this->assertNull($stop->actualTime);
        $this->assertNull($stop->actualDeparturePlatform);
        $this->assertNull($stop->actualArrivalPlatform);
        $this->assertNull($stop->scheduledDeparturePlatform);
        $this->assertNull($stop->scheduledArrivalPlatform);
    }

    public function test_from_array_uses_defaults_for_missing_fields(): void
    {
        $stop = TrainStopDto::fromArray([
            'stazione' => 'ROMA',
            'id' => 'S001',
            'tipoFermata' => 'F',
        ]);

        $this->assertSame(0, $stop->delay);
        $this->assertSame(0, $stop->departureDelay);
        $this->assertSame(0, $stop->arrivalDelay);
        $this->assertSame(0, $stop->stopState);
        $this->assertSame(0, $stop->sequence);
    }

    public function test_constructor_throws_when_station_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeStop(['stazione' => '']);
    }

    public function test_constructor_throws_when_id_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeStop(['id' => '']);
    }

    public function test_constructor_throws_when_stop_type_is_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeStop(['tipoFermata' => 'X']);
    }

    public function test_is_passed_returns_true_when_stop_state_is_one(): void
    {
        $stop = $this->makeStop(['actualFermataType' => 1]);

        $this->assertTrue($stop->isPassed());
        $this->assertFalse($stop->isPending());
    }

    public function test_is_pending_returns_true_when_stop_state_is_zero(): void
    {
        $stop = $this->makeStop(['actualFermataType' => 0]);

        $this->assertTrue($stop->isPending());
        $this->assertFalse($stop->isPassed());
    }

    public function test_is_origin_returns_true_for_tipo_p(): void
    {
        $stop = $this->makeStop(['tipoFermata' => 'P']);

        $this->assertTrue($stop->isOrigin());
        $this->assertFalse($stop->isDestination());
    }

    public function test_is_destination_returns_true_for_tipo_a(): void
    {
        $stop = $this->makeStop(['tipoFermata' => 'A']);

        $this->assertTrue($stop->isDestination());
        $this->assertFalse($stop->isOrigin());
    }

    public function test_intermediate_stop_is_neither_origin_nor_destination(): void
    {
        $stop = $this->makeStop(['tipoFermata' => 'F']);

        $this->assertFalse($stop->isOrigin());
        $this->assertFalse($stop->isDestination());
    }
}
