<?php

namespace App\Http\Controllers;

use App\Services\PublicPortalData;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicPortalController extends Controller
{
    public function __construct(private readonly PublicPortalData $portalData)
    {
    }

    public function distrito(Request $request): View
    {
        $pageData = $this->portalData->districtPageData((string) $request->query('slug', ''));
        $pageData['shareUrl'] = $request->fullUrl();

        return view('pages.distrito', $pageData);
    }

    public function territorio(Request $request): View
    {
        $pageData = $this->portalData->territoryPageData(
            (string) $request->query('nivel', ''),
            (string) $request->query('slug', '')
        );
        $pageData['shareUrl'] = $request->fullUrl();

        return view('pages.territorio', $pageData);
    }

    public function sondeos(): View
    {
        $pageData = $this->portalData->sondeosPageData();
        $pageData['shareUrl'] = url('/sondeos.php');

        return view('pages.sondeos', $pageData);
    }

    public function candidato(Request $request): View
    {
        $pageData = $this->portalData->candidatePageData((int) $request->query('id', 0));
        $pageData['shareUrl'] = $request->fullUrl();

        return view('pages.candidato', $pageData);
    }

    public function encuesta(Request $request): View
    {
        $pageData = $this->portalData->surveyPageData((string) $request->query('id', ''));
        $pageData['shareUrl'] = $request->fullUrl();

        return view('pages.encuesta', $pageData);
    }

    public function encuestadoras(): View
    {
        $pageData = $this->portalData->pollstersPageData();
        $pageData['shareUrl'] = url('/encuestadoras.php');

        return view('pages.encuestadoras', $pageData);
    }
}
