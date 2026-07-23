<?php
/**
 * Pure request handler for BL-14.
 *
 * Returns a structured response so CLI tests can exercise the full endpoint
 * logic without needing a web server.
 */

function voteBuildResponse(int $status, array $body, array $headers = [], array $cookies = []): array
{
    return [
        'status' => $status,
        'headers' => $headers,
        'cookies' => $cookies,
        'body' => $body,
    ];
}

function voteRateLimitExceeded(PDO $pdo, string $encuestaId, string $ipHash): bool
{
    $windowSeconds = voteRateLimitWindowSeconds();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $cutoffExpr = $driver === 'sqlite'
        ? "datetime('now', '-{$windowSeconds} seconds')"
        : 'DATE_SUB(NOW(), INTERVAL ' . (int) $windowSeconds . ' SECOND)';

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM votos_interactivos
         WHERE encuesta_id = :encuesta_id
           AND ip_hash = :ip_hash
           AND created_at >= ' . $cutoffExpr
    );
    $stmt->execute([
        'encuesta_id' => $encuestaId,
        'ip_hash' => $ipHash,
    ]);

    return (int) $stmt->fetchColumn() >= voteRateLimitThreshold();
}

function voteRecentBurstDetected(PDO $pdo, string $encuestaId, string $ipHash): bool
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $cutoffExpr = $driver === 'sqlite'
        ? "datetime('now', '-15 minutes')"
        : 'DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM votos_interactivos
         WHERE encuesta_id = :encuesta_id
           AND ip_hash = :ip_hash
           AND created_at >= ' . $cutoffExpr
    );
    $stmt->execute([
        'encuesta_id' => $encuestaId,
        'ip_hash' => $ipHash,
    ]);

    return (int) $stmt->fetchColumn() >= 3;
}

