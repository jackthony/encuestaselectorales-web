<?php

namespace App\Application\Data;

final readonly class RegisterVoteData
{
    public function __construct(
        public string $surveyRoundId,
        public string $surveyOptionId,
        public float $latitude,
        public float $longitude,
        public float $accuracyMeters,
        public int $interactionTimeMs,
        public string $deviceToken,
        public string $browserFingerprint,
        public string $clientIp,
    ) {}
}
