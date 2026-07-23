<?php

namespace App\Domain\Vote\Contracts;

use App\Infrastructure\Persistence\Models\Territory;

interface GeographicValidator
{
    public function contains(
        Territory $territory,
        float $latitude,
        float $longitude,
        float $accuracyMeters,
    ): bool;
}
