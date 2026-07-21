<?php
/**
 * Operator-only CLI helper to create and publish survey rounds.
 *
 * Usage:
 *   php scripts/crear-encuesta.php crear --distrito=miraflores --titulo="..." --apertura="2026-07-21 09:00:00" --cierre="2026-08-05 23:59:59" --ronda=1
 *   php scripts/crear-encuesta.php publicar --id=<encuesta-id>
 */

require_once __DIR__ . '/../includes/encuestas.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script only runs from the CLI.\n");
    exit(1);
}

function parseArgs(array $argv): array
{
    $command = $argv[1] ?? '';
    $options = [];

    for ($i = 2; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (str_starts_with($arg, '--') && str_contains($arg, '=')) {
            [$key, $value] = explode('=', substr($arg, 2), 2);
            $options[$key] = $value;
        }
    }

    return [$command, $options];
}

function requireOption(array $options, string $key): string
{
    $value = trim((string)($options[$key] ?? ''));
    if ($value === '') {
        fwrite(STDERR, "Missing required option --{$key}.\n");
        exit(1);
    }

    return $value;
}

function parseDateTime(string $value, string $label): string
{
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
    if (!$dt || $dt->format('Y-m-d H:i:s') !== $value) {
        fwrite(STDERR, "Invalid {$label}. Use YYYY-MM-DD HH:MM:SS.\n");
        exit(1);
    }

    return $dt->format('Y-m-d H:i:s');
}

[$command, $options] = parseArgs($argv);
$pdo = getEncuestasPdo();

if ($command === 'crear') {
    $distritoId = requireOption($options, 'distrito');
    $titulo = requireOption($options, 'titulo');
    $apertura = parseDateTime(requireOption($options, 'apertura'), 'apertura');
    $cierre = parseDateTime(requireOption($options, 'cierre'), 'cierre');
    $numeroRonda = (int)($options['ronda'] ?? 1);

    if ($numeroRonda < 1 || $numeroRonda > 255) {
        fwrite(STDERR, "Invalid --ronda. Use a value between 1 and 255.\n");
        exit(1);
    }

    if (strcmp($cierre, $apertura) <= 0) {
        fwrite(STDERR, "The cierre timestamp must be after apertura.\n");
        exit(1);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO encuestas (id, distrito_id, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre, estado_publicacion)
         VALUES (:id, :distrito_id, :tipo, :numero_ronda, :titulo, :fecha_apertura, :fecha_cierre, :estado_publicacion)'
    );
    $id = bin2hex(random_bytes(16));
    $stmt->execute([
        'id' => $id,
        'distrito_id' => $distritoId,
        'tipo' => 'online_propia',
        'numero_ronda' => $numeroRonda,
        'titulo' => $titulo,
        'fecha_apertura' => $apertura,
        'fecha_cierre' => $cierre,
        'estado_publicacion' => 'prueba',
    ]);

    fwrite(STDOUT, "Created encuesta {$id} in prueba state.\n");
    exit(0);
}

if ($command === 'publicar') {
    $id = requireOption($options, 'id');

    $stmt = $pdo->prepare(
        "UPDATE encuestas SET estado_publicacion = 'producción' WHERE id = :id AND tipo = 'online_propia'"
    );
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        fwrite(STDERR, "No encuesta found for id {$id}.\n");
        exit(1);
    }

    fwrite(STDOUT, "Published encuesta {$id}.\n");
    exit(0);
}

fwrite(STDERR, "Usage:\n");
fwrite(STDERR, "  php scripts/crear-encuesta.php crear --distrito=... --titulo=... --apertura=\"YYYY-MM-DD HH:MM:SS\" --cierre=\"YYYY-MM-DD HH:MM:SS\" --ronda=1\n");
fwrite(STDERR, "  php scripts/crear-encuesta.php publicar --id=...\n");
exit(1);
