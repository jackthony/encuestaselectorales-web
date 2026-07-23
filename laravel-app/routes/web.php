<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\SupplementaryPublicPortalController;
use App\Http\Controllers\StaticPublicPortalController;
use App\Http\Controllers\LegacyBridgeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::get('/index.php', [HomeController::class, 'index']);

Route::get('/sondeos.php', [SupplementaryPublicPortalController::class, 'sondeos']);

Route::get('/distrito.php', [PublicPortalController::class, 'distrito']);

Route::get('/encuesta.php', [SupplementaryPublicPortalController::class, 'encuesta']);

Route::get('/candidato.php', [SupplementaryPublicPortalController::class, 'candidato']);

Route::get('/encuestadoras.php', [SupplementaryPublicPortalController::class, 'encuestadoras']);

Route::get('/metodologia.php', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'metodologia.php');

Route::get('/quienes-somos.php', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'quienes-somos.php');

Route::get('/territorio.php', [PublicPortalController::class, 'territorio']);

Route::get('/fuentes-correcciones.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'fuentes-correcciones.html');

Route::get('/politica-editorial.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'politica-editorial.html');

Route::get('/politica-privacidad.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'politica-privacidad.html');

Route::get('/assets/{path}', [LegacyBridgeController::class, 'asset'])
    ->where('path', '.*');
