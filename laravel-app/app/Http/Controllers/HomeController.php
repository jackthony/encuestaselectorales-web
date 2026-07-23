<?php

namespace App\Http\Controllers;

use App\Services\PublicPortalData;
use Illuminate\View\View;

final class HomeController extends Controller
{
    public function __construct(private readonly PublicPortalData $portalData)
    {
    }

    public function index(): View
    {
        $distritos = $this->portalData->districts();
        $encuestas = $this->portalData->fieldStudies();
        $rondasAbiertas = $this->portalData->activeRounds();

        $pageTitle = 'Encuestas Electorales Perú 2026 - Transparencia y Datos';
        $pageDescription = 'El pulso electoral del Perú: sondeos ciudadanos por región, provincia y distrito para las Elecciones Regionales y Municipales 2026.';

        return view('pages.home', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'distritos' => $distritos,
            'encuestas' => $encuestas,
            'rondasAbiertas' => $rondasAbiertas,
            'whatsappNumero' => '51971388435',
            'shareTitle' => $pageTitle,
            'shareDescription' => $pageDescription,
            'shareImage' => 'assets/img/share/home.png',
            'shareType' => 'website',
            'shareUrl' => url('/'),
        ]);
    }
}
