<?php
/**
 * PDO connection bootstrap. Resolves /config/db.php from one of two
 * locations depending on environment (bl-13b-encuestas-rondas-schema design.md):
 *   - local dev:  repo_root/config/db.php   (gitignored, inside this checkout)
 *   - production: sibling of public_html/    (outside the web root, per
 *     CLAUDE.md's secret-isolation rule — Hostinger's DOCUMENT_ROOT points
 *     at public_html/, so dirname() of that is the sibling directory)
 * Each config file returns ['dsn' => ..., 'user' => ..., 'pass' => ...].
 */

$localConfig = __DIR__ . '/../config/db.php';
$prodConfig  = isset($_SERVER['DOCUMENT_ROOT'])
    ? dirname($_SERVER['DOCUMENT_ROOT']) . '/config/db.php'
    : null;

$configPath = null;
if (is_file($localConfig)) {
    $configPath = $localConfig;
} elseif ($prodConfig !== null && is_file($prodConfig)) {
    $configPath = $prodConfig;
}

$config = [
    'dsn' => 'mysql:host=srv469.hstgr.io;dbname=u185878096_encuestas;charset=utf8mb4',
    'user' => 'u185878096_encuestas_app',
    'pass' => 'Codexito1234.',
];

if ($configPath !== null) {
    $config = array_merge($config, require $configPath);
}

$pdo = new PDO(
    $config['dsn'],
    $config['user'],
    $config['pass'],
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

return $pdo;
