<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicPortalRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_page_renders_candidate_party_and_vote_contract(): void
    {
        $territoryId = $this->id('territory:lima-province');
        $partyId = $this->id('party:real');
        $candidateId = $this->id('candidate:real');
        $candidacyId = $this->id('candidacy:real');
        $roundId = $this->id('round:lima-province');
        $optionId = $this->id('option:real');
        $timestamps = ['created_at' => now(), 'updated_at' => now()];

        DB::table('electoral_territories')->insert([
            'id' => $territoryId,
            'official_code' => '150100',
            'scope_type' => 'province',
            'name' => 'Lima',
            'canonical_name' => 'lima',
            'slug' => 'lima-province',
            'source_system' => 'test',
            'source_key' => 'territory:lima-province',
            'publication_state' => 'published',
        ] + $timestamps);
        DB::table('electoral_parties')->insert([
            'id' => $partyId,
            'name' => 'Partido Real',
            'acronym' => 'PR',
            'logo_url' => 'https://media.example/party.png',
            'source_system' => 'test',
            'source_key' => 'party:real',
            'status' => 'active',
        ] + $timestamps);
        DB::table('electoral_candidates')->insert([
            'id' => $candidateId,
            'full_name' => 'Persona Candidata',
            'photo_url' => null,
            'source_system' => 'test',
            'source_key' => 'candidate:real',
            'status' => 'active',
        ] + $timestamps);
        DB::table('electoral_candidacies')->insert([
            'id' => $candidacyId,
            'candidate_id' => $candidateId,
            'political_party_id' => $partyId,
            'territory_id' => $territoryId,
            'office_type' => 'provincial_mayor',
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => 'candidacy:real',
            'status' => 'active',
        ] + $timestamps);
        DB::table('survey_rounds')->insert([
            'id' => $roundId,
            'territory_id' => $territoryId,
            'round_number' => 1,
            'election_cycle' => 'ERM2026',
            'survey_type' => 'online_owned',
            'office_type' => 'provincial_mayor',
            'title' => 'Encuesta provincial de Lima',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'publication_state' => 'published',
            'readiness_state' => 'active',
            'source_system' => 'test',
            'source_key' => 'round:lima-province',
        ] + $timestamps);
        DB::table('survey_options')->insert([
            'id' => $optionId,
            'survey_round_id' => $roundId,
            'candidacy_id' => $candidacyId,
            'display_order' => 1,
            'status' => 'eligible',
        ] + $timestamps);

        $response = $this->get('/encuestas/province/lima-province');

        $response->assertOk()
            ->assertSee('Provincia')
            ->assertSee('Persona Candidata')
            ->assertSee('Partido Real')
            ->assertSee('https://media.example/party.png', false)
            ->assertSee('data-survey-round-id="'.$roundId.'"', false)
            ->assertSee('value="'.$optionId.'"', false)
            ->assertSee('/assets/img/default-face.svg', false)
            ->assertSee('Referencia territorial')
            ->assertSee('zona aproximada')
            ->assertDontSee('Ámbito validado');
    }

    public function test_candidate_less_scope_is_explicitly_blocked(): void
    {
        DB::table('electoral_territories')->insert([
            'id' => $this->id('territory:empty'),
            'official_code' => '150101',
            'scope_type' => 'district',
            'name' => 'Lima',
            'canonical_name' => 'lima',
            'slug' => 'lima-district',
            'source_system' => 'test',
            'source_key' => 'territory:empty',
            'publication_state' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/encuestas/district/lima-district')
            ->assertOk()
            ->assertSee('Distrito')
            ->assertSee('Aún no hay candidaturas verificadas para esta encuesta')
            ->assertDontSee('candidato de ejemplo');
    }

    public function test_legacy_district_url_redirects_to_canonical_scope(): void
    {
        DB::table('electoral_territories')->insert([
            'id' => $this->id('territory:legacy'),
            'official_code' => '150122',
            'scope_type' => 'district',
            'name' => 'Miraflores',
            'canonical_name' => 'miraflores',
            'slug' => 'miraflores',
            'source_system' => 'test',
            'source_key' => 'territory:legacy',
            'publication_state' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/distrito.php?slug=miraflores')
            ->assertRedirect('/encuestas/district/miraflores')
            ->assertStatus(301);
    }

    public function test_candidate_profile_renders_without_legacy_helpers(): void
    {
        $territoryId = $this->id('profile:territory');
        $partyId = $this->id('profile:party');
        $candidateId = $this->id('profile:candidate');
        $timestamps = ['created_at' => now(), 'updated_at' => now()];

        DB::table('electoral_territories')->insert([
            'id' => $territoryId,
            'official_code' => '070000',
            'scope_type' => 'region',
            'name' => 'Callao',
            'canonical_name' => 'callao',
            'slug' => 'callao-region',
            'source_system' => 'test',
            'source_key' => 'profile:territory',
            'publication_state' => 'published',
        ] + $timestamps);
        DB::table('electoral_parties')->insert([
            'id' => $partyId,
            'name' => 'Partido Perfil',
            'logo_url' => 'https://media.example/profile-party.png',
            'source_system' => 'test',
            'source_key' => 'profile:party',
            'status' => 'active',
        ] + $timestamps);
        DB::table('electoral_candidates')->insert([
            'id' => $candidateId,
            'full_name' => 'Candidata de Perfil',
            'photo_url' => null,
            'source_system' => 'test',
            'source_key' => 'profile:candidate',
            'status' => 'active',
        ] + $timestamps);
        DB::table('electoral_candidacies')->insert([
            'id' => $this->id('profile:candidacy'),
            'candidate_id' => $candidateId,
            'political_party_id' => $partyId,
            'territory_id' => $territoryId,
            'office_type' => 'regional_governor',
            'election_cycle' => 'ERM2026',
            'source_system' => 'test',
            'source_key' => 'profile:candidacy',
            'status' => 'active',
        ] + $timestamps);

        $this->get('/candidato.php?id='.$candidateId)
            ->assertOk()
            ->assertSee('Candidata de Perfil')
            ->assertSee('Partido Perfil')
            ->assertSee('/encuestas/region/callao-region', false)
            ->assertSee('/assets/img/default-face.svg', false);
    }

    private function id(string $seed): string
    {
        return strtoupper(substr(hash('sha256', $seed), 0, 26));
    }
}
