<?php

namespace Tests\Unit\Portal;

use App\Application\Portal\OgThumbnailData;
use Tests\TestCase;

final class OgThumbnailDataTest extends TestCase
{
    public function test_transforms_active_round_with_votes(): void
    {
        $data = (new OgThumbnailData())->make($this->detail(
            totalVotes: 100,
            lastVoteAt: '2026-07-23T19:32:00-05:00',
            options: [
                $this->option('María Quispe', 'AVANZA PAÍS', 70),
                $this->option('Jorge Delgado', 'FUERZA POPULAR', 30),
            ],
        ));

        self::assertNotNull($data);
        self::assertSame('SONDEO CIUDADANO · PERÚ 2026', $data['eyebrow']);
        self::assertSame('Distrito de San Isidro', $data['title']);
        self::assertSame('Encuesta distrital de San Isidro · Ronda 1', $data['subtitle']);
        self::assertSame('Base: 100 votos · Actualizado: 23/07/2026 19:32', $data['footer_text']);

        self::assertCount(2, $data['results']);
        self::assertSame(1, $data['results'][0]['position']);
        self::assertTrue($data['results'][0]['is_first']);
        self::assertSame('María Quispe', $data['results'][0]['candidate_name']);
        self::assertSame('70.0%', $data['results'][0]['percentage_label']);
        self::assertSame('70', $data['results'][0]['votes_label']);
        self::assertSame((int) round(70 * 258 / 100), $data['results'][0]['bar_width']);

        self::assertSame(2, $data['results'][1]['position']);
        self::assertFalse($data['results'][1]['is_first']);
        self::assertSame('30.0%', $data['results'][1]['percentage_label']);
    }

    public function test_round_without_votes_yet_has_zero_percentages_and_no_updated_date(): void
    {
        $data = (new OgThumbnailData())->make($this->detail(
            totalVotes: 0,
            lastVoteAt: null,
            options: [$this->option('María Quispe', 'AVANZA PAÍS', 0)],
        ));

        self::assertNotNull($data);
        self::assertSame('Base: 0 votos', $data['footer_text']);
        self::assertSame('0.0%', $data['results'][0]['percentage_label']);
        self::assertSame(0, $data['results'][0]['bar_width']);
    }

    public function test_returns_null_when_round_is_not_active(): void
    {
        $detail = $this->detail(totalVotes: 0, lastVoteAt: null, options: []);
        $detail['state'] = 'blocked';

        self::assertNull((new OgThumbnailData())->make($detail));
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     * @return array<string, mixed>
     */
    private function detail(int $totalVotes, ?string $lastVoteAt, array $options): array
    {
        return [
            'state' => 'active',
            'reason' => null,
            'territory' => [
                'id' => '01J0000000000000000000001',
                'name' => 'San Isidro',
                'scope_type' => 'district',
            ],
            'round' => [
                'title' => 'Encuesta distrital de San Isidro',
                'round_number' => 1,
                'last_vote_at' => $lastVoteAt,
            ],
            'total_votes' => $totalVotes,
            'top_options' => $options,
            'ranked_options' => $options,
        ];
    }

    /** @return array<string, mixed> */
    private function option(string $candidateName, string $partyName, int $voteCount): array
    {
        return [
            'candidate' => ['name' => $candidateName],
            'party' => ['name' => $partyName],
            'vote_count' => $voteCount,
        ];
    }
}
