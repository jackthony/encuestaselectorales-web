<?php

namespace App\Application\Data;

use Carbon\CarbonImmutable;

final readonly class SurveyRoundData
{
    /**
     * @param  array<int, CandidateOptionData>  $options
     */
    public function __construct(
        public string $id,
        public TerritoryData $territory,
        public int $roundNumber,
        public string $electionCycle,
        public string $officeType,
        public string $title,
        public string $readinessState,
        public ?string $blockedReason,
        public CarbonImmutable $opensAt,
        public CarbonImmutable $closesAt,
        public ?CarbonImmutable $lastVoteAt,
        public array $options,
        public int $totalVotes,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'territory' => $this->territory->toArray(),
            'round_number' => $this->roundNumber,
            'election_cycle' => $this->electionCycle,
            'office_type' => $this->officeType,
            'title' => $this->title,
            'readiness_state' => $this->readinessState,
            'blocked_reason' => $this->blockedReason,
            'opens_at' => $this->opensAt->toIso8601String(),
            'closes_at' => $this->closesAt->toIso8601String(),
            'last_vote_at' => $this->lastVoteAt?->toIso8601String(),
            'total_votes' => $this->totalVotes,
            'options' => array_map(
                static fn (CandidateOptionData $option): array => $option->toArray(),
                $this->options,
            ),
        ];
    }
}
