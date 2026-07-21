<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "DOCUMENT_ROOT=" . ($_SERVER['DOCUMENT_ROOT'] ?? '(none)') . PHP_EOL;
echo 'local_config=' . (is_file(__DIR__ . '/config/db.php') ? 'yes' : 'no') . PHP_EOL;
echo 'prod_config=' . (isset($_SERVER['DOCUMENT_ROOT']) && is_file(dirname($_SERVER['DOCUMENT_ROOT']) . '/config/db.php') ? 'yes' : 'no') . PHP_EOL;

try {
    $pdo = require __DIR__ . '/includes/db.php';
    echo 'pdo=' . ($pdo instanceof PDO ? 'ok' : 'no') . PHP_EOL;

    $total = (int) $pdo->query('SELECT COUNT(*) FROM encuestas')->fetchColumn();
    $activas = (int) $pdo->query("SELECT COUNT(*) FROM encuestas WHERE estado_publicacion = 'producción' AND NOW() BETWEEN fecha_apertura AND fecha_cierre")->fetchColumn();

    echo 'encuestas_total=' . $total . PHP_EOL;
    echo 'encuestas_activas=' . $activas . PHP_EOL;
} catch (Throwable $e) {
    echo 'error=' . $e->getMessage() . PHP_EOL;
}
