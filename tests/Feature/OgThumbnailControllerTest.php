<?php

namespace Tests\Feature;

use App\Domain\Survey\PublicationState;
use App\Domain\Survey\RoundAvailability;
use App\Infrastructure\Persistence\Models\Candidacy;
use App\Infrastructure\Persistence\Models\Candidate;
use App\Infrastructure\Persistence\Models\PoliticalParty;
use App\Infrastructure\Persistence\Models\SurveyOption;
use App\Infrastructure\Persistence\Models\SurveyRound;
use App\Infrastructure\Persistence\Models\Territory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OgThumbnailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_png_for_a_territory_with_an_active_round(): void
    {
        $this->createActiveRoundTerritory();

        $response = $this->get('/encuestas/district/carmen-de-la-legua-reynoso/og-image.png');

        $response->assertOk();
        self::assertSame('image/png', $response->headers->get('Content-Type'));

        $info = getimagesizefromstring($response->content());
        self::assertSame(1200, $info[0]);
        self::assertSame(630, $info[1]);
    }

    public function test_returns_404_for_an_unknown_territory(): void
    {
        $response = $this->get('/encuestas/district/no-existe/og-image.png');

        $response->assertNotFound();
    }

    public function test_returns_404_for_a_territory_without_an_active_round(): void
    {
        Territory::query()->create([
            'official_code' => '070999',
            'scope_type' => 'district',
            'name' => 'Sin Ronda',
            'canonical_name' => 'sin ronda',
            'slug' => 'sin-ronda',
            'parent_id' => null,
            'source_system' => 'test',
            'source_key' => 'territory:070999',
            'publication_state' => PublicationState::Published->value,
            'published_at' => now(),
            'source_url' => null,
        ]);

        $response = $this->get('/encuestas/district/sin-ronda/og-image.png');

        $response->assertNotFound();
    }

    private function createActiveRoundTerritory(): void
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

        SurveyOption::query()->create([
            'survey_round_id' => $round->getKey(),
            'candidacy_id' => $candidacy->getKey(),
            'display_order' => 1,
            'status' => 'eligible',
        ]);
    }
}
