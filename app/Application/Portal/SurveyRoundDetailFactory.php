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
        $rankedOptions = $this->rankOptions($options);

        return [
            'state' => $payload['state'],
            'reason' => $payload['reason'],
            'territory' => $territory,
            'round' => $round,
            'total_votes' => $totalVotes,
            'top_options' => array_slice($rankedOptions, 0, 5),
            'ranked_options' => $rankedOptions,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     * @return array<int, array<string, mixed>>
     */
    private function rankOptions(array $options): array
    {
        usort($options, static function (array $left, array $right): int {
            $leftVotes = (int) ($left['vote_count'] ?? 0);
            $rightVotes = (int) ($right['vote_count'] ?? 0);

            if ($leftVotes !== $rightVotes) {
                return $rightVotes <=> $leftVotes;
            }

            $leftOrder = (int) ($left['display_order'] ?? 0);
            $rightOrder = (int) ($right['display_order'] ?? 0);

            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            return strcmp(
                (string) ($left['candidate']['name'] ?? ''),
                (string) ($right['candidate']['name'] ?? ''),
            ) ?: strcmp(
                (string) ($left['option_id'] ?? ''),
                (string) ($right['option_id'] ?? ''),
            );
        });

        return $options;
    }
}
