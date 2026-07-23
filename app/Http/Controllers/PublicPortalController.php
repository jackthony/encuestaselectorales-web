<?php

namespace App\Http\Controllers;

use App\Domain\Survey\Contracts\SurveyRoundQuery;
use App\Infrastructure\Persistence\Models\Territory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicPortalController extends Controller
{
    public function __construct(private readonly SurveyRoundQuery $rounds) {}

    public function scope(string $scope, string $slug): View
    {
        abort_unless(in_array($scope, ['region', 'province', 'district'], true), 404);

        $territory = Territory::query()
            ->where('scope_type', $scope)
            ->where('slug', $slug)
            ->where('publication_state', 'published')
            ->firstOrFail();
        $result = $this->rounds->forTerritory((string) $territory->getKey());
        $payload = $result->toArray();
        $scopeLabel = match ($scope) {
            'region' => 'Región',
            'province' => 'Provincia',
            default => 'Distrito',
        };

        return view('pages.scope', [
            'territory' => $payload['territory'],
            'roundState' => $payload['state'],
            'blockedReason' => $payload['reason'],
            'activeRound' => $payload['round'],
            'scopeLabel' => $scopeLabel,
            'pageTitle' => "{$scopeLabel} {$territory->name} | EncuestasElectorales.pe",
            'pageDescription' => "Candidaturas y encuesta web de la {$scopeLabel} {$territory->name}.",
            'shareTitle' => "{$scopeLabel} {$territory->name} | EncuestasElectorales.pe",
            'shareDescription' => "Encuesta electoral de la {$scopeLabel} {$territory->name}.",
            'shareImage' => 'assets/img/share/default-share.png',
            'shareType' => 'article',
            'shareUrl' => request()->fullUrl(),
        ]);
    }

    public function distrito(Request $request): RedirectResponse
    {
        return $this->legacyRedirect('district', (string) $request->query('slug', ''));
    }

    public function territorio(Request $request): RedirectResponse
    {
        $scope = match ((string) $request->query('nivel', '')) {
            'region' => 'region',
            'provincia', 'province' => 'province',
            default => 'district',
        };

        return $this->legacyRedirect($scope, (string) $request->query('slug', ''));
    }

    private function legacyRedirect(string $scope, string $slug): RedirectResponse
    {
        $territory = Territory::query()
            ->where('scope_type', $scope)
            ->where(function ($query) use ($slug): void {
                $query->where('slug', $slug)
                    ->orWhere('canonical_name', str_replace('-', ' ', strtolower($slug)));
            })
            ->first();

        return $territory
            ? redirect()->route('surveys.scope', ['scope' => $scope, 'slug' => $territory->slug], 301)
            : redirect()->route('home', status: 302);
    }
}
