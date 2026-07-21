<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/vote-security.php';
require_once __DIR__ . '/../includes/trusted-ip.php';

header('Content-Type: application/json; charset=utf-8');

function voteRespond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    voteRespond(405, ['status' => 'error', 'message' => 'Method not allowed.']);
}

$encuestaId = voteNormalizeText((string)($_GET['encuesta_id'] ?? ''), 32);
if ($encuestaId === '' || !preg_match('/\A[a-f0-9]{32}\z/i', $encuestaId)) {
    voteRespond(400, ['status' => 'error', 'message' => 'Missing or invalid encuesta_id.']);
}

$pdo = require __DIR__ . '/../includes/db.php';
$client = resolveTrustedClientIp();
$ipHash = voteHashIp($client['ip']);
$windowSeconds = voteRateLimitWindowSeconds();
$threshold = voteRateLimitThreshold();

$rateLimitSql = 'SELECT COUNT(*) FROM votos_interactivos
    WHERE encuesta_id = :encuesta_id
      AND ip_hash = :ip_hash
      AND created_at >= DATE_SUB(NOW(), INTERVAL ' . (int) $windowSeconds . ' SECOND)';
$rateLimitStmt = $pdo->prepare($rateLimitSql);
$rateLimitStmt->execute([
    'encuesta_id' => $encuestaId,
    'ip_hash' => $ipHash,
]);

if ((int) $rateLimitStmt->fetchColumn() >= $threshold) {
    voteRespond(429, ['status' => 'error', 'message' => 'Rate limit exceeded.']);
}

$rawInput = file_get_contents('php://input');
if ($rawInput === false) {
    voteRespond(400, ['status' => 'error', 'message' => 'Invalid request body.']);
}

try {
    $data = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    voteRespond(400, ['status' => 'error', 'message' => 'Malformed JSON payload.']);
}

if (!is_array($data)) {
    voteRespond(400, ['status' => 'error', 'message' => 'Malformed JSON payload.']);
}

$encuestaStmt = $pdo->prepare(
    "SELECT id, distrito_id, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre
     FROM encuestas
     WHERE id = :id
       AND estado_publicacion = 'producción'
       AND NOW() BETWEEN fecha_apertura AND fecha_cierre
     LIMIT 1"
);
$encuestaStmt->execute(['id' => $encuestaId]);
$encuesta = $encuestaStmt->fetch();

if (!$encuesta) {
    voteRespond(400, ['status' => 'error', 'message' => 'Active encuesta not found.']);
}

$ubigeoVotacion = voteNormalizeText((string)($data['ubigeo_votacion'] ?? ''), 64);
if ($ubigeoVotacion === '' || !findDistritoById($ubigeoVotacion)) {
    voteRespond(400, ['status' => 'error', 'message' => 'Unknown district code.']);
}

if ($ubigeoVotacion !== $encuesta['distrito_id']) {
    voteRespond(400, ['status' => 'error', 'message' => 'District does not match this encuesta.']);
}

$tipoVoto = voteNormalizeText((string)($data['tipo_voto'] ?? 'candidato'), 16);
$tiposValidos = ['candidato', 'blanco', 'viciado'];
if (!in_array($tipoVoto, $tiposValidos, true)) {
    voteRespond(400, ['status' => 'error', 'message' => 'Invalid tipo_voto.']);
}

