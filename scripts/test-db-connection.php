<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = require __DIR__ . '/../includes/db.php';
$row = $pdo->query('SELECT DATABASE() AS database_name, NOW() AS server_time')->fetch();
$encuestas = $pdo->query('SELECT COUNT(*) AS total FROM encuestas')->fetch();

echo json_encode([
    'status' => 'ok',
    'database' => $row['database_name'] ?? null,
    'server_time' => $row['server_time'] ?? null,
    'encuestas_total' => (int) ($encuestas['total'] ?? 0),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
