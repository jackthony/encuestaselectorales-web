<?php

namespace App\Http\Controllers;

use App\Services\SupplementaryPublicPortalData;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SupplementaryPublicPortalController extends Controller
{
    public function __construct(private readonly SupplementaryPublicPortalData $portalData)
    {
    }

    public function sondeos(): View
    {
        return view('pages.sondeos', $this->portalData->sondeosPageData());
    }

    public function encuesta(Request $request): View
    {
        return view('pages.encuesta', $this->portalData->surveyPageData((string) $request->query('id', '')));
    }

    public function candidato(Request $request): View
    {
        return view('pages.candidato', $this->portalData->candidatePageData((int) $request->query('id', 0)));
    }

    public function encuestadoras(): View
    {
        return view('pages.encuestadoras', $this->portalData->encuestadorasPageData());
    }
}
