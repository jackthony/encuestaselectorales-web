<?php

namespace App\Application\Portal;

final readonly class SurveyShareDescriptionFactory
{
    /**
     * @param  array<string, mixed>|null  $round
     */
    public function forScope(string $scopeLabel, string $territoryName, ?array $round): string
    {
        if (! is_array($round) || ! isset($round['options']) || ! is_array($round['options'])) {
            return "Encuesta electoral de la {$scopeLabel} {$territoryName}.";
        }

        $totalVotes = (int) ($round['total_votes'] ?? 0);
        if ($totalVotes <= 0) {
            return "Encuesta electoral de la {$scopeLabel} {$territoryName}. Sin votos registrados todavía.";
        }

        $leader = collect($round['options'])
            ->sortByDesc(fn (array $option): int => (int) ($option['vote_count'] ?? 0))
            ->first();

        if (! is_array($leader)) {
            return "Encuesta electoral de la {$scopeLabel} {$territoryName}. {$totalVotes} votos emitidos.";
        }

        $leaderName = (string) ($leader['candidate']['name'] ?? 'Candidatura');
        $leaderVotes = (int) ($leader['vote_count'] ?? 0);

        return "Votación actual de la {$scopeLabel} {$territoryName}: {$totalVotes} votos emitidos. Lidera {$leaderName} con {$leaderVotes} votos.";
    }
}
