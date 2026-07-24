<?php

namespace App\Application\Portal;

use App\Application\Data\TerritoryData;
use App\Domain\Catalog\Contracts\TerritoryCatalog;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Domain\Survey\RoundAvailability;

final readonly class PublicPortalPageService
{
    public function __construct(
        private TerritoryCatalog $territories,
        private SurveyRoundQuery $rounds,
        private SurveyRoundCardFactory $cards,
        private SurveyRoundDetailFactory $details,
        private SurveyShareDescriptionFactory $shareDescriptions,
    ) {}

    /** @return array<string, mixed> */
    public function homeViewData(?string $selectedScope = null, ?string $selectedSlug = null): array
    {
        $pageTitle = 'Encuestas Electorales Perú 2026 - Transparencia y Datos';
        $pageDescription = 'Sondeos ciudadanos por región, provincia y distrito para las Elecciones Regionales y Municipales 2026.';
        $rounds = $this->rounds->activeNational();
        $selected = $this->resolveSelectedRound($selectedScope, $selectedSlug, $rounds);
        $shareDescription = $selected
            ? $this->shareDescriptions->forScope(
                $selected['territory']['scope_type'],
                $selected['territory']['name'],
                $selected['round'],
            )
            : $pageDescription;

        return [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'selectedRound' => $selected,
            'rondasAbiertas' => array_map(
                fn ($round): array => $this->cards->make($round),
                $rounds,
            ),
            'shareTitle' => $pageTitle,
            'shareDescription' => $shareDescription,
            'shareType' => 'website',
            'shareUrl' => $selected
                ? route('home', [
                    'scope' => $selected['territory']['scope_type'],
                    'slug' => $selected['territory']['slug'],
                ])
                : route('home'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function scopeViewData(TerritoryData $territory, string $currentUrl): array
    {
        $result = $this->details->make($this->rounds->forTerritory($territory->id));
        $scopeLabel = $this->scopeLabel($territory->scopeType);
        $shareDescription = $this->shareDescriptions->forScope($scopeLabel, $territory->name, $result['round'] ?? null);

        return [
            'territory' => $result['territory'],
            'roundState' => $result['state'],
            'blockedReason' => $result['reason'],
            'activeRound' => $result['round'],
            'totalVotes' => $result['total_votes'],
            'topOptions' => $result['top_options'],
            'rankedOptions' => $result['ranked_options'],
            'scopeLabel' => $scopeLabel,
            'pageTitle' => "{$scopeLabel} {$territory->name} | EncuestasElectorales.pe",
            'pageDescription' => "Candidaturas y encuesta web de la {$scopeLabel} {$territory->name}.",
            'shareTitle' => "{$scopeLabel} {$territory->name} | EncuestasElectorales.pe",
            'shareDescription' => $shareDescription,
            'shareImage' => $result['state'] === RoundAvailability::Active->value
                ? route('surveys.og-image', ['scope' => $territory->scopeType, 'slug' => $territory->slug])
                : null,
            'shareType' => 'article',
            'shareUrl' => $currentUrl,
        ];
    }

    private function scopeLabel(string $scope): string
    {
        return match ($scope) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };
    }

    /**
     * @param  array<int, \App\Application\Data\SurveyRoundData>  $rounds
     * @return array<string, mixed>|null
     */
    private function resolveSelectedRound(?string $scope, ?string $slug, array $rounds): ?array
    {
        if ($scope === null || $slug === null) {
            return null;
        }

        $territory = $this->territories->findPublishedByScopeAndSlug($scope, $slug);
        if ($territory === null) {
            return null;
        }

        $round = $this->rounds->forTerritory($territory->id);

        return $round ? $this->details->make($round) : null;
    }
}
