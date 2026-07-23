<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InitialSurveyRoundsSeeder extends Seeder
{
    private const ELECTION_CYCLE = 'ERM2026';

    private const SOURCE_SYSTEM = 'initial_survey_rounds';

    /**
     * @var array<int, array{
     *     scope_type:string,
     *     official_code:string,
     *     office_type:string,
     *     source_key:string,
     *     title:string
     * }>
     */
    private const TARGETS = [
        [
            'scope_type' => 'province',
            'official_code' => '150100',
            'office_type' => 'provincial_mayor',
            'source_key' => 'erm2026:province:150100:provincial_mayor:round:1',
            'title' => 'Encuesta provincial de Lima',
        ],
        [
            'scope_type' => 'region',
            'official_code' => '070000',
            'office_type' => 'regional_governor',
            'source_key' => 'erm2026:region:070000:regional_governor:round:1',
            'title' => 'Encuesta regional del Callao',
        ],
        [
            'scope_type' => 'district',
            'official_code' => '150142',
            'office_type' => 'district_mayor',
            'source_key' => 'erm2026:district:150142:district_mayor:round:1',
            'title' => 'Encuesta distrital de Villa El Salvador',
        ],
        [
            'scope_type' => 'district',
            'official_code' => '150143',
            'office_type' => 'district_mayor',
            'source_key' => 'erm2026:district:150143:district_mayor:round:1',
            'title' => 'Encuesta distrital de Villa María del Triunfo',
        ],
    ];

    public function run(): void
    {
        foreach (self::TARGETS as $target) {
            DB::transaction(function () use ($target): void {
                $territoryId = DB::table('electoral_territories')
                    ->where('scope_type', $target['scope_type'])
                    ->where('official_code', $target['official_code'])
                    ->value('id');

                if ($territoryId === null) {
                    $this->command?->warn(
                        "Initial survey skipped: {$target['scope_type']} "
                        ."{$target['official_code']} is absent from the electoral catalog.",
                    );

                    return;
                }

                $candidacies = $this->activeCandidacies(
                    (string) $territoryId,
                    $target['office_type'],
                );
                $roundId = $this->reconcileRound($target, (string) $territoryId, $candidacies);

                $this->reconcileOptions($roundId, $candidacies);
            }, 3);
        }
    }

    /**
     * @return Collection<int, object{id:string, ballot_order:int|null}>
     */
    private function activeCandidacies(string $territoryId, string $officeType): Collection
    {
        return DB::table('electoral_candidacies as candidacies')
            ->join('electoral_candidates as candidates', 'candidates.id', '=', 'candidacies.candidate_id')
            ->join('electoral_parties as parties', 'parties.id', '=', 'candidacies.political_party_id')
            ->where('candidacies.territory_id', $territoryId)
            ->where('candidacies.office_type', $officeType)
            ->where('candidacies.election_cycle', self::ELECTION_CYCLE)
            ->where('candidacies.status', 'active')
            ->where('candidates.status', 'active')
            ->where('parties.status', 'active')
            ->orderByRaw('CASE WHEN candidacies.ballot_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('candidacies.ballot_order')
            ->orderBy('candidacies.id')
            ->get(['candidacies.id', 'candidacies.ballot_order']);
    }

    /**
     * @param  array{
     *     scope_type:string,
     *     official_code:string,
     *     office_type:string,
     *     source_key:string,
     *     title:string
     * }  $target
     * @param  Collection<int, object{id:string, ballot_order:int|null}>  $candidacies
     */
    private function reconcileRound(array $target, string $territoryId, Collection $candidacies): string
    {
        $naturalRound = DB::table('survey_rounds')
            ->where('territory_id', $territoryId)
            ->where('round_number', 1)
            ->where('election_cycle', self::ELECTION_CYCLE)
            ->where('survey_type', 'online_owned')
            ->where('office_type', $target['office_type'])
            ->first();

        if ($naturalRound !== null) {
            $roundId = $naturalRound->id;
        } else {
            $roundId = DB::table('survey_rounds')
                ->where('source_system', self::SOURCE_SYSTEM)
                ->where('source_key', $target['source_key'])
                ->value('id');
        }

        $isReady = $candidacies->isNotEmpty();
        $now = now();
        $attributes = [
            'territory_id' => $territoryId,
            'round_number' => 1,
            'election_cycle' => self::ELECTION_CYCLE,
            'survey_type' => 'online_owned',
            'office_type' => $target['office_type'],
            'title' => $target['title'],
            'opens_at' => CarbonImmutable::create(2026, 7, 21, 0, 0, 0, 'America/Lima'),
            'closes_at' => CarbonImmutable::create(2026, 8, 5, 23, 59, 59, 'America/Lima'),
            'publication_state' => 'published',
            'readiness_state' => $isReady ? 'active' : 'blocked',
            'blocked_reason' => $isReady ? null : 'candidate_data_unavailable',
            'updated_at' => $now,
        ];

        if ($roundId === null) {
            $roundId = $this->deterministicId('round:'.$target['source_key']);

            DB::table('survey_rounds')->insert($attributes + [
                'id' => $roundId,
                'source_system' => self::SOURCE_SYSTEM,
                'source_key' => $target['source_key'],
                'created_at' => $now,
            ]);

            return $roundId;
        }

        DB::table('survey_rounds')->where('id', $roundId)->update($attributes);

        return (string) $roundId;
    }

    /**
     * @param  Collection<int, object{id:string, ballot_order:int|null}>  $candidacies
     */
    private function reconcileOptions(string $roundId, Collection $candidacies): void
    {
        $existing = DB::table('survey_options')
            ->where('survey_round_id', $roundId)
            ->orderBy('id')
            ->get(['id', 'candidacy_id']);

        foreach ($existing as $index => $option) {
            DB::table('survey_options')->where('id', $option->id)->update([
                'display_order' => 60000 + $index,
                'status' => 'ineligible',
                'updated_at' => now(),
            ]);
        }

        foreach ($candidacies->values() as $index => $candidacy) {
            $option = $existing->firstWhere('candidacy_id', $candidacy->id);
            $attributes = [
                'display_order' => $index + 1,
                'status' => 'eligible',
                'updated_at' => now(),
            ];

            if ($option !== null) {
                DB::table('survey_options')->where('id', $option->id)->update($attributes);

                continue;
            }

            DB::table('survey_options')->insert($attributes + [
                'id' => $this->deterministicId("option:{$roundId}:{$candidacy->id}"),
                'survey_round_id' => $roundId,
                'candidacy_id' => $candidacy->id,
                'created_at' => now(),
            ]);
        }

        $activeCandidacyIds = $candidacies->pluck('id')->all();
        $staleOptions = DB::table('survey_options')
            ->where('survey_round_id', $roundId)
            ->when(
                $activeCandidacyIds !== [],
                fn ($query) => $query->whereNotIn('candidacy_id', $activeCandidacyIds),
            )
            ->get(['id']);

        foreach ($staleOptions as $option) {
            $hasVotes = DB::table('interactive_votes')
                ->where('survey_option_id', $option->id)
                ->exists();

            if (! $hasVotes) {
                DB::table('survey_options')->where('id', $option->id)->delete();
            }
        }
    }

    private function deterministicId(string $seed): string
    {
        return strtoupper(substr(hash('sha256', self::SOURCE_SYSTEM.':'.$seed), 0, 26));
    }
}
