<?php
// Use the remote Hostinger MySQL host in CLI/local contexts, but the
// production website should connect to the local loopback MySQL server.
$dbHost = 'srv469.hstgr.io';
if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
    $host = strtolower((string) $_SERVER['HTTP_HOST']);
    if ($host === 'encuestaselectorales.pe' || str_ends_with($host, '.encuestaselectorales.pe')) {
        $dbHost = '127.0.0.1';
    }
}

return [
    'dsn' => "mysql:host={$dbHost};dbname=u185878096_encuestas;charset=utf8mb4",
    'user' => 'u185878096_encuestas_app',
    'pass' => 'Codexito1234.',
];
