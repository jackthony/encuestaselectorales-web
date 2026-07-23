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

        return [
            'state' => $payload['state'],
            'reason' => $payload['reason'],
            'territory' => $territory,
            'round' => $round,
            'total_votes' => $totalVotes,
            'top_options' => array_slice($options, 0, 5),
        ];
    }
}
