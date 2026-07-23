<?php

namespace Tests\Feature;

use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Survey\RoundAvailability;
use App\Domain\Survey\SurveyOptionEligibility;
use App\Infrastructure\Persistence\Models\Candidacy;
use App\Infrastructure\Persistence\Models\SurveyRound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SurveyRoundQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_homonymous_territories_keep_type_and_ancestry(): void
    {
        $region = $this->territory('region', '070000', 'Callao');
        $province = $this->territory('province', '070100', 'Callao', $region);
        $this->territory('district', '070101', 'Callao', $province);

        $results = app(TerritoryCatalog::class)->searchPublished('Callao');

        $this->assertCount(3, $results);
        $this->assertSame(['region', 'province', 'district'], array_column(
            array_map(static fn ($territory): array => $territory->toArray(), $results),
            'scope_type',
        ));
        $this->assertSame('province', $results[2]->ancestors[0]['scope_type']);
        $this->assertSame('region', $results[2]->ancestors[1]['scope_type']);
    }

    public function test_active_round_returns_scoped_candidate_and_media_fallback_signal(): void
    {
        $territory = $this->territory('province', '150100', 'Lima');
        $party = $this->catalogRecord('electoral_parties', 'party', [
            'name' => 'Partido Real',
            'acronym' => 'PR',
            'logo_url' => 'https://media.example/party.png',
            'status' => 'active',
        ]);
        $candidate = $this->catalogRecord('electoral_candidates', 'candidate', [
            'full_name' => 'Persona Candidata',
            'photo_url' => null,
            'status' => 'active',
        ]);
        $candidacy = $this->catalogRecord('electoral_candidacies', 'candidacy', [
            'candidate_id' => $candidate,
            'political_party_id' => $party,
            'territory_id' => $territory,
            'office_type' => 'provincial_mayor',
            'election_cycle' => 'ERM2026',
            'status' => 'active',
        ]);
        $round = $this->catalogRecord('survey_rounds', 'round', [
            'territory_id' => $territory,
            'round_number' => 1,
            'election_cycle' => 'ERM2026',
            'survey_type' => 'online_owned',
            'office_type' => 'provincial_mayor',
            'title' => 'Encuesta provincial de Lima',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'publication_state' => 'published',
            'readiness_state' => 'active',
        ]);
        $this->catalogRecord('survey_options', 'option', [
            'survey_round_id' => $round,
            'candidacy_id' => $candidacy,
            'display_order' => 1,
            'status' => 'eligible',
        ]);

        $result = app(SurveyRoundQuery::class)->forTerritory($territory);

        $this->assertSame(RoundAvailability::Active, $result->state);
        $this->assertSame('province', $result->round?->territory->scopeType);
        $this->assertSame('Persona Candidata', $result->round?->options[0]->candidateName);
        $this->assertNull($result->round?->options[0]->candidatePhotoUrl);
        $this->assertSame('https://media.example/party.png', $result->round?->options[0]->partyLogoUrl);
    }

    public function test_candidate_less_territory_is_explicitly_blocked(): void
    {
        $territory = $this->territory('district', '150101', 'Lima');

        $result = app(SurveyRoundQuery::class)->forTerritory($territory);

        $this->assertSame(RoundAvailability::Blocked, $result->state);
        $this->assertSame('candidate_data_unavailable', $result->reason);
    }

    public function test_publication_window_distinguishes_scheduled_and_closed_rounds(): void
    {
        $territory = $this->territory('district', '150122', 'Miraflores');
        $party = $this->catalogRecord('electoral_parties', 'window-party', [
            'name' => 'Partido Real',
            'status' => 'active',
        ]);
        $candidate = $this->catalogRecord('electoral_candidates', 'window-candidate', [
            'full_name' => 'Persona Candidata',
            'status' => 'active',
        ]);
        $this->catalogRecord('electoral_candidacies', 'window-candidacy', [
            'candidate_id' => $candidate,
            'political_party_id' => $party,
            'territory_id' => $territory,
            'office_type' => 'district_mayor',
            'election_cycle' => 'ERM2026',
            'status' => 'active',
        ]);
        $round = $this->catalogRecord('survey_rounds', 'window-round', [
            'territory_id' => $territory,
            'round_number' => 1,
            'election_cycle' => 'ERM2026',
            'survey_type' => 'online_owned',
            'office_type' => 'district_mayor',
            'title' => 'Encuesta distrital de Miraflores',
            'opens_at' => now()->addDay(),
            'closes_at' => now()->addDays(2),
            'publication_state' => 'published',
            'readiness_state' => 'active',
        ]);

        $this->assertSame(
            RoundAvailability::Scheduled,
            app(SurveyRoundQuery::class)->forTerritory($territory)->state,
        );

        DB::table('survey_rounds')->where('id', $round)->update([
            'opens_at' => now()->subDays(2),
            'closes_at' => now()->subDay(),
        ]);

        $this->assertSame(
            RoundAvailability::Closed,
            app(SurveyRoundQuery::class)->forTerritory($territory)->state,
        );
    }

    public function test_option_policy_rejects_another_territory(): void
    {
        $round = new SurveyRound([
            'territory_id' => $this->id('territory-one'),
            'office_type' => 'district_mayor',
            'election_cycle' => 'ERM2026',
        ]);
        $candidacy = new Candidacy([
            'territory_id' => $this->id('territory-two'),
            'office_type' => 'district_mayor',
            'election_cycle' => 'ERM2026',
            'status' => 'active',
        ]);

        $this->expectException(\DomainException::class);

        app(SurveyOptionEligibility::class)->assertEligible($round, $candidacy);
    }

    private function territory(
        string $scopeType,
        string $officialCode,
        string $name,
        ?string $parentId = null,
    ): string {
        return $this->catalogRecord('electoral_territories', "{$scopeType}:{$officialCode}", [
            'official_code' => $officialCode,
            'scope_type' => $scopeType,
            'name' => $name,
            'canonical_name' => strtolower($name),
            'slug' => strtolower("{$name}-{$scopeType}"),
            'parent_id' => $parentId,
            'publication_state' => 'published',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function catalogRecord(string $table, string $seed, array $attributes): string
    {
        $id = $this->id("{$table}:{$seed}");

        $defaults = [
            'id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($table, 'source_system')) {
            $defaults['source_system'] = 'test';
            $defaults['source_key'] = $seed;
        }

        DB::table($table)->insert($attributes + $defaults);

        return $id;
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