function voteHandleRequest(PDO $pdo, array $server, array $get, array $cookie, string $rawInput): array
{
    if (($server['REQUEST_METHOD'] ?? '') !== 'POST') {
        return voteBuildResponse(
            405,
            ['status' => 'error', 'message' => 'Method not allowed.'],
            ['Content-Type' => 'application/json; charset=utf-8', 'Allow' => 'POST']
        );
    }

    $encuestaId = voteNormalizeText((string) ($get['encuesta_id'] ?? ''), 32);
    if ($encuestaId === '' || !preg_match('/\A[a-f0-9]{32}\z/i', $encuestaId)) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Missing or invalid encuesta_id.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $client = resolveTrustedClientIpFromServer($server);
    $ipHash = voteHashIp($client['ip']);

    if (voteRateLimitExceeded($pdo, $encuestaId, $ipHash)) {
        return voteBuildResponse(429, ['status' => 'error', 'message' => 'Rate limit exceeded.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    if ($rawInput === '') {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Invalid request body.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    try {
        $data = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Malformed JSON payload.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    if (!is_array($data)) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Malformed JSON payload.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $encuestaStmt = $pdo->prepare(
        "SELECT id, distrito_id, tipo, numero_ronda, titulo, fecha_apertura, fecha_cierre
         FROM encuestas
         WHERE id = :id
           AND estado_publicacion = 'producción'
           AND :now BETWEEN fecha_apertura AND fecha_cierre
         LIMIT 1"
    );
    $encuestaStmt->execute([
        'id' => $encuestaId,
        'now' => $now,
    ]);
    $encuesta = $encuestaStmt->fetch();

    if (!$encuesta) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Active encuesta not found.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $ubigeoVotacion = voteNormalizeText((string)($data['ubigeo_votacion'] ?? ''), 64);
    if ($ubigeoVotacion === '' || !findDistritoById($ubigeoVotacion)) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Unknown district code.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    if ($ubigeoVotacion !== $encuesta['distrito_id']) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'District does not match this encuesta.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $tipoVoto = voteNormalizeText((string)($data['tipo_voto'] ?? 'candidato'), 16);
    $tiposValidos = ['candidato', 'blanco', 'viciado'];
    if (!in_array($tipoVoto, $tiposValidos, true)) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Invalid tipo_voto.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $gpsLat = filter_var($data['gps_lat'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $gpsLng = filter_var($data['gps_lng'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    if ($gpsLat === null || $gpsLat === false || $gpsLng === null || $gpsLng === false) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'GPS coordinates are required.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }
    if ($gpsLat < -90 || $gpsLat > 90 || $gpsLng < -180 || $gpsLng > 180) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'GPS coordinates are out of range.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $gpsAccuracy = null;
    if (array_key_exists('gps_accuracy_meters', $data) && $data['gps_accuracy_meters'] !== null && $data['gps_accuracy_meters'] !== '') {
        $gpsAccuracy = filter_var($data['gps_accuracy_meters'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if ($gpsAccuracy === null || $gpsAccuracy === false || $gpsAccuracy < 0) {
            return voteBuildResponse(400, ['status' => 'error', 'message' => 'Invalid GPS accuracy.'], ['Content-Type' => 'application/json; charset=utf-8']);
        }
    }

    $districtMatch = voteDistrictApproximateMatch($ubigeoVotacion, (float) $gpsLat, (float) $gpsLng);

    $interactionTimeMs = filter_var($data['interaction_time_ms'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    if ($interactionTimeMs === null || $interactionTimeMs === false || $interactionTimeMs <= 0) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Invalid interaction time.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }
    if ($interactionTimeMs < 200) {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'Interaction time too short.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $candidatoId = null;
    if ($tipoVoto === 'candidato') {
        $validatedCandidateId = filter_var($data['candidato_id'] ?? null, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if ($validatedCandidateId === null || $validatedCandidateId === false || $validatedCandidateId <= 0) {
            return voteBuildResponse(400, ['status' => 'error', 'message' => 'candidato_id is required for candidato votes.'], ['Content-Type' => 'application/json; charset=utf-8']);
        }
        $candidatoId = (string) $validatedCandidateId;
    } elseif (array_key_exists('candidato_id', $data) && trim((string) $data['candidato_id']) !== '') {
        return voteBuildResponse(400, ['status' => 'error', 'message' => 'candidato_id is only allowed for candidato votes.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $userAgent = voteNormalizeText((string)($server['HTTP_USER_AGENT'] ?? ''), 512);
    $cfPais = voteNormalizeText((string)($server['HTTP_CF_IPCOUNTRY'] ?? ''), 2);
    $cfPais = $cfPais !== '' ? $cfPais : null;

    $browserFingerprint = voteFingerprint(
        (string)($data['browser_fingerprint'] ?? ''),
        $userAgent,
        $client['ip']
    );

    $deviceToken = voteNormalizeText((string)($cookie['device_token'] ?? ''), 64);
    if ($deviceToken === '' || !preg_match('/\A[a-f0-9]{64}\z/i', $deviceToken)) {
        $deviceToken = voteDefaultDeviceToken();
    }

    $recentBurst = voteRecentBurstDetected($pdo, $encuestaId, $ipHash);

    $trustScore = voteScore([
        'district_match' => $districtMatch,
        'cf_country' => $cfPais,
        'gps_accuracy_meters' => $gpsAccuracy,
        'interaction_time_ms' => $interactionTimeMs,
        'device_cookie_present' => isset($cookie['device_token']) && trim((string) $cookie['device_token']) !== '',
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
            return voteBuildResponse(429, ['status' => 'error', 'message' => 'Vote already recorded or rate limited.'], ['Content-Type' => 'application/json; charset=utf-8']);
        }

        error_log('BL-14 votar.php failed: ' . $e->getMessage());
        return voteBuildResponse(500, ['status' => 'error', 'message' => 'Unable to record vote.'], ['Content-Type' => 'application/json; charset=utf-8']);
    }

    $cookieOptions = [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => !voteLocalDevEnabled(),
        'httponly' => true,
        'samesite' => 'Strict',
    ];

    return voteBuildResponse(
        200,
        ['status' => 'success', 'message' => 'Voto registrado y encriptado correctamente.'],
        ['Content-Type' => 'application/json; charset=utf-8'],
        [[
            'name' => 'device_token',
            'value' => $deviceToken,
            'options' => $cookieOptions,
        ]]
    );
}
