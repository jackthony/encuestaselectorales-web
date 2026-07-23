<?php

namespace App\Application\Data;

use App\Domain\Survey\RoundAvailability;

final readonly class RoundResult
{
    public function __construct(
        public RoundAvailability $state,
        public ?SurveyRoundData $round = null,
        public ?TerritoryData $territory = null,
        public ?string $reason = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'state' => $this->state->value,
            'reason' => $this->reason,
            'territory' => $this->territory?->toArray(),
            'round' => $this->round?->toArray(),
        ];
    }
}
