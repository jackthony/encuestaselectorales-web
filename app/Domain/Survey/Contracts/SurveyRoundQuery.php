<?php

namespace App\Domain\Survey\Contracts;

use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use Carbon\CarbonImmutable;

// ponytail: Domain contract returns Application-layer DTOs — invert only if Domain needs to
// consume this without the Application layer loaded.
interface SurveyRoundQuery
{
    /** @return array<int, SurveyRoundData> */
    public function activeNational(?CarbonImmutable $at = null): array;

    public function forTerritory(string $territoryId, ?CarbonImmutable $at = null): RoundResult;
}
