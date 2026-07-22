<?php

use App\Http\Controllers\LegacyBridgeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'index.php');

Route::get('/index.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'index.php');

Route::get('/sondeos.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'sondeos.php');

Route::get('/distrito.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'distrito.php');

Route::get('/encuesta.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'encuesta.php');

Route::get('/candidato.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'candidato.php');

Route::get('/encuestadoras.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'encuestadoras.php');

Route::get('/metodologia.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'metodologia.php');

Route::get('/quienes-somos.php', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'quienes-somos.php');

Route::get('/fuentes-correcciones.html', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'fuentes-correcciones.html');

Route::get('/politica-editorial.html', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'politica-editorial.html');

Route::get('/politica-privacidad.html', [LegacyBridgeController::class, 'page'])
    ->defaults('page', 'politica-privacidad.html');

Route::get('/assets/{path}', [LegacyBridgeController::class, 'asset'])
    ->where('path', '.*');
