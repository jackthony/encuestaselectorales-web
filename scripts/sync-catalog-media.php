<?php
declare(strict_types=1);

/**
 * Syncs candidate photos and party logos from remote URLs into Hostinger-backed
 * local storage for the Laravel public app.
 *
 * Usage:
 *   php scripts/sync-catalog-media.php
 *   php scripts/sync-catalog-media.php --force
 */

function resolvePdo(string $root): PDO
{
    $configFiles = [
        $root . '/config/db.php',
        dirname($root) . '/config/db.php',
    ];

    foreach ($configFiles as $configFile) {
        if (!is_file($configFile)) {
            continue;
        }

        $config = require $configFile;
        if (!is_array($config) || !isset($config['dsn'], $config['user'], $config['pass'])) {
            continue;
        }

        return new PDO(
            $config['dsn'],
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    throw new RuntimeException('No database config found.');
}

function downloadBinary(string $url): array
{
    $url = trim($url);
    if ($url === '') {
        return ['ok' => false, 'error' => 'empty-url'];
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['ok' => false, 'error' => 'curl-init-failed'];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Codex media sync)',
            CURLOPT_HTTPHEADER => ['Accept: image/*,*/*;q=0.8'],
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            return ['ok' => false, 'error' => $error !== '' ? $error : ('http-' . $status)];
        }

        return [
            'ok' => true,
            'body' => $body,
            'content_type' => $contentType,
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 25,
            'header' => "User-Agent: Mozilla/5.0 (Codex media sync)\r\nAccept: image/*,*/*;q=0.8\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return ['ok' => false, 'error' => 'download-failed'];
    }

    return [
        'ok' => true,
        'body' => $body,
        'content_type' => '',
    ];
}

function ensureParentDir(string $path): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function syncOne(PDO $pdo, string $table, string $idColumn, string $uidColumn, string $urlColumn, string $localColumn, string $subdir, bool $force): array
{
    $rows = $pdo->query(
        "SELECT {$idColumn} AS id, {$uidColumn} AS uid, {$urlColumn} AS remote_url, {$localColumn} AS local_path
         FROM {$table}
         ORDER BY {$uidColumn}"
    )->fetchAll();

    $root = dirname(__DIR__);
    $publicRoot = $root . '/laravel-app/public';
    $stats = ['rows' => count($rows), 'updated' => 0, 'skipped' => 0, 'failed' => 0];

    foreach ($rows as $row) {
        $uid = (string) ($row['uid'] ?? '');
        $remoteUrl = trim((string) ($row['remote_url'] ?? ''));
        if ($uid === '' || $remoteUrl === '') {
            $stats['skipped']++;
            continue;
        }

        $relativePath = 'assets/img/elections/' . $subdir . '/' . $uid . '.jpg';
        $absolutePath = $publicRoot . '/' . $relativePath;

        if (is_file($absolutePath) && !$force) {
            if (($row['local_path'] ?? '') !== $relativePath) {
                $stmt = $pdo->prepare("UPDATE {$table} SET {$localColumn} = :local_path WHERE {$idColumn} = :id");
                $stmt->execute([
                    'local_path' => $relativePath,
                    'id' => $row['id'],
                ]);
            }

            $stats['skipped']++;
            continue;
        }

        $result = downloadBinary($remoteUrl);
        if (!($result['ok'] ?? false)) {
            $stats['failed']++;
            continue;
        }

        ensureParentDir($absolutePath);
        file_put_contents($absolutePath, (string) ($result['body'] ?? ''));

        $stmt = $pdo->prepare("UPDATE {$table} SET {$localColumn} = :local_path WHERE {$idColumn} = :id");
        $stmt->execute([
            'local_path' => $relativePath,
            'id' => $row['id'],
        ]);
        $stats['updated']++;
    }

    return $stats;
}

$force = in_array('--force', $argv, true);
$root = dirname(__DIR__);
$pdo = resolvePdo($root);

$partyStats = syncOne($pdo, 'political_organizations', 'id', 'organization_uid', 'party_logo_url', 'party_logo_local_path', 'parties', $force);
$candidateStats = syncOne($pdo, 'candidates', 'id', 'candidate_uid', 'candidate_photo_url', 'candidate_photo_local_path', 'candidates', $force);

echo 'Party logos: ' . json_encode($partyStats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
echo 'Candidate photos: ' . json_encode($candidateStats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
