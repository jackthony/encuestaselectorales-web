<?php
/**
 * Shared view helpers. camelCase functions per docs/engineering-standards.md §4.
 * No DB, no /api/ — BL-10 is a pure structural refactor (see proposal.md).
 */

/** HTML-escapes a value for safe output. */
function esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Formats a numeric percentage the way the canvas prototypes render it: "24.5%". */
function pct($value, int $decimals = 1): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float) $value, $decimals) . '%';
}

/**
 * Resolves a party's brand color from data/partido.json — never a hardcoded
 * hex literal (php-architecture spec, "Party colors come from data, not literals").
 *
 * Accepts either a party id (int), or siglas/nombre (string, case-insensitive).
 * Returns null (not an invented color) when the party is not in the catalog —
 * callers must handle that by logging the gap, per tasks.md rule 4.
 */
function partyColor($partyIdOrSiglas): ?string
{
    static $partidos = null;
    if ($partidos === null) {
        $partidos = require __DIR__ . '/data.php';
        $partidos = $partidos['partidos'];
    }

    foreach ($partidos as $p) {
        if (is_int($partyIdOrSiglas) || is_numeric($partyIdOrSiglas)) {
            if ((int) $p['id'] === (int) $partyIdOrSiglas) {
                return $p['color'];
            }
            continue;
        }
        if (strcasecmp($p['siglas'], (string) $partyIdOrSiglas) === 0
            || strcasecmp($p['nombre'], (string) $partyIdOrSiglas) === 0) {
            return $p['color'];
        }
    }

    return null;
}

/**
 * Same as partyColor(), but falls back to a neutral gray (never an
 * invented party color) and logs the gap instead of returning null —
 * convenience for view code that just needs a swatch color (tasks.md rule 4).
 */
function partyColorOrGray($partyIdOrSiglas): string
{
    $color = partyColor($partyIdOrSiglas);
    if ($color === null) {
        error_log("BL-10 finding: party \"$partyIdOrSiglas\" not found in data/partido.json.");
        return '#6b7280';
    }
    return $color;
}

/** Converts a "#RRGGBB" hex color to "r, g, b" for use inside rgba(...). */
function hexToRgbTriplet(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return '107, 114, 128'; // neutral gray fallback, not a party color
    }
    return implode(', ', [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ]);
}

/** Looks up a party record by id. Returns null if not found (never invented). */
function findPartido(int $id): ?array
{
    static $partidos = null;
    if ($partidos === null) {
        $partidos = require __DIR__ . '/data.php';
        $partidos = $partidos['partidos'];
    }
    foreach ($partidos as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

/** Looks up a candidate record by id. Returns null if not found. */
function findCandidato(int $id): ?array
{
    static $candidatos = null;
    if ($candidatos === null) {
        $candidatos = require __DIR__ . '/data.php';
        $candidatos = $candidatos['candidatos'];
    }
    foreach ($candidatos as $c) {
        if ((int) $c['id'] === $id) {
            return $c;
        }
    }
    return null;
}

/** Looks up the "ejemplo" pollster record — the only attributable source for demo figures. */
function encuestadoraEjemplo(): ?array
{
    static $encuestadoras = null;
    if ($encuestadoras === null) {
        $encuestadoras = require __DIR__ . '/data.php';
        $encuestadoras = $encuestadoras['encuestadoras'];
    }
    foreach ($encuestadoras as $e) {
        if ($e['id'] === 'ejemplo') {
            return $e;
        }
    }
    return null;
}
