<?php
// 1. Solo permitir peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit;
}

// 2. Comprobar que la petición viene de tu propia web (ajusta tu dominio cuando esté activo)
$referer = $_SERVER['HTTP_REFERER'] ?? '';
// Si quieres activarlo estrictamente cuando tengas el dominio definitivo, descomenta las 4 líneas siguientes:
/*
if (strpos($referer, 'ateliertapiceria.es') === false) {
    http_response_code(403);
    exit;
}
*/

date_default_timezone_set('Europe/Madrid');
$archivo_log = 'registro_visitas_jhfg8vsdgf7.txt';

// 3. Protección de disco: si el log supera los 5 MB (5.242.880 bytes), no escribir más
if (file_exists($archivo_log) && filesize($archivo_log) > 5242880) {
    http_response_code(204);
    exit;
}

// 4. Recoger y sanear los datos para evitar texto malicioso o saltos de línea falsos
$fecha = date('Y-m-d H:i:s');
$ip = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'IP no válida';
$navegador_raw = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
// Eliminamos etiquetas HTML y limitamos a 150 caracteres
$navegador = substr(preg_replace('/[\r\n]+/', ' ', strip_tags($navegador_raw)), 0, 150);

// Filtrar bots conocidos por su User-Agent para no ensuciar tus métricas
if (preg_match('/bot|crawl|slurp|spider|mediapartners/i', $navegador)) {
    http_response_code(204);
    exit;
}

// Contar visitas y guardar
$total_visitas = file_exists($archivo_log) ? count(file($archivo_log)) + 1 : 1;
$linea = "Visita #$total_visitas | Fecha: $fecha | IP: $ip | Dispositivo: $navegador" . PHP_EOL;

file_put_contents($archivo_log, $linea, FILE_APPEND | LOCK_EX);

http_response_code(204);
?>
