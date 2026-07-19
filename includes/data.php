<?php
/**
 * Reads + decodes data/*.json once per request. No DB, no /api/ (BL-13/BL-14).
 * Returned as a plain array so callers can `$x = require __DIR__ . '/data.php';`.
 */

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
