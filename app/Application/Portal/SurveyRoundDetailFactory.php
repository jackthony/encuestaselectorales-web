<?php

namespace App\Application\Portal;

use App\Application\Data\RoundResult;

final readonly class SurveyRoundDetailFactory
{
    /**
     * @return array<string, mixed>
     */
    public function make(RoundResult $result): array
    {
        $payload = $result->toArray();
        $round = is_array($payload['round'] ?? null) ? $payload['round'] : null;
        $territory = is_array($payload['territory'] ?? null) ? $payload['territory'] : null;

        $options = is_array($round['options'] ?? null) ? $round['options'] : [];
        $totalVotes = (int) ($round['total_votes'] ?? 0);
        $leader = collect($options)
            ->sortByDesc(fn (array $option): int => (int) ($option['vote_count'] ?? 0))
            ->first();

        return [
            'state' => $payload['state'],
            'reason' => $payload['reason'],
            'territory' => $territory,
            'round' => $round,
            'total_votes' => $totalVotes,
            'leader_name' => is_array($leader) ? (string) ($leader['candidate']['name'] ?? 'Candidatura') : 'Candidatura',
            'leader_votes' => is_array($leader) ? (int) ($leader['vote_count'] ?? 0) : 0,
            'top_options' => array_slice($options, 0, 5),
        ];
    }
}
