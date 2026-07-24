<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OgThumbnailController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\StaticPublicPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/index.php', [HomeController::class, 'index']);

Route::get('/metodologia.php', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'metodologia.php');

Route::get('/quienes-somos.php', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'quienes-somos.php');

Route::redirect('/encuestas/region/callao', '/encuestas/region/callao-region', 301);

Route::get('/encuestas/{scope}/{slug}', [PublicPortalController::class, 'scope'])
    ->whereIn('scope', ['region', 'province', 'district'])
    ->name('surveys.scope');

Route::get('/encuestas/{scope}/{slug}/og-image.png', [OgThumbnailController::class, 'show'])
    ->whereIn('scope', ['region', 'province', 'district'])
    ->name('surveys.og-image');

Route::get('/fuentes-correcciones.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'fuentes-correcciones.html');

Route::get('/politica-editorial.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'politica-editorial.html');

Route::get('/politica-privacidad.html', [StaticPublicPortalController::class, 'show'])
    ->defaults('page', 'politica-privacidad.html');

if (app()->environment(['local', 'testing'])) {
    Route::get('/__design/og-results-preview', function (\Illuminate\Http\Request $request) {
        $fixture = basename($request->query('fixture', 'og-results-preview'));
        $path = base_path("tests/Fixtures/{$fixture}.php");
        abort_unless(is_file($path), 404);

        return view('dev.og-results-preview', ['data' => require $path]);
    })->name('dev.og-results-preview');
}
