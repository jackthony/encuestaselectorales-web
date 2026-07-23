<?php

namespace App\Http\Controllers;

use App\Application\Data\SurveyRoundData;
use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Infrastructure\Persistence\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SupplementaryPublicPortalController extends Controller
{
    public function __construct(private readonly SurveyRoundQuery $rounds) {}

    public function sondeos(): View
    {
        return view('pages.sondeos', [
            'activeRounds' => array_map(
                fn (SurveyRoundData $round): array => $this->roundCard($round),
                $this->rounds->activeNational(),
            ),
            'fieldStudies' => [],
            'pageTitle' => 'Sondeos activos | EncuestasElectorales.pe',
            'pageDescription' => 'Sondeos ciudadanos en vivo y estudios de campo publicados.',
            'shareUrl' => url('/sondeos.php'),
        ]);
    }

    public function encuesta(): View
    {
        return view('pages.encuesta', [
            'survey' => null,
            'pollster' => null,
            'result' => null,
            'rows' => [],
            'pageTitle' => 'Estudio no disponible | EncuestasElectorales.pe',
            'pageDescription' => 'Estudio de campo no disponible.',
        ]);
    }

    public function candidato(Request $request): View
    {
        $candidate = Candidate::query()
            ->with('candidacies.politicalParty', 'candidacies.territory')
            ->find((string) $request->query('id', ''));
        $candidacy = $candidate?->candidacies->first();

        return view('pages.candidato', [
            'candidate' => $candidate ? [
                'id' => (string) $candidate->getKey(),
                'nombre' => $candidate->full_name,
                'foto' => $candidate->photo_url ?: asset('assets/img/default-face.svg'),
                'activo' => $candidate->status === 'active',
            ] : null,
            'district' => $candidacy ? [
                'id' => (string) $candidacy->territory->getKey(),
                'nombre' => $candidacy->territory->name,
                'scope_type' => $candidacy->territory->scope_type->value,
                'slug' => $candidacy->territory->slug,
            ] : null,
            'party' => $candidacy ? [
                'id' => (string) $candidacy->politicalParty->getKey(),
                'nombre' => $candidacy->politicalParty->name,
                'siglas' => $candidacy->politicalParty->acronym,
                'logo' => $candidacy->politicalParty->logo_url,
            ] : null,
            'partyColor' => '#123377',
            'relatedCandidates' => [],
            'activeRound' => null,
            'history' => [],
            'pageTitle' => $candidate
                ? "Perfil de {$candidate->full_name} | EncuestasElectorales.pe"
                : 'Candidato no encontrado | EncuestasElectorales.pe',
            'pageDescription' => 'Perfil de candidatura electoral.',
        ]);
    }

    public function encuestadoras(): View
    {
        return view('pages.encuestadoras', [
            'pollsters' => [],
            'pageTitle' => 'Directorio de encuestadoras | EncuestasElectorales.pe',
            'pageDescription' => 'Directorio de encuestadoras y estudios verificados.',
        ]);
    }

    /** @return array<string, mixed> */
    private function roundCard(SurveyRoundData $round): array
    {
        $label = match ($round->territory->scopeType) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };

        return [
            'id' => $round->id,
            'titulo' => $round->title,
            'fecha_apertura' => $round->opensAt->format('d/m/Y'),
            'fecha_cierre' => $round->closesAt->format('d/m/Y'),
            'readiness_state' => $round->readinessState,
            'blocked_reason' => $round->blockedReason,
            'option_count' => count($round->options),
            'scope_label' => "{$label} {$round->territory->name}",
            'target_url' => route('surveys.scope', [
                'scope' => $round->territory->scopeType,
                'slug' => $round->territory->slug,
            ]),
        ];
    }
}
