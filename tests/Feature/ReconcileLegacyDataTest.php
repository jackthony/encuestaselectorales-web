<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ReconcileLegacyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_fails_until_every_legacy_row_has_a_verified_target(): void
    {
        Schema::create('encuestas', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
        });
        DB::table('encuestas')->insert(['id' => 'legacy-one']);

        $this->artisan('app:reconcile-legacy', ['--json' => true])
            ->expectsOutputToContain('"status": "mismatch"')
            ->assertFailed();

        $territory = $this->id('territory');
        $round = $this->id('round');
        $now = now();
        DB::table('electoral_territories')->insert([
            'id' => $territory,
            'official_code' => '070000',
            'scope_type' => 'region',
            'name' => 'Callao',
            'canonical_name' => 'callao',
            'slug' => 'callao-region',
            'source_system' => 'test',
            'source_key' => 'callao',
            'publication_state' => 'published',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('survey_rounds')->insert([
            'id' => $round,
            'territory_id' => $territory,
            'round_number' => 1,
            'election_cycle' => 'ERM2026',
            'survey_type' => 'online_owned',
            'office_type' => 'regional_governor',
            'title' => 'Encuesta regional del Callao',
            'opens_at' => $now,
            'closes_at' => $now->copy()->addDay(),
            'publication_state' => 'published',
            'readiness_state' => 'blocked',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('legacy_mappings')->insert([
            'id' => $this->id('mapping'),
            'source_table' => 'encuestas',
            'legacy_id' => 'legacy-one',
            'target_table' => 'survey_rounds',
            'target_id' => $round,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->artisan('app:reconcile-legacy', ['--json' => true])
            ->expectsOutputToContain('"status": "matched"')
            ->assertSuccessful();
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
