<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Data\CandidateOptionData;
use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Survey\PublicationState;
use App\Domain\Survey\RoundAvailability;
use App\Infrastructure\Persistence\Models\SurveyOption;
use App\Infrastructure\Persistence\Models\SurveyRound;
use App\Infrastructure\Persistence\Models\Territory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentSurveyRoundQuery implements SurveyRoundQuery
{
    public function __construct(private readonly EloquentTerritoryCatalog $territories) {}

    public function activeNational(?CarbonImmutable $at = null): array
    {
        $at ??= CarbonImmutable::now();

        return SurveyRound::query()
            ->where('publication_state', PublicationState::Published->value)
            ->whereIn('readiness_state', [
                RoundAvailability::Active->value,
                RoundAvailability::Blocked->value,
            ])
            ->where('opens_at', '<=', $at)
            ->where('closes_at', '>=', $at)
            ->with($this->roundRelations())
            ->orderBy('closes_at')
            ->get()
            ->map(fn (SurveyRound $round): SurveyRoundData => $this->toData($round))
            ->values()
            ->all();
    }

    public function forTerritory(string $territoryId, ?CarbonImmutable $at = null): RoundResult
    {
        $at ??= CarbonImmutable::now();
        $territory = Territory::query()->with('parent.parent')->find($territoryId);

        if (! $territory) {
            return new RoundResult(RoundAvailability::Unavailable, reason: 'territory_not_found');
        }

        $territoryData = $this->territories->toData($territory);
        $activeRound = $this->activeAt($at)
            ->where('territory_id', $territoryId)
            ->with($this->roundRelations())
            ->latest('opens_at')
            ->first();

        if ($activeRound) {
            if ($activeRound->options->isEmpty()) {
                return new RoundResult(
                    RoundAvailability::Blocked,
                    territory: $territoryData,
                    reason: 'candidate_data_unavailable',
                );
            }

            return new RoundResult(
                RoundAvailability::Active,
                round: $this->toData($activeRound),
                territory: $territoryData,
            );
        }

        $hasCandidates = $territory->candidacies()
            ->where('status', 'active')
            ->whereHas('candidate', fn ($query) => $query->where('status', 'active'))
            ->whereHas('politicalParty', fn ($query) => $query->where('status', 'active'))
            ->exists();

        if (! $hasCandidates) {
            return new RoundResult(
                RoundAvailability::Blocked,
                territory: $territoryData,
                reason: 'candidate_data_unavailable',
            );
        }

        $nextRound = SurveyRound::query()
            ->where('territory_id', $territoryId)
            ->where('publication_state', PublicationState::Published->value)
            ->where('opens_at', '>', $at)
            ->orderBy('opens_at')
            ->first();

        if ($nextRound) {
            return new RoundResult(
                RoundAvailability::Scheduled,
                territory: $territoryData,
                reason: 'round_not_open_yet',
            );
        }

        $closedRound = SurveyRound::query()
            ->where('territory_id', $territoryId)
            ->where('publication_state', PublicationState::Published->value)
            ->where('closes_at', '<', $at)
            ->exists();

        return new RoundResult(
            $closedRound ? RoundAvailability::Closed : RoundAvailability::Unavailable,
            territory: $territoryData,
            reason: $closedRound ? 'round_closed' : 'no_active_round',
        );
    }

    /** @return Builder<SurveyRound> */
    private function activeAt(CarbonImmutable $at): Builder
    {
        return SurveyRound::query()
            ->where('publication_state', PublicationState::Published->value)
            ->where('readiness_state', RoundAvailability::Active->value)
            ->where('opens_at', '<=', $at)
            ->where('closes_at', '>=', $at);
    }

    /** @return array<int, string> */
    private function roundRelations(): array
    {
        return [
            'territory.parent.parent',
            'options' => fn ($query) => $query
                ->where('status', 'eligible')
                ->whereHas('candidacy', fn ($candidacy) => $candidacy
                    ->where('status', 'active')
                    ->whereHas('candidate', fn ($candidate) => $candidate->where('status', 'active'))
                    ->whereHas('politicalParty', fn ($party) => $party->where('status', 'active')))
                ->orderBy('display_order'),
            'options.candidacy.candidate',
            'options.candidacy.politicalParty',
        ];
    }

    private function toData(SurveyRound $round): SurveyRoundData
    {
        return new SurveyRoundData(
            id: (string) $round->getKey(),
            territory: $this->territories->toData($round->territory),
            roundNumber: $round->round_number,
            electionCycle: $round->election_cycle,
            officeType: $round->office_type,
            title: $round->title,
            readinessState: $round->readiness_state->value,
            blockedReason: $round->blocked_reason,
            opensAt: CarbonImmutable::instance($round->opens_at),
            closesAt: CarbonImmutable::instance($round->closes_at),
            options: $round->options
                ->map(static function (SurveyOption $option): CandidateOptionData {
                    $candidacy = $option->candidacy;

                    return new CandidateOptionData(
                        optionId: (string) $option->getKey(),
                        candidacyId: (string) $candidacy->getKey(),
                        candidateId: (string) $candidacy->candidate->getKey(),
                        candidateName: $candidacy->candidate->full_name,
                        candidatePhotoUrl: $candidacy->candidate->photo_url,
                        partyId: (string) $candidacy->politicalParty->getKey(),
                        partyName: $candidacy->politicalParty->name,
                        partyAcronym: $candidacy->politicalParty->acronym,
                        partyLogoUrl: $candidacy->politicalParty->logo_url,
                        displayOrder: $option->display_order,
                    );
                })
                ->all(),
        );
    }
}
