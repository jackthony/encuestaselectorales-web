<?php

namespace App\Domain\Vote\Contracts;

use App\Infrastructure\Persistence\Models\Territory;

// ponytail: Domain contract depends on an Infrastructure Eloquent model — invert (Domain-native
// value object) only if this contract needs a second implementation not backed by Territory.
interface GeographicValidator
{
    public function contains(
        Territory $territory,
        float $latitude,
        float $longitude,
        float $accuracyMeters,
    ): bool;
}
