<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class LocalProductionSnapshotSeeder extends Seeder
{
    private const SOURCE_SYSTEM = 'local_seed';

    private const TERRITORY_MAP_PATH = 'data/territories_ubigeo_map.json';

    private const ELECTION_CYCLE = 'ERM2026';

    /**
     * Current public snapshot observed on production for:
     * /encuestas/district/carmen-de-la-legua-reynoso
     *
     * @var array<int, array{
     *     party_key:string,
     *     party_name:string,
     *     candidate_key:string,
     *     candidate_name:string
     * }>
     */
    private const CARMEN_OPTIONS = [
        [
            'party_key' => 'party-1',
            'party_name' => 'ACCION POPULAR',
            'candidate_key' => 'candidate-1',
            'candidate_name' => 'RICHARD ANGEL TINEO SURCO',
        ],
        [
            'party_key' => 'party-2',
            'party_name' => 'ALIANZA PARA EL PROGRESO',
            'candidate_key' => 'candidate-2',
            'candidate_name' => 'JUANA ROSA SILVA GAMBOA',
        ],
        [
            'party_key' => 'party-3',
            'party_name' => 'FUERZA POPULAR',
            'candidate_key' => 'candidate-3',
            'candidate_name' => 'JUAN DE DIOS GAVILANO RAMIREZ',
        ],
        [
            'party_key' => 'party-4',
            'party_name' => 'PARTIDO DEMOCRATA VERDE',
            'candidate_key' => 'candidate-4',
            'candidate_name' => 'EDISON OLIVER TORRES SOTELO',
        ],
        [
            'party_key' => 'party-5',
            'party_name' => 'PARTIDO POLITICO PRIN',
            'candidate_key' => 'candidate-5',
            'candidate_name' => 'OSCAR ANDRE VILLAR GONZALES',
        ],
        [
            'party_key' => 'party-6',
            'party_name' => 'PRIMERO LA GENTE - COMUNIDAD, ECOLOGIA, LIBERTAD Y PROGRESO',
            'candidate_key' => 'candidate-6',
            'candidate_name' => 'RICCE JAVIER TENORIO SERNAQUE',
        ],
        [
            'party_key' => 'party-7',
            'party_name' => 'RENOVACION POPULAR PERU',
            'candidate_key' => 'candidate-7',
            'candidate_name' => 'DANIEL ALMANZOR LECCA RUBIO',
        ],
    ];

    public function run(): void
    {
        $territories = $this->seedTerritoriesFromMap();

        $districtId = $territories['070103'] ?? null;
        if ($districtId === null) {
            throw new RuntimeException('La semilla local no pudo resolver el distrito 070103.');
        }

        $roundId = $this->upsertRound($districtId);
        $this->seedRoundOptions($roundId);
    }

    /**
     * @return array<string, string>
     */
    private function seedTerritoriesFromMap(): array
    {
        $path = base_path(self::TERRITORY_MAP_PATH);
        if (! is_file($path)) {
            throw new RuntimeException("No existe el mapa territorial local en {$path}.");
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($decoded)) {
            throw new RuntimeException('El mapa territorial local no contiene una lista válida.');
        }

        $territoriesByCode = [];

        foreach ($decoded as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $officialCode = (string) ($entry['official_code'] ?? '');
            $scopeType = (string) ($entry['scope_type'] ?? '');
            $name = (string) ($entry['name'] ?? '');

            if ($officialCode === '' || $scopeType === '' || $name === '') {
                continue;
            }

            $parentId = null;
            $parentCode = $entry['parent_official_code'] ?? null;
            if (is_string($parentCode) && $parentCode !== '' && isset($territoriesByCode[$parentCode])) {
                $parentId = $territoriesByCode[$parentCode];
            }

            $territoriesByCode[$officialCode] = $this->upsertTerritory([
                'official_code' => $officialCode,
                'scope_type' => $scopeType,
                'name' => $name,
                'parent_id' => $parentId,
            ]);
        }

        return $territoriesByCode;
    }

    /**
     * @param  array{official_code:string,scope_type:string,name:string,parent_id:?string}  $territory
     */
    private function upsertTerritory(array $territory): string
    {
        $existing = DB::table('electoral_territories')
            ->where('scope_type', $territory['scope_type'])
            ->where('official_code', $territory['official_code'])
            ->first();

        $payload = [
            'official_code' => $territory['official_code'],
            'scope_type' => $territory['scope_type'],
            'name' => $territory['name'],
            'canonical_name' => Str::lower(Str::ascii($territory['name'])),
            'slug' => Str::slug($territory['name']),
            'parent_id' => $territory['parent_id'],
            'source_system' => self::SOURCE_SYSTEM,
            'source_key' => 'territory:'.$territory['official_code'],
            'publication_state' => 'published',
            'published_at' => now(),
            'source_url' => null,
            'updated_at' => now(),
        ];

        if ($existing !== null) {
            DB::table('electoral_territories')->where('id', $existing->id)->update($payload);

            return (string) $existing->id;
        }

        $id = (string) Str::ulid();
        DB::table('electoral_territories')->insert($payload + [
            'id' => $id,
            'created_at' => now(),
        ]);

        return $id;
    }

    private function upsertParty(string $sourceKey, string $name): string
    {
        return $this->upsertCatalogRow('electoral_parties', $sourceKey, [
            'name' => $name,
            'acronym' => null,
            'logo_url' => null,
            'logo_storage_disk' => null,
            'logo_storage_path' => null,
            'logo_source_attribution' => null,
            'source_url' => null,
            'status' => 'active',
        ]);
    }

    private function upsertCandidate(string $sourceKey, string $name): string
    {
        return $this->upsertCatalogRow('electoral_candidates', $sourceKey, [
            'full_name' => $name,
            'photo_url' => null,
            'photo_storage_disk' => null,
            'photo_storage_path' => null,
            'photo_source_attribution' => null,
            'source_url' => null,
            'status' => 'active',
        ]);
    }

    private function upsertCandidacy(string $candidateId, string $partyId, string $territoryId, string $sourceKey, int $ballotOrder): string
    {
        $existing = DB::table('electoral_candidacies')
            ->where('candidate_id', $candidateId)
            ->where('political_party_id', $partyId)
            ->where('territory_id', $territoryId)
            ->where('office_type', 'district_mayor')
            ->where('election_cycle', self::ELECTION_CYCLE)
            ->first();

        $payload = [
            'candidate_id' => $candidateId,
            'political_party_id' => $partyId,
            'territory_id' => $territoryId,
            'office_type' => 'district_mayor',
            'election_cycle' => self::ELECTION_CYCLE,
            'source_system' => self::SOURCE_SYSTEM,
            'source_key' => $sourceKey,
            'ballot_order' => $ballotOrder,
            'status' => 'active',
            'source_file' => null,
            'source_row' => null,
            'source_url' => null,
            'retrieved_at' => now(),
            'updated_at' => now(),
        ];

        if ($existing !== null) {
            DB::table('electoral_candidacies')->where('id', $existing->id)->update($payload);

            return (string) $existing->id;
        }

        $id = (string) Str::ulid();
        DB::table('electoral_candidacies')->insert($payload + [
            'id' => $id,
            'created_at' => now(),
        ]);

        return $id;
    }

    private function upsertRound(string $territoryId): string
    {
        $existing = DB::table('survey_rounds')
            ->where('territory_id', $territoryId)
            ->where('round_number', 1)
            ->where('election_cycle', self::ELECTION_CYCLE)
            ->where('survey_type', 'online_owned')
            ->where('office_type', 'district_mayor')
            ->first();

        $payload = [
            'territory_id' => $territoryId,
            'round_number' => 1,
            'election_cycle' => self::ELECTION_CYCLE,
            'survey_type' => 'online_owned',
            'office_type' => 'district_mayor',
            'title' => 'Encuesta distrital de Carmen de la Legua-Reynoso',
            'opens_at' => CarbonImmutable::create(2026, 7, 23, 0, 0, 0, 'America/Lima'),
            'closes_at' => CarbonImmutable::create(2026, 8, 5, 23, 59, 59, 'America/Lima'),
            'publication_state' => 'published',
            'readiness_state' => 'active',
            'blocked_reason' => null,
            'source_system' => self::SOURCE_SYSTEM,
            'source_key' => 'district:070103:district_mayor:round:1',
            'source_url' => null,
            'updated_at' => now(),
        ];

        if ($existing !== null) {
            DB::table('survey_rounds')->where('id', $existing->id)->update($payload);

            return (string) $existing->id;
        }

        $id = (string) Str::ulid();
        DB::table('survey_rounds')->insert($payload + [
            'id' => $id,
            'created_at' => now(),
        ]);

        return $id;
    }

    private function seedRoundOptions(string $roundId): void
    {
        $hasVotes = DB::table('interactive_votes')
            ->where('survey_round_id', $roundId)
            ->exists();

        if (! $hasVotes) {
            DB::table('survey_options')->where('survey_round_id', $roundId)->delete();
        }

        foreach (self::CARMEN_OPTIONS as $index => $option) {
            $partyId = $this->upsertParty($option['party_key'], $option['party_name']);
            $candidateId = $this->upsertCandidate($option['candidate_key'], $option['candidate_name']);
            $candidacyId = $this->upsertCandidacy(
                $candidateId,
                $partyId,
                $this->districtId(),
                'candidacy:070103:'.($index + 1),
                $index + 1,
            );

            $existing = DB::table('survey_options')
                ->where('survey_round_id', $roundId)
                ->where('candidacy_id', $candidacyId)
                ->first();

            $payload = [
                'survey_round_id' => $roundId,
                'candidacy_id' => $candidacyId,
                'display_order' => $index + 1,
                'status' => 'eligible',
                'updated_at' => now(),
            ];

            if ($existing !== null) {
                DB::table('survey_options')->where('id', $existing->id)->update($payload);

                continue;
            }

            DB::table('survey_options')->insert($payload + [
                'id' => (string) Str::ulid(),
                'created_at' => now(),
            ]);
        }
    }

    private function districtId(): string
    {
        $district = DB::table('electoral_territories')
            ->where('scope_type', 'district')
            ->where('official_code', '070103')
            ->first();

        if ($district === null) {
            throw new RuntimeException('No se pudo resolver el distrito 070103.');
        }

        return (string) $district->id;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertCatalogRow(string $table, string $sourceKey, array $payload): string
    {
        $existing = DB::table($table)
            ->where('source_system', self::SOURCE_SYSTEM)
            ->where('source_key', $sourceKey)
            ->first();

        $base = $payload + [
            'source_system' => self::SOURCE_SYSTEM,
            'source_key' => $sourceKey,
            'updated_at' => now(),
        ];

        if ($existing !== null) {
            DB::table($table)->where('id', $existing->id)->update($base);

            return (string) $existing->id;
        }

        $id = (string) Str::ulid();
        DB::table($table)->insert($base + [
            'id' => $id,
            'created_at' => now(),
        ]);

        return $id;
    }
}
