<?php

namespace App\Http\Controllers;

use App\Application\Data\SurveyRoundData;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(private readonly SurveyRoundQuery $rounds) {}

    public function index(): View
    {
        $pageTitle = 'Encuestas Electorales Perú 2026 - Transparencia y Datos';
        $pageDescription = 'Sondeos ciudadanos por región, provincia y distrito para las Elecciones Regionales y Municipales 2026.';

        return view('pages.home', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'distritos' => [],
            'encuestas' => [],
            'rondasAbiertas' => array_map(
                fn (SurveyRoundData $round): array => $this->roundCard($round),
                $this->rounds->activeNational(),
            ),
            'whatsappNumero' => '51971388435',
            'shareTitle' => $pageTitle,
            'shareDescription' => $pageDescription,
            'shareImage' => 'assets/img/share/home.png',
            'shareType' => 'website',
            'shareUrl' => url('/'),
        ]);
    }

    /** @return array<string, mixed> */
    private function roundCard(SurveyRoundData $round): array
    {
        $scopeLabel = match ($round->territory->scopeType) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };

        return [
            'id' => $round->id,
            'titulo' => $round->title,
            'fecha_apertura' => $round->opensAt->format('d/m/Y'),
            'fecha_cierre' => $round->closesAt->format('d/m/Y'),
            'scope_label' => "{$scopeLabel} {$round->territory->name}",
            'office_type' => $round->officeType,
            'readiness_state' => $round->readinessState,
            'blocked_reason' => $round->blockedReason,
            'option_count' => count($round->options),
            'target_url' => route('surveys.scope', [
                'scope' => $round->territory->scopeType,
                'slug' => $round->territory->slug,
            ]),
        ];
    }
}
