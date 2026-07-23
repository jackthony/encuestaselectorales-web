<?php

namespace Tests\Feature;

use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use App\Application\Data\TerritoryData;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Survey\RoundAvailability;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

final class SurveyReadApiTest extends TestCase
{
    public function test_territory_search_validates_and_returns_typed_results(): void
    {
        $catalog = Mockery::mock(TerritoryCatalog::class);
        $catalog->expects('searchPublished')
            ->with('Callao', 20)
            ->andReturn([$this->territory()]);
        $this->app->instance(TerritoryCatalog::class, $catalog);

        $this->getJson('/api/territories/search?q=Callao')
            ->assertOk()
            ->assertJsonPath('data.0.scope_type', 'region')
            ->assertJsonPath('data.0.name', 'Callao');

        $this->getJson('/api/territories/search?q=C')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_active_round_and_blocked_state_have_stable_shapes(): void
    {
        $rounds = Mockery::mock(SurveyRoundQuery::class);
        $rounds->expects('activeNational')->andReturn([$this->round()]);
        $rounds->expects('forTerritory')
            ->with('territory-id')
            ->andReturn(new RoundResult(
                RoundAvailability::Blocked,
                territory: $this->territory(),
                reason: 'candidate_data_unavailable',
            ));
        $this->app->instance(SurveyRoundQuery::class, $rounds);

        $this->getJson('/api/survey-rounds')
            ->assertOk()
            ->assertJsonPath('data.0.territory.scope_type', 'region')
            ->assertJsonPath('data.0.office_type', 'regional_governor');

        $this->getJson('/api/territories/territory-id/survey-round')
            ->assertOk()
            ->assertJsonPath('data.state', 'blocked')
            ->assertJsonPath('data.reason', 'candidate_data_unavailable');
    }

    private function territory(): TerritoryData
    {
        return new TerritoryData(
            id: 'territory-id',
            officialCode: '070000',
            name: 'Callao',
            slug: 'callao-region',
            scopeType: 'region',
        );
    }

    private function round(): SurveyRoundData
    {
        return new SurveyRoundData(
            id: 'round-id',
            territory: $this->territory(),
            roundNumber: 1,
            electionCycle: 'ERM2026',
            officeType: 'regional_governor',
            title: 'Encuesta regional del Callao',
            readinessState: 'active',
            blockedReason: null,
            opensAt: CarbonImmutable::parse('2026-07-21 00:00:00', 'America/Lima'),
            closesAt: CarbonImmutable::parse('2026-08-05 23:59:59', 'America/Lima'),
            options: [],
        );
    }
}
