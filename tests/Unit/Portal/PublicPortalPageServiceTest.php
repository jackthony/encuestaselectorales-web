<?php

namespace Tests\Unit\Portal;

use App\Application\Data\CandidateOptionData;
use App\Application\Data\RoundResult;
use App\Application\Data\SurveyRoundData;
use App\Application\Data\TerritoryData;
use App\Application\Portal\PublicPortalPageService;
use App\Application\Portal\SurveyRoundCardFactory;
use App\Application\Portal\SurveyRoundDetailFactory;
use App\Application\Portal\SurveyShareDescriptionFactory;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Survey\RoundAvailability;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

final class PublicPortalPageServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_home_view_data_starts_without_selection(): void
    {
        $territories = Mockery::mock(TerritoryCatalog::class);
        $territories->shouldNotReceive('findPublishedByScopeAndSlug');

        $rounds = Mockery::mock(SurveyRoundQuery::class);
        $rounds->shouldReceive('activeNational')->once()->andReturn([$this->makeRound()]);
        $rounds->shouldNotReceive('forTerritory');

        $service = new PublicPortalPageService(
            $territories,
            $rounds,
            new SurveyRoundCardFactory(),
            new SurveyRoundDetailFactory(),
            new SurveyShareDescriptionFactory(),
        );

        $viewData = $service->homeViewData();

        self::assertNull($viewData['selectedRound']);
        self::assertSame('Encuestas Electorales Perú 2026 - Transparencia y Datos', $viewData['pageTitle']);
        self::assertCount(1, $viewData['rondasAbiertas']);
        self::assertSame('website', $viewData['shareType']);
        self::assertSame(route('home'), $viewData['shareUrl']);
    }

    public function test_home_view_data_uses_explicit_selection_only(): void
    {
        $territory = new TerritoryData(
            id: 'territory-1',
            officialCode: '070000',
            name: 'Callao',
            slug: 'callao-region',
            scopeType: 'region',
        );

        $territories = Mockery::mock(TerritoryCatalog::class);
        $territories->shouldReceive('findPublishedByScopeAndSlug')
            ->once()
            ->with('region', 'callao-region')
            ->andReturn($territory);

        $rounds = Mockery::mock(SurveyRoundQuery::class);
        $rounds->shouldReceive('activeNational')->once()->andReturn([$this->makeRound()]);
        $rounds->shouldReceive('forTerritory')->once()->with('territory-1')->andReturn($this->makeRoundResult($territory));

        $service = new PublicPortalPageService(
            $territories,
            $rounds,
            new SurveyRoundCardFactory(),
            new SurveyRoundDetailFactory(),
            new SurveyShareDescriptionFactory(),
        );

        $viewData = $service->homeViewData('region', 'callao-region');

        self::assertIsArray($viewData['selectedRound']);
        self::assertSame('Callao', $viewData['selectedRound']['territory']['name']);
        self::assertSame(route('home', ['scope' => 'region', 'slug' => 'callao-region']), $viewData['shareUrl']);
    }

    public function test_scope_view_data_sets_share_image_url_for_active_round(): void
    {
        $territory = new TerritoryData(
            id: 'territory-1',
            officialCode: '070000',
            name: 'Callao',
            slug: 'callao-region',
            scopeType: 'region',
        );

        $territories = Mockery::mock(TerritoryCatalog::class);
        $territories->shouldNotReceive('findPublishedByScopeAndSlug');

        $rounds = Mockery::mock(SurveyRoundQuery::class);
        $rounds->shouldReceive('forTerritory')->once()->with('territory-1')->andReturn($this->makeRoundResult($territory));

        $service = new PublicPortalPageService(
            $territories,
            $rounds,
            new SurveyRoundCardFactory(),
            new SurveyRoundDetailFactory(),
            new SurveyShareDescriptionFactory(),
        );

        $viewData = $service->scopeViewData($territory, 'https://example.test/encuestas/region/callao-region');

        self::assertSame(
            route('surveys.og-image', ['scope' => 'region', 'slug' => 'callao-region']),
            $viewData['shareImage'],
        );
    }

    public function test_scope_view_data_has_no_share_image_when_round_is_not_active(): void
    {
        $territory = new TerritoryData(
            id: 'territory-1',
            officialCode: '070000',
            name: 'Callao',
            slug: 'callao-region',
            scopeType: 'region',
        );

        $territories = Mockery::mock(TerritoryCatalog::class);
        $rounds = Mockery::mock(SurveyRoundQuery::class);
        $rounds->shouldReceive('forTerritory')->once()->with('territory-1')->andReturn(
            new RoundResult(state: RoundAvailability::Unavailable, territory: $territory, reason: 'no_active_round'),
        );

        $service = new PublicPortalPageService(
            $territories,
            $rounds,
            new SurveyRoundCardFactory(),
            new SurveyRoundDetailFactory(),
            new SurveyShareDescriptionFactory(),
        );

        $viewData = $service->scopeViewData($territory, 'https://example.test/x');

        self::assertNull($viewData['shareImage']);
    }

    private function makeRound(): SurveyRoundData
    {
        $territory = new TerritoryData(
            id: 'territory-1',
            officialCode: '070000',
            name: 'Callao',
            slug: 'callao-region',
            scopeType: 'region',
        );

        return new SurveyRoundData(
            id: 'round-1',
            territory: $territory,
            roundNumber: 1,
            electionCycle: '2026',
            officeType: 'regional',
            title: 'Encuesta regional del Callao',
            readinessState: 'active',
            blockedReason: null,
            opensAt: CarbonImmutable::parse('2026-07-23 00:00:00', 'America/Lima'),
            closesAt: CarbonImmutable::parse('2026-08-23 00:00:00', 'America/Lima'),
            lastVoteAt: null,
            options: [
                new CandidateOptionData(
                    optionId: 'option-1',
                    candidacyId: 'cand-1',
                    candidateId: 'candidate-1',
                    candidateName: 'Candidatura 1',
                    candidatePhotoUrl: null,
                    partyId: 'party-1',
                    partyName: 'Partido 1',
                    partyAcronym: null,
                    partyLogoUrl: null,
                    displayOrder: 1,
                    voteCount: 0,
                ),
            ],
            totalVotes: 0,
        );
    }

    private function makeRoundResult(TerritoryData $territory): RoundResult
    {
        return new RoundResult(
            state: RoundAvailability::Active,
            round: $this->makeRound(),
            territory: $territory,
            reason: null,
        );
    }
}
