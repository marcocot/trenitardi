<?php

namespace Tests\Unit\Services;

use App\DTOs\TrainSearchResultDto;
use App\DTOs\TrainStatusDto;
use App\Services\TrainService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrainServiceTest extends TestCase
{
    private TrainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrainService;
    }

    // --- searchTrain ---

    public function test_search_train_returns_dto_on_valid_response(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/9642*' => Http::response(
                '9642 - REGGIO DI CALABRIA CENTRALE - 05/03/26|9642-S11781-1772665200000',
                200,
            ),
        ]);

        $result = $this->service->searchTrain('9642');

        $this->assertInstanceOf(TrainSearchResultDto::class, $result);
        $this->assertSame(9642, $result->trainNumber);
        $this->assertSame('S11781', $result->trainId);
        $this->assertSame(1772665200000, $result->timestamp);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $result->origin);
    }

    public function test_search_train_returns_null_on_http_failure(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('', 500),
        ]);

        $result = $this->service->searchTrain('9642');

        $this->assertNull($result);
    }

    public function test_search_train_returns_null_on_empty_body(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('', 200),
        ]);

        $result = $this->service->searchTrain('9642');

        $this->assertNull($result);
    }

    public function test_search_train_returns_null_on_malformed_response(): void
    {
        Http::fake([
            '*cercaNumeroTrenoTrenoAutocomplete/*' => Http::response('not-a-valid-response', 200),
        ]);

        $result = $this->service->searchTrain('9642');

        $this->assertNull($result);
    }

    // --- getRawTrainStatus ---

    public function test_get_raw_train_status_returns_array_on_valid_response(): void
    {
        Http::fake([
            '*andamentoTreno/S11781/9642/*' => Http::response(
                json_encode(['numeroTreno' => 9642, 'origine' => 'REGGIO', 'destinazione' => 'TORINO']),
                200,
            ),
        ]);

        $result = $this->service->getRawTrainStatus('S11781', 9642, 1772665200000);

        $this->assertIsArray($result);
        $this->assertSame(9642, $result['numeroTreno']);
    }

    public function test_get_raw_train_status_returns_null_on_http_failure(): void
    {
        Http::fake([
            '*andamentoTreno/*' => Http::response('', 503),
        ]);

        $result = $this->service->getRawTrainStatus('S11781', 9642, 1772665200000);

        $this->assertNull($result);
    }

    public function test_get_raw_train_status_returns_null_on_non_array_response(): void
    {
        Http::fake([
            '*andamentoTreno/*' => Http::response('"just a string"', 200),
        ]);

        $result = $this->service->getRawTrainStatus('S11781', 9642, 1772665200000);

        $this->assertNull($result);
    }

    public function test_get_raw_train_status_returns_null_on_request_exception(): void
    {
        Http::fake([
            '*andamentoTreno/*' => function () {
                throw new RequestException(new \Illuminate\Http\Client\Response(
                    new \GuzzleHttp\Psr7\Response(500)
                ));
            },
        ]);

        $result = $this->service->getRawTrainStatus('S11781', 9642, 1772665200000);

        $this->assertNull($result);
    }

    // --- getTrainStatus ---

    public function test_get_train_status_returns_dto_on_valid_response(): void
    {
        Http::fake([
            '*andamentoTreno/*' => Http::response(
                json_encode([
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
                    'stazioneUltimoRilevamento' => null,
                    'compOraUltimoRilevamento' => null,
                    'compRitardo' => ['in orario'],
                    'oraUltimoRilevamento' => null,
                    'fermate' => [],
                ]),
                200,
            ),
        ]);

        $result = $this->service->getTrainStatus('S11781', 9642, 1772665200000);

        $this->assertInstanceOf(TrainStatusDto::class, $result);
        $this->assertSame(9642, $result->trainNumber);
        $this->assertSame('REGGIO DI CALABRIA CENTRALE', $result->origin);
    }

    public function test_get_train_status_returns_null_when_raw_data_is_null(): void
    {
        Http::fake([
            '*andamentoTreno/*' => Http::response('', 500),
        ]);

        $result = $this->service->getTrainStatus('S11781', 9642, 1772665200000);

        $this->assertNull($result);
    }
}
