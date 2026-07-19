<?php
/**
 * Reads + decodes data/*.json once per request. No DB, no /api/ (BL-13/BL-14).
 * Returned as a plain array so callers can `$x = require __DIR__ . '/data.php';`.
 */

/**
 * Single switch gating the interactive vote widget (bl-11-responsive-wcag
 * design.md, "Priority 0"). Stays false until /api/votar.php, rate limiting
 * and GPS validation are deployed and verified (BL-14) — a form with nowhere
 * safe to submit is exactly the fake functionality this flag exists to
 * prevent. Guarded because this file is `require`d (not `require_once`)
 * from several call sites per request.
 */
if (!defined('VOTACION_EN_VIVO')) {
    define('VOTACION_EN_VIVO', false);
}

$dataDir = __DIR__ . '/../data';

$readJson = static function (string $file) use ($dataDir): array {
    $path = $dataDir . '/' . $file;
    if (!is_file($path)) {
        return [];
    }
    $decoded = json_decode(file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
};

return [
    'distritos'      => $readJson('distrito.json'),
    'candidatos'     => $readJson('candidato.json'),
    'partidos'       => $readJson('partido.json'),
    'encuestas'      => $readJson('encuesta.json'),
    'encuestadoras'  => $readJson('encuestadora.json'),
    'resultados'     => $readJson('resultado.json'),
];
