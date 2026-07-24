<?php

namespace Tests\Unit\Portal;

use App\Application\Data\CandidateOptionData;
use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use App\Application\Data\TerritoryData;
use App\Application\Portal\SurveyRoundDetailFactory;
use App\Domain\Survey\RoundAvailability;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class SurveyRoundDetailFactoryTest extends TestCase
{
    public function test_it_ranks_options_by_votes_for_the_live_count(): void
    {
        $territory = new TerritoryData(
            id: 'territory-1',
            officialCode: '070103',
            name: 'Carmen de la Legua-Reynoso',
            slug: 'carmen-de-la-legua-reynoso',
            scopeType: 'district',
        );

        $result = new RoundResult(
            state: RoundAvailability::Active,
            territory: $territory,
            round: new SurveyRoundData(
                id: 'round-1',
                territory: $territory,
                roundNumber: 1,
                electionCycle: '2026',
                officeType: 'district_mayor',
                title: 'Encuesta distrital de Carmen de la Legua-Reynoso',
                readinessState: 'active',
                blockedReason: null,
                opensAt: CarbonImmutable::parse('2026-07-23 00:00:00', 'America/Lima'),
                closesAt: CarbonImmutable::parse('2026-08-23 00:00:00', 'America/Lima'),
                lastVoteAt: CarbonImmutable::parse('2026-07-24 14:31:00', 'America/Lima'),
                options: [
                    new CandidateOptionData(
                        optionId: 'option-1',
                        candidacyId: 'cand-1',
                        candidateId: 'candidate-1',
                        candidateName: 'Ana Torres',
                        candidatePhotoUrl: null,
                        partyId: 'party-1',
                        partyName: 'Partido Azul',
                        partyAcronym: null,
                        partyLogoUrl: null,
                        displayOrder: 1,
                        voteCount: 2,
                    ),
                    new CandidateOptionData(
                        optionId: 'option-2',
                        candidacyId: 'cand-2',
                        candidateId: 'candidate-2',
                        candidateName: 'Luis Rojas',
                        candidatePhotoUrl: null,
                        partyId: 'party-2',
                        partyName: 'Partido Verde',
                        partyAcronym: null,
                        partyLogoUrl: null,
                        displayOrder: 2,
                        voteCount: 5,
                    ),
                ],
                totalVotes: 7,
            ),
        );

        $payload = (new SurveyRoundDetailFactory())->make($result);

        self::assertSame(7, $payload['total_votes']);
        self::assertSame('2026-07-24T14:31:00-05:00', $payload['round']['last_vote_at']);
        self::assertSame('Luis Rojas', $payload['top_options'][0]['candidate']['name']);
        self::assertSame('Luis Rojas', $payload['ranked_options'][0]['candidate']['name']);
        self::assertSame('Ana Torres', $payload['ranked_options'][1]['candidate']['name']);
    }
}
