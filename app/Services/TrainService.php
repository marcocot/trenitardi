<?php

namespace App\Services;

use App\DTOs\TrainSearchResultDto;
use App\DTOs\TrainStatusDto;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class TrainService
{
    private const BASE_URL = 'http://www.viaggiatreno.it/infomobilita/resteasy/viaggiatreno';

    /**
     * Search for a train by number and return its identifiers.
     */
    public function searchTrain(string $trainNumber): ?TrainSearchResultDto
    {
        $response = Http::timeout(10)
            ->get(self::BASE_URL.'/cercaNumeroTrenoTrenoAutocomplete/'.$trainNumber);

        if ($response->failed() || empty($response->body())) {
            return null;
        }

        return TrainSearchResultDto::fromApiResponse($response->body());
    }

    /**
     * Get live train status as a raw API response array.
     *
     * @return array<string, mixed>|null
     */
    public function getRawTrainStatus(string $trainId, int $trainNumber, int $timestamp): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get(self::BASE_URL."/andamentoTreno/{$trainId}/{$trainNumber}/{$timestamp}");

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            return is_array($data) ? $data : null;
        } catch (RequestException) {
            return null;
        }
    }

    /**
     * Get live train status data as a typed DTO.
     */
    public function getTrainStatus(string $trainId, int $trainNumber, int $timestamp): ?TrainStatusDto
    {
        $data = $this->getRawTrainStatus($trainId, $trainNumber, $timestamp);

        return $data !== null ? TrainStatusDto::fromArray($data) : null;
    }
}
