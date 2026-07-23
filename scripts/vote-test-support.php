<?php

function voteTestAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function voteTestTempDir(): string
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'codex-vote-' . bin2hex(random_bytes(4));
    if (!mkdir($dir) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create temp dir ' . $dir);
    }
    return $dir;
}

function voteTestBootstrapSecurity(array $overrides = []): array
{
    $dir = voteTestTempDir();
    $keyPath = $dir . DIRECTORY_SEPARATOR . 'ip.key';
    file_put_contents($keyPath, random_bytes(32));

    $config = array_merge([
        'local_dev' => false,
        'rate_limit_threshold' => 5,
        'rate_limit_window_seconds' => 3600,
        'ip_salt' => bin2hex(random_bytes(32)),
        'aes_key_path' => $keyPath,
        'trust_score' => [],
    ], $overrides);

    $configPath = $dir . DIRECTORY_SEPARATOR . 'security.php';
    file_put_contents($configPath, '<?php return ' . var_export($config, true) . ';' . PHP_EOL);

    putenv('VOTE_SECURITY_CONFIG=' . $configPath);
    putenv('CODEX_SECURITY_CONFIG=' . $configPath);

    return [
        'dir' => $dir,
        'config_path' => $configPath,
        'key_path' => $keyPath,
    ];
}

function voteTestCleanup(array $bootstrap): void
{
    foreach (['config_path', 'key_path'] as $fileKey) {
        if (!empty($bootstrap[$fileKey]) && is_file($bootstrap[$fileKey])) {
            @unlink($bootstrap[$fileKey]);
        }
    }
    if (!empty($bootstrap['dir']) && is_dir($bootstrap['dir'])) {
        @rmdir($bootstrap['dir']);
    }
}

function voteTestPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec(<<<SQL
CREATE TABLE encuestas (
    id TEXT PRIMARY KEY,
    distrito_id TEXT NOT NULL,
    nivel TEXT NOT NULL DEFAULT 'distrito',
    tipo TEXT NOT NULL,
    numero_ronda INTEGER NOT NULL DEFAULT 1,
    titulo TEXT NOT NULL,
    fecha_apertura TEXT NOT NULL,
    fecha_cierre TEXT NOT NULL,
    estado_publicacion TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);
    $pdo->exec(<<<SQL
CREATE TABLE votos_interactivos (
    id TEXT PRIMARY KEY,
    encuesta_id TEXT NOT NULL,
    ubigeo_votacion TEXT NOT NULL,
    candidato_id TEXT NULL,
    tipo_voto TEXT NOT NULL,
    gps_lat REAL NOT NULL,
    gps_lng REAL NOT NULL,
    gps_accuracy_meters INTEGER NULL,
    interaction_time_ms INTEGER NULL,
    ip_hash TEXT NOT NULL,
    ip_cifrada BLOB NOT NULL,
    ip_iv BLOB NOT NULL,
    ip_tag BLOB NOT NULL,
    device_token TEXT NOT NULL,
    browser_fingerprint TEXT NOT NULL,
    user_agent TEXT NULL,
    cf_pais TEXT NULL,
    trust_score INTEGER NULL,
    estado TEXT NOT NULL DEFAULT 'valido',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);
    $pdo->exec('CREATE INDEX idx_ratelimit_ip ON votos_interactivos (encuesta_id, ip_hash, created_at)');
    $pdo->exec('CREATE INDEX idx_ratelimit_device ON votos_interactivos (encuesta_id, device_token, created_at)');
    return $pdo;
}

function voteTestSeedEncuesta(PDO $pdo, array $overrides = []): array
{
    $encuesta = array_merge([
        'id' => bin2hex(random_bytes(16)),
        'distrito_id' => 'callao',
        'nivel' => 'region',
        'tipo' => 'online_propia',
        'numero_ronda' => 1,
        'titulo' => 'Encuesta de prueba',
        'fecha_apertura' => '2026-07-22 00:00:00',
        'fecha_cierre' => '2026-07-23 23:59:59',
        'estado_publicacion' => 'producción',
    ], $overrides);

    $stmt = $pdo->prepare(
        'INSERT INTO encuestas (id, distrito_id, nivel, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre, estado_publicacion)
         VALUES (:id, :distrito_id, :nivel, :tipo, :numero_ronda, :titulo, :fecha_apertura, :fecha_cierre, :estado_publicacion)'
    );
    $stmt->execute($encuesta);

    return $encuesta;
}

function voteTestPayload(array $overrides = []): array
{
    return array_merge([
        'ubigeo_votacion' => 'callao',
        'tipo_voto' => 'candidato',
        'candidato_id' => 1,
        'gps_lat' => -12.05,
        'gps_lng' => -77.08,
        'gps_accuracy_meters' => 20,
        'interaction_time_ms' => 1200,
        'browser_fingerprint' => bin2hex(random_bytes(16)),
    ], $overrides);
}

function voteTestRequest(PDO $pdo, array $server, array $get, array $cookie, array $payload): array
{
    require_once __DIR__ . '/../includes/helpers.php';
    require_once __DIR__ . '/../includes/vote-security.php';
    require_once __DIR__ . '/../includes/trusted-ip.php';
    require_once __DIR__ . '/../includes/vote-handler.php';

    return voteHandleRequest($pdo, $server, $get, $cookie, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

