<?php

namespace App\Application\Portal;

use App\Application\Data\SurveyRoundData;

final readonly class SurveyRoundCardFactory
{
    /** @return array<string, mixed> */
    public function make(SurveyRoundData $round): array
    {
        $scopeLabel = $this->scopeLabel($round->territory->scopeType);
        $leader = collect($round->options)
            ->sortByDesc(fn ($option): int => $option->voteCount)
            ->first();
        $territoryScopeRank = match ($round->territory->scopeType) {
            'region' => 1,
            'province' => 2,
            default => 3,
        };

        return [
            'id' => $round->id,
            'territory_scope' => $round->territory->scopeType,
            'territory_slug' => $round->territory->slug,
            'territory_name' => $round->territory->name,
            'territory_code' => $round->territory->officialCode,
            'territory_scope_rank' => $territoryScopeRank,
            'territory_ancestors' => $round->territory->ancestors,
            'titulo' => $round->title,
            'round_number' => $round->roundNumber,
            'fecha_apertura' => $round->opensAt->format('d/m/Y'),
            'fecha_cierre' => $round->closesAt->format('d/m/Y'),
            'scope_label' => "{$scopeLabel} {$round->territory->name}",
            'office_type' => $round->officeType,
            'readiness_state' => $round->readinessState,
            'blocked_reason' => $round->blockedReason,
            'option_count' => count($round->options),
            'total_votes' => $round->totalVotes,
            'leader_name' => $leader?->candidateName,
            'leader_votes' => $leader?->voteCount ?? 0,
            'target_url' => route('surveys.scope', [
                'scope' => $round->territory->scopeType,
                'slug' => $round->territory->slug,
            ]),
        ];
    }

    private function scopeLabel(string $scope): string
    {
        return match ($scope) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };
    }
}
