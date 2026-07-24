<?php

namespace App\Application\Portal;

use App\Domain\Survey\RoundAvailability;
use Carbon\CarbonImmutable;

final class OgThumbnailData
{
    private const EYEBROW = 'SONDEO CIUDADANO · PERÚ 2026';

    private const BAR_MAX_WIDTH = 258;

    /**
     * @param  array<string, mixed>  $detail  SurveyRoundDetailFactory::make() output
     * @return array<string, mixed>|null null when there is no active round to render
     */
    public function make(array $detail): ?array
    {
        if (($detail['state'] ?? null) !== RoundAvailability::Active->value) {
            return null;
        }

        $territory = $detail['territory'];
        $round = $detail['round'];
        $totalVotes = (int) $detail['total_votes'];

        $results = [];
        foreach (array_values($detail['top_options']) as $index => $option) {
            $results[] = $this->transformOption($option, $index + 1, $totalVotes);
        }

        return [
            'eyebrow' => self::EYEBROW,
            'title' => $this->scopeLabel($territory['scope_type']).' de '.$territory['name'],
            'subtitle' => $round['title'].' · Ronda '.$round['round_number'],
            'footer_text' => $this->footerText($totalVotes, $round['last_vote_at']),
            'results' => $results,
        ];
    }

    /**
     * @param  array<string, mixed>  $option
     * @return array<string, mixed>
     */
    private function transformOption(array $option, int $position, int $totalVotes): array
    {
        $percentage = $totalVotes > 0 ? ($option['vote_count'] / $totalVotes) * 100 : 0.0;

        return [
            'position' => $position,
            'is_first' => $position === 1,
            'candidate_name' => $option['candidate']['name'],
            'party_name' => $option['party']['name'],
            'percentage_label' => number_format($percentage, 1).'%',
            'votes_label' => number_format((int) $option['vote_count']),
            'bar_width' => (int) round($percentage * self::BAR_MAX_WIDTH / 100),
        ];
    }

    private function footerText(int $totalVotes, ?string $lastVoteAtIso): string
    {
        $base = 'Base: '.number_format($totalVotes).' votos';

        if ($lastVoteAtIso === null) {
            return $base;
        }

        $lastVoteAt = CarbonImmutable::parse($lastVoteAtIso)->setTimezone('America/Lima');

        return $base.' · Actualizado: '.$lastVoteAt->format('d/m/Y H:i');
    }

    private function scopeLabel(string $scopeType): string
    {
        return match ($scopeType) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };
    }
}
