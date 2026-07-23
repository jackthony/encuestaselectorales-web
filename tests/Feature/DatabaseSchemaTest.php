<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalized_tables_use_opaque_primary_keys(): void
    {
        foreach ([
            'electoral_territories',
            'electoral_parties',
            'electoral_candidates',
            'electoral_candidacies',
            'survey_rounds',
            'survey_options',
            'interactive_votes',
            'import_runs',
            'import_rows',
        ] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'id'), "{$table} must have an opaque id.");
        }

        $idColumn = collect(Schema::getColumns('electoral_territories'))
            ->firstWhere('name', 'id');
        $primaryIndex = collect(Schema::getIndexes('electoral_territories'))
            ->first(static fn (array $index): bool => (bool) ($index['primary'] ?? false));

        $this->assertNotNull($idColumn);
        $this->assertStringStartsWith('varchar', strtolower((string) $idColumn['type']));
        $this->assertNotNull($primaryIndex);
        $this->assertContains('id', $primaryIndex['columns']);
    }

    public function test_candidacy_rejects_missing_catalog_relationships(): void
    {
        $this->expectException(QueryException::class);

        DB::table('electoral_candidacies')->insert([
            'id' => $this->id('candidacy'),
            'candidate_id' => $this->id('missing-candidate'),
            'political_party_id' => $this->id('missing-party'),
            'territory_id' => $this->id('missing-territory'),
            'office_type' => 'provincial_mayor',
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => 'invalid',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_territory_natural_key_is_unique(): void
    {
        $row = [
            'official_code' => '070000',
            'scope_type' => 'region',
            'name' => 'Callao',
            'canonical_name' => 'callao',
            'slug' => 'callao-region',
            'source_system' => 'test',
            'publication_state' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('electoral_territories')->insert($row + [
            'id' => $this->id('callao-one'),
            'source_key' => 'callao-one',
        ]);

        $this->expectException(QueryException::class);

        DB::table('electoral_territories')->insert($row + [
            'id' => $this->id('callao-two'),
            'source_key' => 'callao-two',
        ]);
    }

    public function test_additive_schema_can_coexist_with_legacy_tables(): void
    {
        foreach (['candidates', 'candidacies', 'encuestas', 'votos_interactivos'] as $table) {
            Schema::create($table, function ($blueprint): void {
                $blueprint->string('id', 32)->primary();
            });

            $this->assertTrue(Schema::hasTable($table));
        }
    }

    public function test_vote_constraints_block_duplicate_ip_and_device_signals(): void
    {
        $ids = $this->seedEligibleOption();

        $this->insertVote($this->id('vote-one'), $ids, 'a', 'b');

        try {
            $this->insertVote($this->id('vote-two'), $ids, 'a', 'c');
            $this->fail('The round and IP signal constraint did not reject a duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('interactive_votes', 1);
        }

        try {
            $this->insertVote($this->id('vote-three'), $ids, 'd', 'b');
            $this->fail('The round and device signal constraint did not reject a duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('interactive_votes', 1);
        }
    }

    /**
     * @return array{territory:string,round:string,option:string}
     */
    private function seedEligibleOption(): array
    {
        $territoryId = $this->id('territory');
        $partyId = $this->id('party');
        $candidateId = $this->id('candidate');
        $candidacyId = $this->id('candidacy');
        $roundId = $this->id('round');
        $optionId = $this->id('option');
        $now = now();

        DB::table('electoral_territories')->insert([
            'id' => $territoryId,
            'official_code' => '150100',
            'scope_type' => 'province',
            'name' => 'Lima',
            'canonical_name' => 'lima',
            'slug' => 'lima-provincia',
            'source_system' => 'test',
            'source_key' => 'territory:lima',
            'publication_state' => 'published',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_parties')->insert([
            'id' => $partyId,
            'source_system' => 'test',
            'source_key' => 'party:one',
            'name' => 'Partido Verificado',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_candidates')->insert([
            'id' => $candidateId,
            'source_system' => 'test',
            'source_key' => 'candidate:one',
            'full_name' => 'Candidato Verificado',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_candidacies')->insert([
            'id' => $candidacyId,
            'candidate_id' => $candidateId,
            'political_party_id' => $partyId,
            'territory_id' => $territoryId,
            'office_type' => 'provincial_mayor',
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => 'candidacy:one',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('survey_rounds')->insert([
            'id' => $roundId,
            'territory_id' => $territoryId,
            'round_number' => 1,
            'election_cycle' => 'ERM2026',
            'survey_type' => 'online_owned',
            'office_type' => 'provincial_mayor',
            'title' => 'Encuesta provincial de Lima',
            'opens_at' => $now->copy()->subDay(),
            'closes_at' => $now->copy()->addDay(),
            'publication_state' => 'published',
            'readiness_state' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('survey_options')->insert([
            'id' => $optionId,
            'survey_round_id' => $roundId,
            'candidacy_id' => $candidacyId,
            'display_order' => 1,
            'status' => 'eligible',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'territory' => $territoryId,
            'round' => $roundId,
            'option' => $optionId,
        ];
    }

    /**
     * @param  array{territory:string,round:string,option:string}  $ids
     */
    private function insertVote(string $id, array $ids, string $ipSignal, string $deviceSignal): void
    {
        DB::table('interactive_votes')->insert([
            'id' => $id,
            'survey_round_id' => $ids['round'],
            'survey_option_id' => $ids['option'],
            'validated_territory_id' => $ids['territory'],
            'vote_type' => 'candidate',
            'gps_latitude' => -12.046374,
            'gps_longitude' => -77.042793,
            'gps_accuracy_meters' => 10,
            'geo_validation_method' => 'test',
            'geo_validation_result' => 'inside',
            'interaction_time_ms' => 1000,
            'ip_ciphertext' => 'ciphertext',
            'ip_nonce' => '123456789012',
            'ip_auth_tag' => '1234567890123456',
            'ip_encryption_key_version' => 1,
            'ip_hmac' => hash('sha256', $ipSignal),
            'ip_hmac_key_version' => 1,
            'device_token_hmac' => hash('sha256', $deviceSignal),
            'device_hmac_key_version' => 1,
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
