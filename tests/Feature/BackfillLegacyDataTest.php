<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillLegacyDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('vote.device_hmac_key', 'device-key-012345678901234567890');
    }

    public function test_catalog_is_backfilled_and_a_second_run_creates_no_duplicates(): void
    {
        $this->createLegacyCatalogTables();
        $this->seedLegacyCatalog();

        $this->artisan('legacy:backfill', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('electoral_territories', 1);
        $this->assertDatabaseCount('electoral_parties', 1);
        $this->assertDatabaseCount('electoral_candidates', 1);
        $this->assertDatabaseCount('electoral_candidacies', 1);
        $this->assertDatabaseCount('legacy_mappings', 4);

        $territoryId = DB::table('legacy_mappings')
            ->where('source_table', 'election_scopes')
            ->where('legacy_id', 'scope-legacy')
            ->where('target_table', 'electoral_territories')
            ->value('target_id');

        $this->artisan('legacy:backfill', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('electoral_territories', 1);
        $this->assertDatabaseCount('electoral_parties', 1);
        $this->assertDatabaseCount('electoral_candidates', 1);
        $this->assertDatabaseCount('electoral_candidacies', 1);
        $this->assertDatabaseCount('legacy_mappings', 4);
        $this->assertSame(
            $territoryId,
            DB::table('legacy_mappings')
                ->where('source_table', 'election_scopes')
                ->where('legacy_id', 'scope-legacy')
                ->where('target_table', 'electoral_territories')
                ->value('target_id')
        );
    }

    public function test_dry_run_reports_work_without_persisting_it(): void
    {
        $this->createLegacyCatalogTables();
        $this->seedLegacyCatalog();

        $this->artisan('legacy:backfill', ['--dry-run' => true, '--batch' => 2])
            ->expectsOutputToContain('Dry run enabled')
            ->assertSuccessful();

        $this->assertDatabaseCount('electoral_territories', 0);
        $this->assertDatabaseCount('electoral_parties', 0);
        $this->assertDatabaseCount('electoral_candidates', 0);
        $this->assertDatabaseCount('electoral_candidacies', 0);
        $this->assertDatabaseCount('legacy_mappings', 0);
    }

    public function test_round_option_and_mappable_vote_are_preserved(): void
    {
        $this->createLegacyCatalogTables();
        $this->createLegacySurveyTables();
        $this->seedLegacyCatalog();
        $this->seedLegacySurvey();

        $this->artisan('legacy:backfill', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('survey_rounds', 1);
        $this->assertDatabaseCount('survey_options', 1);
        $this->assertDatabaseCount('interactive_votes', 1);
        $this->assertDatabaseHas('legacy_mappings', [
            'source_table' => 'encuestas',
            'legacy_id' => 'survey-legacy',
            'target_table' => 'survey_rounds',
        ]);
        $this->assertDatabaseHas('legacy_mappings', [
            'source_table' => 'votos_interactivos',
            'legacy_id' => 'vote-legacy',
            'target_table' => 'interactive_votes',
        ]);
        $this->assertDatabaseHas('interactive_votes', [
            'ip_hmac' => hash('sha256', 'connection'),
            'device_token_hmac' => hash_hmac(
                'sha256',
                hash('sha256', 'device'),
                'device-key-012345678901234567890',
            ),
            'geo_validation_method' => 'legacy_bl14',
            'status' => 'accepted',
        ]);

        $this->artisan('legacy:backfill', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('survey_rounds', 1);
        $this->assertDatabaseCount('survey_options', 1);
        $this->assertDatabaseCount('interactive_votes', 1);
    }

    public function test_command_succeeds_when_legacy_tables_are_absent(): void
    {
        $this->artisan('legacy:backfill')
            ->expectsOutput('No legacy tables were found; nothing to backfill.')
            ->assertSuccessful();

        $this->assertDatabaseCount('legacy_mappings', 0);
    }

    private function createLegacyCatalogTables(): void
    {
        Schema::create('election_scopes', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('scope_uid', 32);
            $table->string('source_system', 50);
            $table->string('source_key', 120);
            $table->string('territory_slug', 64);
            $table->string('election_process_code', 30);
            $table->unsignedSmallInteger('election_year');
            $table->string('election_level', 20);
            $table->string('office_code', 80);
            $table->string('office_name', 150);
            $table->string('region_ubigeo', 6)->nullable();
            $table->string('region_name', 100);
            $table->string('province_ubigeo', 6)->nullable();
            $table->string('province_name', 100)->nullable();
            $table->string('district_ubigeo', 6)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('political_organizations', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('source_system', 50);
            $table->string('source_key', 120);
            $table->string('organization_name');
            $table->string('organization_abbreviation', 50)->nullable();
            $table->text('party_logo_url')->nullable();
            $table->string('party_logo_local_path', 500)->nullable();
            $table->text('organization_profile_url')->nullable();
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('source_system', 50);
            $table->string('source_key', 120);
            $table->string('candidate_full_name');
            $table->text('candidate_photo_url')->nullable();
            $table->string('candidate_photo_local_path', 500)->nullable();
            $table->text('candidate_profile_url')->nullable();
            $table->timestamps();
        });

        Schema::create('candidacies', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('source_system', 50);
            $table->string('source_key', 180);
            $table->string('scope_id', 32);
            $table->string('organization_id', 32);
            $table->string('candidate_id', 32);
            $table->string('candidacy_status', 50);
            $table->unsignedInteger('ballot_order')->nullable();
            $table->string('source_file')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->text('source_url')->nullable();
            $table->dateTime('retrieved_at')->nullable();
            $table->timestamps();
        });
    }

    private function createLegacySurveyTables(): void
    {
        Schema::create('encuestas', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('distrito_id', 64);
            $table->string('nivel', 20);
            $table->string('tipo', 30);
            $table->unsignedTinyInteger('numero_ronda');
            $table->string('titulo');
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre');
            $table->string('estado_publicacion', 20);
            $table->dateTime('created_at');
        });

        Schema::create('votos_interactivos', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->string('encuesta_id', 32);
            $table->string('ubigeo_votacion', 64);
            $table->string('candidato_id', 64)->nullable();
            $table->string('tipo_voto', 20);
            $table->decimal('gps_lat', 10, 8);
            $table->decimal('gps_lng', 11, 8);
            $table->unsignedSmallInteger('gps_accuracy_meters')->nullable();
            $table->unsignedInteger('interaction_time_ms')->nullable();
            $table->string('ip_hash', 64);
            $table->binary('ip_cifrada');
            $table->binary('ip_iv');
            $table->binary('ip_tag');
            $table->string('device_token', 64);
            $table->string('browser_fingerprint', 64);
            $table->string('estado', 20);
            $table->dateTime('created_at');
        });
    }

    private function seedLegacyCatalog(): void
    {
        $now = now();

        DB::table('election_scopes')->insert([
            'id' => 'scope-legacy',
            'scope_uid' => 'scope-uid',
            'source_system' => 'jne',
            'source_key' => 'scope:callao:regional',
            'territory_slug' => 'callao',
            'election_process_code' => 'ERM2026',
            'election_year' => 2026,
            'election_level' => 'REGIONAL',
            'office_code' => 'regional_governor',
            'office_name' => 'Gobernador Regional',
            'region_ubigeo' => '070000',
            'region_name' => 'Callao',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('political_organizations')->insert([
            'id' => 'party-legacy',
            'source_system' => 'jne',
            'source_key' => 'party:one',
            'organization_name' => 'Partido Verificado',
            'organization_abbreviation' => 'PV',
            'party_logo_url' => 'https://example.test/party.webp',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('candidates')->insert([
            'id' => 'candidate-legacy',
            'source_system' => 'jne',
            'source_key' => 'candidate:one',
            'candidate_full_name' => 'Candidato Verificado',
            'candidate_photo_url' => 'https://example.test/candidate.webp',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('candidacies')->insert([
            'id' => 'candidacy-legacy',
            'source_system' => 'jne',
            'source_key' => 'candidacy:one',
            'scope_id' => 'scope-legacy',
            'organization_id' => 'party-legacy',
            'candidate_id' => 'candidate-legacy',
            'candidacy_status' => 'active',
            'ballot_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function seedLegacySurvey(): void
    {
        $now = now();

        DB::table('encuestas')->insert([
            'id' => 'survey-legacy',
            'distrito_id' => 'callao',
            'nivel' => 'region',
            'tipo' => 'online_propia',
            'numero_ronda' => 1,
            'titulo' => 'Encuesta regional del Callao',
            'fecha_apertura' => $now->copy()->subDay(),
            'fecha_cierre' => $now->copy()->addDay(),
            'estado_publicacion' => 'producción',
            'created_at' => $now,
        ]);
        DB::table('votos_interactivos')->insert([
            'id' => 'vote-legacy',
            'encuesta_id' => 'survey-legacy',
            'ubigeo_votacion' => 'callao',
            'candidato_id' => 'candidate-legacy',
            'tipo_voto' => 'candidato',
            'gps_lat' => -12.046374,
            'gps_lng' => -77.042793,
            'gps_accuracy_meters' => 15,
            'interaction_time_ms' => 1200,
            'ip_hash' => hash('sha256', 'connection'),
            'ip_cifrada' => 'encrypted-ip',
            'ip_iv' => '1234567890123456',
            'ip_tag' => '1234567890123456',
            'device_token' => hash('sha256', 'device'),
            'browser_fingerprint' => hash('sha256', 'browser'),
            'estado' => 'valido',
            'created_at' => $now,
        ]);
    }
}