$gpsLat = filter_var($data['gps_lat'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
$gpsLng = filter_var($data['gps_lng'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
if ($gpsLat === null || $gpsLat === false || $gpsLng === null || $gpsLng === false) {
    voteRespond(400, ['status' => 'error', 'message' => 'GPS coordinates are required.']);
}
if ($gpsLat < -90 || $gpsLat > 90 || $gpsLng < -180 || $gpsLng > 180) {
    voteRespond(400, ['status' => 'error', 'message' => 'GPS coordinates are out of range.']);
}

$gpsAccuracy = null;
if (array_key_exists('gps_accuracy_meters', $data) && $data['gps_accuracy_meters'] !== null && $data['gps_accuracy_meters'] !== '') {
    $gpsAccuracy = filter_var($data['gps_accuracy_meters'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    if ($gpsAccuracy === null || $gpsAccuracy === false || $gpsAccuracy < 0) {
        voteRespond(400, ['status' => 'error', 'message' => 'Invalid GPS accuracy.']);
    }
}

$districtMatch = voteDistrictApproximateMatch($ubigeoVotacion, (float) $gpsLat, (float) $gpsLng);

$interactionTimeMs = filter_var($data['interaction_time_ms'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
if ($interactionTimeMs === null || $interactionTimeMs === false || $interactionTimeMs <= 0) {
    voteRespond(400, ['status' => 'error', 'message' => 'Invalid interaction time.']);
}
if ($interactionTimeMs < 200) {
    voteRespond(400, ['status' => 'error', 'message' => 'Interaction time too short.']);
}

$candidatoId = null;
if ($tipoVoto === 'candidato') {
    $validatedCandidateId = filter_var($data['candidato_id'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    if ($validatedCandidateId === null || $validatedCandidateId === false || $validatedCandidateId <= 0) {
        voteRespond(400, ['status' => 'error', 'message' => 'candidato_id is required for candidato votes.']);
    }
    $candidatoId = (string) $validatedCandidateId;
} elseif (array_key_exists('candidato_id', $data) && trim((string) $data['candidato_id']) !== '') {
    voteRespond(400, ['status' => 'error', 'message' => 'candidato_id is only allowed for candidato votes.']);
}

$userAgent = voteNormalizeText((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 512);
$cfPais = voteNormalizeText((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? ''), 2);
$cfPais = $cfPais !== '' ? $cfPais : null;

$browserFingerprint = voteFingerprint(
    (string)($data['browser_fingerprint'] ?? ''),
    $userAgent,
    $client['ip']
);

$deviceToken = voteNormalizeText((string)($_COOKIE['device_token'] ?? ''), 64);
if ($deviceToken === '' || !preg_match('/\A[a-f0-9]{64}\z/i', $deviceToken)) {
    $deviceToken = voteDefaultDeviceToken();
}

$duplicateStmt = $pdo->prepare(
    'SELECT id
     FROM votos_interactivos
     WHERE encuesta_id = :encuesta_id
       AND (ip_hash = :ip_hash OR device_token = :device_token)
     ORDER BY created_at DESC
     LIMIT 1'
);
$duplicateStmt->execute([
    'encuesta_id' => $encuestaId,
    'ip_hash' => $ipHash,
    'device_token' => $deviceToken,
]);

if ($duplicateStmt->fetchColumn() !== false) {
    voteRespond(409, [
        'status' => 'error',
        'message' => 'Ya registramos un voto para esta encuesta desde esta conexión o dispositivo.',
    ]);
}

$recentBurstStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM votos_interactivos
     WHERE encuesta_id = :encuesta_id
       AND ip_hash = :ip_hash
       AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
);
$recentBurstStmt->execute([
    'encuesta_id' => $encuestaId,
    'ip_hash' => $ipHash,
]);
$recentBurst = (int) $recentBurstStmt->fetchColumn() >= 3;

$trustScore = voteScore([
    'district_match' => $districtMatch,
    'cf_country' => $cfPais,
    'gps_accuracy_meters' => $gpsAccuracy,
    'interaction_time_ms' => $interactionTimeMs,
    'device_cookie_present' => isset($_COOKIE['device_token']) && trim((string) $_COOKIE['device_token']) !== '',
    'recent_burst' => $recentBurst,
]);

$estado = $trustScore < 40 ? 'sospechoso' : 'valido';
$ipEncrypted = voteEncryptIp($client['ip']);
$voteId = bin2hex(random_bytes(16));

$insertStmt = $pdo->prepare(
    'INSERT INTO votos_interactivos (
        id,
        encuesta_id,
        ubigeo_votacion,
        candidato_id,
        tipo_voto,
        gps_lat,
        gps_lng,
        gps_accuracy_meters,
        interaction_time_ms,
        ip_hash,
        ip_cifrada,
        ip_iv,
        ip_tag,
        device_token,
        browser_fingerprint,
        user_agent,
        cf_pais,
        trust_score,
        estado
    ) VALUES (
        :id,
        :encuesta_id,
        :ubigeo_votacion,
        :candidato_id,
        :tipo_voto,
        :gps_lat,
        :gps_lng,
        :gps_accuracy_meters,
        :interaction_time_ms,
        :ip_hash,
        :ip_cifrada,
        :ip_iv,
        :ip_tag,
        :device_token,
        :browser_fingerprint,
        :user_agent,
        :cf_pais,
        :trust_score,
        :estado
    )'
);

try {
    $insertStmt->execute([
        'id' => $voteId,
        'encuesta_id' => $encuestaId,
        'ubigeo_votacion' => $ubigeoVotacion,
        'candidato_id' => $candidatoId,
        'tipo_voto' => $tipoVoto,
        'gps_lat' => $gpsLat,
        'gps_lng' => $gpsLng,
        'gps_accuracy_meters' => $gpsAccuracy,
        'interaction_time_ms' => $interactionTimeMs,
        'ip_hash' => $ipHash,
        'ip_cifrada' => $ipEncrypted['ciphertext'],
        'ip_iv' => $ipEncrypted['iv'],
        'ip_tag' => $ipEncrypted['tag'],
        'device_token' => $deviceToken,
        'browser_fingerprint' => $browserFingerprint,
        'user_agent' => $userAgent,
        'cf_pais' => $cfPais,
        'trust_score' => $trustScore,
        'estado' => $estado,
    ]);
} catch (PDOException $e) {
    $sqlState = (string) $e->getCode();
    if (str_starts_with($sqlState, '23')) {
        voteRespond(429, ['status' => 'error', 'message' => 'Vote already recorded or rate limited.']);
    }

    error_log('BL-14 votar.php failed: ' . $e->getMessage());
    voteRespond(500, ['status' => 'error', 'message' => 'Unable to record vote.']);
}

$cookieOptions = [
    'expires' => time() + 60 * 60 * 24 * 30,
    'path' => '/',
    'secure' => !voteLocalDevEnabled(),
    'httponly' => true,
    'samesite' => 'Strict',
];
setcookie('device_token', $deviceToken, $cookieOptions);

voteRespond(200, [
    'status' => 'success',
    'message' => 'Voto registrado y encriptado correctamente.',
]);
