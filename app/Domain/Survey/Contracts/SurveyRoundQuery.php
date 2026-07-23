<?php

namespace App\Domain\Survey\Contracts;

use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use Carbon\CarbonImmutable;

interface SurveyRoundQuery
{
    /** @return array<int, SurveyRoundData> */
    public function activeNational(?CarbonImmutable $at = null): array;

    public function forTerritory(string $territoryId, ?CarbonImmutable $at = null): RoundResult;
}
