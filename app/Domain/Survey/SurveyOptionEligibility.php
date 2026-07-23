<?php

namespace App\Domain\Survey;

use App\Infrastructure\Persistence\Models\Candidacy;
use App\Infrastructure\Persistence\Models\SurveyRound;
use DomainException;

final class SurveyOptionEligibility
{
    public function assertEligible(SurveyRound $round, Candidacy $candidacy): void
    {
        if ($round->territory_id !== $candidacy->territory_id
            || $round->office_type !== $candidacy->office_type
            || $round->election_cycle !== $candidacy->election_cycle
            || $candidacy->status !== 'active'
        ) {
            throw new DomainException(
                'The candidacy does not match the survey territory, office, and election cycle.',
            );
        }
    }
}
