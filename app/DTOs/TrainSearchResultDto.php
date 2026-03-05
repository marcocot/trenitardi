<?php

namespace App\DTOs;

use Webmozart\Assert\Assert;

readonly class TrainSearchResultDto
{
    public function __construct(
        public int $trainNumber,
        public string $trainId,
        public int $timestamp,
        public string $origin,
    ) {
        Assert::positiveInteger($trainNumber, 'Train number must be a positive integer.');
        Assert::notEmpty($trainId, 'Train ID must not be empty.');
        Assert::positiveInteger($timestamp, 'Timestamp must be a positive integer.');
    }

    /**
     * Parse the autocomplete API response string.
     * Format: "9642 - REGGIO DI CALABRIA CENTRALE - 05/03/26|9642-S11781-1772665200000"
     */
    public static function fromApiResponse(string $body): ?self
    {
        $body = trim($body);
        $parts = explode('|', $body);

        if (count($parts) < 2) {
            return null;
        }

        $trainParts = explode('-', trim($parts[1]));
        if (count($trainParts) < 3) {
            return null;
        }

        $labelParts = explode(' - ', $parts[0]);
        $origin = isset($labelParts[1]) ? trim($labelParts[1]) : '';

        try {
            return new self(
                trainNumber: (int) trim($trainParts[0]),
                trainId: trim($trainParts[1]),
                timestamp: (int) trim($trainParts[2]),
                origin: $origin,
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}
