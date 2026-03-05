<?php

namespace Tests\Unit\DTOs;

use App\DTOs\TrainSearchResultDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TrainSearchResultDtoTest extends TestCase
{
    public function test_constructor_sets_all_properties(): void
    {
        $dto = new TrainSearchResultDto(
            trainNumber: 9642,
            trainId: 'S11781',
            timestamp: 1772665200000,
            origin: 'REGGIO DI CALABRIA CENTRALE',
        );

        $this->assertSame(9642, $dto->trainNumber);
        $this->assertSame('S11781', $dto->trainId);
        $this->assertSame(1772665200000, $dto->timestamp);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $dto->origin);
    }

    public function test_constructor_throws_when_train_number_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TrainSearchResultDto(
            trainNumber: 0,
            trainId: 'S11781',
            timestamp: 1772665200000,
            origin: 'ORIGIN',
        );
    }

    public function test_constructor_throws_when_train_number_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TrainSearchResultDto(
            trainNumber: -1,
            trainId: 'S11781',
            timestamp: 1772665200000,
            origin: 'ORIGIN',
        );
    }

    public function test_constructor_throws_when_train_id_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TrainSearchResultDto(
            trainNumber: 9642,
            trainId: '',
            timestamp: 1772665200000,
            origin: 'ORIGIN',
        );
    }

    public function test_constructor_throws_when_timestamp_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TrainSearchResultDto(
            trainNumber: 9642,
            trainId: 'S11781',
            timestamp: 0,
            origin: 'ORIGIN',
        );
    }

    public function test_from_api_response_parses_valid_response(): void
    {
        $body = '9642 - REGGIO DI CALABRIA CENTRALE - 05/03/26|9642-S11781-1772665200000';

        $dto = TrainSearchResultDto::fromApiResponse($body);

        $this->assertNotNull($dto);
        $this->assertSame(9642, $dto->trainNumber);
        $this->assertSame('S11781', $dto->trainId);
        $this->assertSame(1772665200000, $dto->timestamp);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $dto->origin);
    }

    public function test_from_api_response_trims_whitespace(): void
    {
        $body = '  9642 - ORIGIN - 05/03/26|9642-S11781-1772665200000  ';

        $dto = TrainSearchResultDto::fromApiResponse($body);

        $this->assertNotNull($dto);
        $this->assertSame('S11781', $dto->trainId);
    }

    public function test_from_api_response_returns_null_when_pipe_missing(): void
    {
        $dto = TrainSearchResultDto::fromApiResponse('9642 - ORIGIN - 05/03/26');

        $this->assertNull($dto);
    }

    public function test_from_api_response_returns_null_when_train_parts_insufficient(): void
    {
        $dto = TrainSearchResultDto::fromApiResponse('label|9642-S11781');

        $this->assertNull($dto);
    }

    public function test_from_api_response_returns_null_when_assertion_fails(): void
    {
        // trainNumber = 0, which fails positiveInteger assertion
        $dto = TrainSearchResultDto::fromApiResponse('label|0-S11781-1772665200000');

        $this->assertNull($dto);
    }

    public function test_from_api_response_handles_missing_origin(): void
    {
        $body = '9642|9642-S11781-1772665200000';

        $dto = TrainSearchResultDto::fromApiResponse($body);

        $this->assertNotNull($dto);
        $this->assertSame('', $dto->origin);
    }
}
