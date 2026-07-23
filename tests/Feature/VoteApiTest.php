<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class VoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('vote.encryption_key', '01234567890123456789012345678901');
        config()->set('vote.ip_hmac_key', 'ip-hmac-key-0123456789012345678901');
        config()->set('vote.device_hmac_key', 'device-key-012345678901234567890');
        config()->set('vote.encryption_key_version', 7);
        config()->set('vote.max_gps_accuracy_meters', 100);
        config()->set('vote.territory_bounds.070000', [
            'lat_min' => -12.14,
            'lat_max' => -11.99,
            'lng_min' => -77.19,
            'lng_max' => -77.03,
        ]);
    }

    public function test_valid_vote_is_encrypted_and_duplicate_is_rejected(): void
    {
        $ids = $this->seedRound();
        $payload = $this->payload($ids);
        $server = [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.50',
        ];

        $this->withServerVariables($server)
            ->postJson('/api/votes', $payload)
            ->assertCreated()
            ->assertJsonPath('code', 'vote_registered')
            ->assertJsonStructure(['data' => ['vote_id']])
            ->assertCookie('encuestas_device');

        $vote = DB::table('interactive_votes')->first();
        $this->assertNotNull($vote);
        $this->assertSame(7, (int) $vote->ip_encryption_key_version);
        $this->assertNotSame('203.0.113.10', $vote->ip_ciphertext);
        $this->assertSame('203.0.113.10', openssl_decrypt(
            $vote->ip_ciphertext,
            'aes-256-gcm',
            '01234567890123456789012345678901',
            OPENSSL_RAW_DATA,
            $vote->ip_nonce,
            $vote->ip_auth_tag,
        ));

        $this->withServerVariables($server)
            ->postJson('/api/votes', $payload)
            ->assertConflict()
            ->assertJsonPath('code', 'duplicate_vote');

        $this->assertDatabaseCount('interactive_votes', 1);
    }

    public function test_invalid_or_outside_location_persists_nothing(): void
    {
        $ids = $this->seedRound();

        $this->postJson('/api/votes', $this->payload($ids, [
            'gps_latitude' => -13.0,
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('code', 'geographic_validation_failed');

        $this->postJson('/api/votes', [
            'survey_round_id' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'survey_round_id',
                'survey_option_id',
                'gps_latitude',
            ]);

        $this->assertDatabaseCount('interactive_votes', 0);
    }

    public function test_closed_round_is_rejected(): void
    {
        $ids = $this->seedRound();
        DB::table('survey_rounds')->where('id', $ids['round'])->update([
            'opens_at' => now()->subDays(2),
            'closes_at' => now()->subDay(),
        ]);

        $this->postJson('/api/votes', $this->payload($ids))
            ->assertConflict()
            ->assertJsonPath('code', 'round_unavailable');

        $this->assertDatabaseCount('interactive_votes', 0);
    }

    public function test_inactive_candidate_option_is_rejected(): void
    {
        $ids = $this->seedRound();
        DB::table('electoral_candidates')->where('id', $ids['candidate'])->update([
            'status' => 'inactive',
        ]);

        $this->postJson('/api/votes', $this->payload($ids))
            ->assertConflict()
            ->assertJsonPath('code', 'option_unavailable');

        $this->assertDatabaseCount('interactive_votes', 0);
    }

    public function test_production_uses_server_observed_ip_without_trusting_forwarded_header(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set('vote.trusted_proxies', ['203.0.113.0/24']);
        $ids = $this->seedRound();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.99',
        ])
            ->postJson('/api/votes', $this->payload($ids))
            ->assertCreated();

        $vote = DB::table('interactive_votes')->first();
        $this->assertSame('203.0.113.10', openssl_decrypt(
            $vote->ip_ciphertext,
            'aes-256-gcm',
            '01234567890123456789012345678901',
            OPENSSL_RAW_DATA,
            $vote->ip_nonce,
            $vote->ip_auth_tag,
        ));
    }

    public function test_production_accepts_cloudflare_ip_only_from_a_trusted_proxy(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set('vote.trusted_proxies', ['203.0.113.0/24']);
        $ids = $this->seedRound();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_CF_CONNECTING_IP' => '198.51.100.50',
        ])
            ->postJson('/api/votes', $this->payload($ids))
            ->assertCreated();

        $vote = DB::table('interactive_votes')->first();
        $this->assertSame('198.51.100.50', openssl_decrypt(
            $vote->ip_ciphertext,
            'aes-256-gcm',
            '01234567890123456789012345678901',
            OPENSSL_RAW_DATA,
            $vote->ip_nonce,
            $vote->ip_auth_tag,
        ));

        $this->withServerVariables([
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_CF_CONNECTING_IP' => '198.51.100.60',
        ])
            ->postJson('/api/votes', $this->payload($ids, [
                'device_token' => 'another-device-token-012345678901234567890',
            ]))
            ->assertServiceUnavailable()
            ->assertJsonPath('code', 'network_validation_failed');
    }

    public function test_legacy_vote_payload_is_adapted_without_using_legacy_php(): void
    {
        $ids = $this->seedRound();
        DB::table('legacy_mappings')->insert([
            [
                'id' => $this->id('mapping-round'),
                'source_table' => 'encuestas',
                'legacy_id' => 'legacy-round',
                'target_table' => 'survey_rounds',
                'target_id' => $ids['round'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->id('mapping-candidate'),
                'source_table' => 'candidates',
                'legacy_id' => '42',
                'target_table' => 'electoral_candidates',
                'target_id' => $ids['candidate'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
            ->postJson('/api/votar.php?encuesta_id=legacy-round', [
                'ubigeo_votacion' => 'callao',
                'tipo_voto' => 'candidato',
                'candidato_id' => 42,
                'gps_lat' => -12.05,
                'gps_lng' => -77.10,
                'gps_accuracy_meters' => 15,
                'interaction_time_ms' => 1200,
                'browser_fingerprint' => 'legacy-browser-fingerprint',
                'device_token' => 'legacy-device-token-012345678901234567890123456789',
            ])
            ->assertCreated()
            ->assertJsonPath('code', 'vote_registered')
            ->assertJsonStructure(['device_token', 'vote_id']);

        $this->assertDatabaseCount('interactive_votes', 1);
    }

    public function test_key_failure_rolls_back_vote(): void
    {
        $ids = $this->seedRound();
        config()->set('vote.encryption_key', 'too-short');

        $this->postJson('/api/votes', $this->payload($ids))
            ->assertInternalServerError();

        $this->assertDatabaseCount('interactive_votes', 0);
    }

    /**
     * @return array{territory:string,candidate:string,round:string,option:string}
     */
    private function seedRound(): array
    {
        $territory = $this->id('territory');
        $party = $this->id('party');
        $candidate = $this->id('candidate');
        $candidacy = $this->id('candidacy');
        $round = $this->id('round');
        $option = $this->id('option');
        $now = now();

        DB::table('electoral_territories')->insert([
            'id' => $territory,
            'official_code' => '070000',
            'scope_type' => 'region',
            'name' => 'Callao',
            'canonical_name' => 'callao',
            'slug' => 'callao-region',
            'source_system' => 'test',
            'source_key' => 'territory',
            'publication_state' => 'published',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_parties')->insert([
            'id' => $party,
            'source_system' => 'test',
            'source_key' => 'party',
            'name' => 'Partido Real',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_candidates')->insert([
            'id' => $candidate,
            'source_system' => 'test',
            'source_key' => 'candidate',
            'full_name' => 'Persona Candidata',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('electoral_candidacies')->insert([
            'id' => $candidacy,
            'candidate_id' => $candidate,
            'political_party_id' => $party,
            'territory_id' => $territory,
            'office_type' => 'regional_governor',
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => 'candidacy',
            'status' => 'active',
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
            'opens_at' => $now->copy()->subDay(),
            'closes_at' => $now->copy()->addDay(),
            'publication_state' => 'published',
            'readiness_state' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('survey_options')->insert([
            'id' => $option,
            'survey_round_id' => $round,
            'candidacy_id' => $candidacy,
            'display_order' => 1,
            'status' => 'eligible',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return compact('territory', 'candidate', 'round', 'option');
    }

    /**
     * @param  array{territory:string,candidate:string,round:string,option:string}  $ids
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $ids, array $overrides = []): array
    {
        return $overrides + [
            'survey_round_id' => $ids['round'],
            'survey_option_id' => $ids['option'],
            'gps_latitude' => -12.05,
            'gps_longitude' => -77.10,
            'gps_accuracy_meters' => 15,
            'interaction_time_ms' => 1200,
            'browser_fingerprint' => 'browser-fingerprint-0001',
            'device_token' => 'device-token-01234567890123456789012345678901',
        ];
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
