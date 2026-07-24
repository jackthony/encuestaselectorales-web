<?php

namespace Tests\Feature\Api;

use App\Domain\Survey\PublicationState;
use App\Domain\Survey\RoundAvailability;
use App\Infrastructure\Persistence\Models\Candidate;
use App\Infrastructure\Persistence\Models\Candidacy;
use App\Infrastructure\Persistence\Models\InteractiveVote;
use App\Infrastructure\Persistence\Models\PoliticalParty;
use App\Infrastructure\Persistence\Models\SurveyOption;
use App\Infrastructure\Persistence\Models\SurveyRound;
use App\Infrastructure\Persistence\Models\Territory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_is_persisted_and_returns_device_token(): void
    {
        $territory = Territory::query()->create([
            'official_code' => '070103',
            'scope_type' => 'district',
            'name' => 'Carmen de la Legua-Reynoso',
            'canonical_name' => 'carmen de la legua reynoso',
            'slug' => 'carmen-de-la-legua-reynoso',
            'parent_id' => null,
            'source_system' => 'test',
            'source_key' => 'territory:070103',
            'publication_state' => PublicationState::Published->value,
            'published_at' => now(),
            'source_url' => null,
        ]);

        $party = PoliticalParty::query()->create([
            'source_system' => 'test',
            'source_key' => 'party:1',
            'name' => 'Partido de Prueba',
            'acronym' => 'PP',
            'logo_url' => null,
            'logo_storage_disk' => null,
            'logo_storage_path' => null,
            'logo_source_attribution' => null,
            'source_url' => null,
            'status' => 'active',
        ]);

        $candidate = Candidate::query()->create([
            'source_system' => 'test',
            'source_key' => 'candidate:1',
            'full_name' => 'Candidato de Prueba',
            'photo_url' => null,
            'photo_storage_disk' => null,
            'photo_storage_path' => null,
            'photo_source_attribution' => null,
            'source_url' => null,
            'status' => 'active',
        ]);

        $candidacy = Candidacy::query()->create([
            'candidate_id' => $candidate->getKey(),
            'political_party_id' => $party->getKey(),
            'territory_id' => $territory->getKey(),
            'office_type' => 'district_mayor',
            'election_cycle' => '2026',
            'source_system' => 'test',
            'source_key' => 'candidacy:1',
            'ballot_order' => 1,
            'status' => 'active',
            'source_file' => null,
            'source_row' => null,
            'source_url' => null,
            'retrieved_at' => now(),
        ]);

        $round = SurveyRound::query()->create([
            'territory_id' => $territory->getKey(),
            'round_number' => 1,
            'election_cycle' => '2026',
            'survey_type' => 'district_mayor',
            'office_type' => 'district_mayor',
            'title' => 'Encuesta distrital de Carmen de la Legua-Reynoso',
            'opens_at' => CarbonImmutable::now()->subHour(),
            'closes_at' => CarbonImmutable::now()->addHour(),
            'publication_state' => PublicationState::Published->value,
            'readiness_state' => RoundAvailability::Active->value,
            'blocked_reason' => null,
            'source_system' => 'test',
            'source_key' => 'round:1',
            'source_url' => null,
        ]);

        $option = SurveyOption::query()->create([
            'survey_round_id' => $round->getKey(),
            'candidacy_id' => $candidacy->getKey(),
            'display_order' => 1,
            'status' => 'eligible',
        ]);

        $response = $this->postJson('/api/votes', [
            'survey_round_id' => $round->getKey(),
            'survey_option_id' => $option->getKey(),
            'gps_latitude' => -12.057222,
            'gps_longitude' => -77.095833,
            'gps_accuracy_meters' => 18,
            'interaction_time_ms' => 550,
            'browser_fingerprint' => 'Linux|1|1920x1080|24|8|8|America/Lima',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'code',
                'message',
                'device_token',
                'data' => [
                    'vote_id',
                    'result' => [
                        'state',
                        'reason',
                        'territory',
                        'round',
                    ],
                ],
            ])
            ->assertCookie('encuestas_device');

        self::assertSame('success', $response->json('status'));
        self::assertSame('vote_registered', $response->json('code'));
        self::assertIsString($response->json('device_token'));
        self::assertSame(64, strlen((string) $response->json('device_token')));
        self::assertSame('active', $response->json('data.result.state'));
        self::assertSame(1, $response->json('data.result.round.total_votes'));
        self::assertNotNull($response->json('data.result.round.last_vote_at'));

        $this->assertDatabaseCount('interactive_votes', 1);
        $this->assertDatabaseHas('interactive_votes', [
            'survey_round_id' => $round->getKey(),
            'survey_option_id' => $option->getKey(),
            'validated_territory_id' => $territory->getKey(),
            'geo_validation_result' => 'inside',
            'status' => 'accepted',
        ]);

        self::assertSame(
            1,
            InteractiveVote::query()
                ->where('survey_round_id', $round->getKey())
                ->where('survey_option_id', $option->getKey())
                ->count(),
        );
    }
}
