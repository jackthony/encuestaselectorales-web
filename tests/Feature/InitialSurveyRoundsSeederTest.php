<?php

namespace Tests\Feature;

use App\Domain\Survey\Contracts\SurveyRoundQuery;
use Database\Seeders\InitialSurveyRoundsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InitialSurveyRoundsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_only_active_approved_candidacies_and_blocks_a_missing_roster(): void
    {
        $lima = $this->territory('province', '150100', 'Lima');
        $callao = $this->territory('region', '070000', 'Callao');
        $eligibleCandidacy = $this->candidacy($lima, 'provincial_mayor', 'eligible', true);
        $this->candidacy($lima, 'provincial_mayor', 'inactive-candidacy', false);
        $this->candidacy($lima, 'regional_governor', 'wrong-office', true);
        $this->candidacy($callao, 'regional_governor', 'inactive-party', true, false);

        $this->seed(InitialSurveyRoundsSeeder::class);

        $limaRound = DB::table('survey_rounds')->where('territory_id', $lima)->first();
        $callaoRound = DB::table('survey_rounds')->where('territory_id', $callao)->first();

        $this->assertNotNull($limaRound);
        $this->assertSame('active', $limaRound->readiness_state);
        $this->assertSame('published', $limaRound->publication_state);
        $this->assertSame('2026-08-05 23:59:59', $limaRound->closes_at);
        $this->assertSame('America/Lima', config('app.timezone'));
        $this->assertDatabaseCount('survey_options', 1);
        $this->assertDatabaseHas('survey_options', [
            'survey_round_id' => $limaRound->id,
            'candidacy_id' => $eligibleCandidacy,
            'display_order' => 1,
            'status' => 'eligible',
        ]);

        $this->assertNotNull($callaoRound);
        $this->assertSame('blocked', $callaoRound->readiness_state);
        $this->assertSame('candidate_data_unavailable', $callaoRound->blocked_reason);
        $this->assertDatabaseMissing('survey_options', [
            'survey_round_id' => $callaoRound->id,
            'status' => 'eligible',
        ]);

        $listedRounds = app(SurveyRoundQuery::class)->activeNational();
        $this->assertCount(2, $listedRounds);
        $this->assertSame(
            ['active', 'blocked'],
            collect($listedRounds)->pluck('readinessState')->sort()->values()->all(),
        );
    }

    public function test_it_is_idempotent_and_never_changes_existing_votes(): void
    {
        $lima = $this->territory('province', '150100', 'Lima');
        $this->territory('region', '070000', 'Callao');
        $candidacy = $this->candidacy($lima, 'provincial_mayor', 'eligible', true);

        $this->seed(InitialSurveyRoundsSeeder::class);

        $round = DB::table('survey_rounds')->where('territory_id', $lima)->first();
        $option = DB::table('survey_options')->where('survey_round_id', $round->id)->first();
        $voteId = $this->id('vote');

        DB::table('interactive_votes')->insert([
            'id' => $voteId,
            'survey_round_id' => $round->id,
            'survey_option_id' => $option->id,
            'validated_territory_id' => $lima,
            'vote_type' => 'candidate',
            'gps_latitude' => -12.046374,
            'gps_longitude' => -77.042793,
            'gps_accuracy_meters' => 10,
            'geo_validation_method' => 'test',
            'geo_validation_result' => 'inside',
            'interaction_time_ms' => 1500,
            'ip_ciphertext' => 'encrypted',
            'ip_nonce' => 'nonce',
            'ip_auth_tag' => 'tag',
            'ip_encryption_key_version' => 1,
            'ip_hmac' => hash('sha256', 'ip'),
            'ip_hmac_key_version' => 1,
            'device_token_hmac' => hash('sha256', 'device'),
            'device_hmac_key_version' => 1,
            'browser_fingerprint_hmac' => hash('sha256', 'browser'),
            'browser_hmac_key_version' => 1,
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(InitialSurveyRoundsSeeder::class);

        $this->assertDatabaseCount('survey_rounds', 2);
        $this->assertDatabaseCount('survey_options', 1);
        $this->assertDatabaseCount('interactive_votes', 1);
        $this->assertDatabaseHas('interactive_votes', [
            'id' => $voteId,
            'survey_round_id' => $round->id,
            'survey_option_id' => $option->id,
        ]);
        $this->assertDatabaseHas('survey_options', [
            'id' => $option->id,
            'candidacy_id' => $candidacy,
            'status' => 'eligible',
        ]);
    }

    public function test_it_reconciles_a_blocked_round_when_an_active_roster_arrives(): void
    {
        $lima = $this->territory('province', '150100', 'Lima');
        $this->territory('region', '070000', 'Callao');

        $this->seed(InitialSurveyRoundsSeeder::class);

        $roundId = DB::table('survey_rounds')->where('territory_id', $lima)->value('id');
        $this->assertDatabaseHas('survey_rounds', [
            'id' => $roundId,
            'readiness_state' => 'blocked',
        ]);

        $candidacy = $this->candidacy($lima, 'provincial_mayor', 'late-roster', true);
        $this->seed(InitialSurveyRoundsSeeder::class);

        $this->assertDatabaseHas('survey_rounds', [
            'id' => $roundId,
            'readiness_state' => 'active',
            'blocked_reason' => null,
        ]);
        $this->assertDatabaseHas('survey_options', [
            'survey_round_id' => $roundId,
            'candidacy_id' => $candidacy,
            'status' => 'eligible',
        ]);
    }

    private function territory(string $scopeType, string $officialCode, string $name): string
    {
        $id = $this->id("territory:{$scopeType}:{$officialCode}");

        DB::table('electoral_territories')->insert([
            'id' => $id,
            'official_code' => $officialCode,
            'scope_type' => $scopeType,
            'name' => $name,
            'canonical_name' => strtolower($name),
            'slug' => strtolower("{$name}-{$scopeType}"),
            'source_system' => 'test',
            'source_key' => "territory:{$scopeType}:{$officialCode}",
            'publication_state' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function candidacy(
        string $territoryId,
        string $officeType,
        string $seed,
        bool $active,
        bool $partyActive = true,
    ): string {
        $partyId = $this->id("party:{$seed}");
        $candidateId = $this->id("candidate:{$seed}");
        $candidacyId = $this->id("candidacy:{$seed}");

        DB::table('electoral_parties')->insert([
            'id' => $partyId,
            'source_system' => 'test',
            'source_key' => "party:{$seed}",
            'name' => "Party {$seed}",
            'status' => $partyActive ? 'active' : 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('electoral_candidates')->insert([
            'id' => $candidateId,
            'source_system' => 'test',
            'source_key' => "candidate:{$seed}",
            'full_name' => "Candidate {$seed}",
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('electoral_candidacies')->insert([
            'id' => $candidacyId,
            'candidate_id' => $candidateId,
            'political_party_id' => $partyId,
            'territory_id' => $territoryId,
            'office_type' => $officeType,
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => "candidacy:{$seed}",
            'status' => $active ? 'active' : 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $candidacyId;
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
