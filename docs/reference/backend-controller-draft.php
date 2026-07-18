<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://encuestaselectorales.pe'); 
header('Access-Control-Allow-Credentials: true');

// Variables de entorno (En Hostinger, ideal guardarlas fuera de public_html)
$db_host = 'localhost';
$db_user = 'tu_usuario_db';
$db_pass = 'tu_pass_seguro';
$db_name = 'tu_base_datos';
$secret_key = 'TU_LLAVE_MAESTRA_AES_256'; // Llave para desencriptar IPs en tu Dashboard
$ip_salt = 'sal_aleatoria_para_hashes_123'; 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Método no permitido']));
}

// Leer payload del frontend
$data = json_decode(file_get_contents("php://input"), true);

// Validación de presencia de GPS (Estricta)
if (empty($data['gps_lat']) || empty($data['gps_lng'])) {
    die(json_encode(['error' => 'El voto requiere validación geográfica.']));
}

$id_seguro = bin2hex(random_bytes(16)); // ID indescifrable (32 caracteres)
$encuesta_id = $data['encuesta_id'] ?? 'alcaldia-lima-2026';
$ubigeo_votacion = $data['ubigeo_votacion'];
$candidato_id = (int)$data['candidato_id'];
$gps_lat = (float)$data['gps_lat'];
$gps_lng = (float)$data['gps_lng'];
$gps_accuracy = (int)$data['gps_accuracy'];
$interaction_time = (int)$data['interaction_time_ms'];
$is_out_of_district = $data['is_out_of_district'] ? 1 : 0;
$fingerprint = $data['fingerprint']; 
$device_token = $_COOKIE['device_token'] ?? bin2hex(random_bytes(32)); 

$ip_real = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$cf_pais = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'PE';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// 1. Hash de la IP (Unidireccional, para validación de doble voto)
$ip_hash = hash('sha256', $ip_real . $ip_salt);

// 2. Cifrado de la IP (Reversible, solo para tu Inteligencia Electoral)
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
$ip_cifrada_raw = openssl_encrypt($ip_real, 'aes-256-cbc', $secret_key, 0, $iv);
$ip_cifrada = base64_encode($ip_cifrada_raw . '::' . $iv); 

try {
    $stmt = $pdo->prepare("
        INSERT INTO votos_interactivos 
        (id, encuesta_id, ubigeo_votacion, candidato_id, gps_lat, gps_lng, gps_accuracy_meters, is_out_of_district, ip_cifrada, ip_hash, browser_fingerprint, device_token, interaction_time_ms, cf_pais, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_seguro, $encuesta_id, $ubigeo_votacion, $candidato_id, 
        $gps_lat, $gps_lng, $gps_accuracy, $is_out_of_district, 
        $ip_cifrada, $ip_hash, $fingerprint, $device_token, 
        $interaction_time, $cf_pais, $user_agent
    ]);
    
    // Devolvemos el Token al usuario para bloquear futuros votos
    setcookie('device_token', $device_token, time() + (86400 * 30), "/", "", true, true);
    
    echo json_encode(['success' => true, 'message' => 'Voto validado y registrado exitosamente.']);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // UNIQUE CONSTRAINT FAIL
        http_response_code(429);
        echo json_encode(['error' => 'Ya hemos registrado un voto desde este dispositivo o conexión.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor.']);
    }
}
?>