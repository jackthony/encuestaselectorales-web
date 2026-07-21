<?php
/**
 * Survey round read helpers.
 */

function getEncuestasPdo(): ?PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $pdo = require __DIR__ . '/db.php';
    } catch (Throwable $e) {
        error_log('encuestas PDO bootstrap unavailable: ' . $e->getMessage());
        return null;
    }

    return $pdo;
}

function getRondaActiva(string $distritoId): ?array
{
    static $cache = [];

    if ($distritoId === '') {
        return null;
    }

    if (array_key_exists($distritoId, $cache)) {
        return $cache[$distritoId];
    }

    $pdo = getEncuestasPdo();
    if (!$pdo instanceof PDO) {
        $cache[$distritoId] = null;
        return null;
    }

    $sql = <<<SQL
SELECT id, distrito_id, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre, estado_publicacion, created_at
FROM encuestas
WHERE distrito_id = :distrito_id
  AND estado_publicacion = 'producción'
  AND NOW() BETWEEN fecha_apertura AND fecha_cierre
ORDER BY fecha_apertura DESC, numero_ronda DESC, created_at DESC
LIMIT 1
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['distrito_id' => $distritoId]);
    $ronda = $stmt->fetch();

    $cache[$distritoId] = $ronda ?: null;

    return $cache[$distritoId];
}

function getRondasActivas(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $pdo = getEncuestasPdo();
    if (!$pdo instanceof PDO) {
        $cache = [];
        return $cache;
    }

    $sql = <<<SQL
SELECT id, distrito_id, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre, estado_publicacion, created_at
FROM encuestas
WHERE estado_publicacion = 'producción'
  AND NOW() BETWEEN fecha_apertura AND fecha_cierre
ORDER BY fecha_apertura DESC, numero_ronda DESC, created_at DESC
SQL;

    $stmt = $pdo->query($sql);
    $cache = $stmt->fetchAll();

    return $cache;
}
