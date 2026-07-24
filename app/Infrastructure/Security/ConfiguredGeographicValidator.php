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

        $bounds = config("vote.territory_bounds.{$territory->official_code}");

        if (! is_array($bounds)) {
            return app()->environment(['local', 'testing']);
        }

        foreach (['lat_min', 'lat_max', 'lng_min', 'lng_max'] as $key) {
            if (! isset($bounds[$key]) || ! is_numeric($bounds[$key])) {
                return false;
            }
        }

        return $latitude >= (float) $bounds['lat_min']
            && $latitude <= (float) $bounds['lat_max']
            && $longitude >= (float) $bounds['lng_min']
            && $longitude <= (float) $bounds['lng_max'];
    }
}
