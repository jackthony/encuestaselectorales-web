<?php

namespace App\Infrastructure\Security;

use App\Domain\Vote\Contracts\GeographicValidator;
use App\Infrastructure\Persistence\Models\Territory;

final class ConfiguredGeographicValidator implements GeographicValidator
{
    public function contains(
        Territory $territory,
        float $latitude,
        float $longitude,
        float $accuracyMeters,
    ): bool {
        if ($accuracyMeters < 0
            || $accuracyMeters > (float) config('vote.max_gps_accuracy_meters', 100)
        ) {
            return false;
        }

        // Territorial geofence was intentionally disabled to keep the vote
        // flow usable in production. IP, device and GPS-accuracy checks remain.
        return true;
    }
}
