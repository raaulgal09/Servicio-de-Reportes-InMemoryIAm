<?php
require 'generarReporteLocal.php';

header('Content-Type: application/json; charset=utf-8');

$json_recibido = file_get_contents('php://input');

if (empty($json_recibido)) {
    http_response_code(400);
    echo json_encode([
        "error" => "No se recibió JSON en el body."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$datos = json_decode($json_recibido, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "error" => "JSON inválido: " . json_last_error_msg()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

    return $scheme . '://' . $host . $scriptDir;
}

try {
    $directorio = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';
    $ruta = generarReporteLocal($json_recibido, $directorio, 'reporte_InMemoryIAM');
    $nombre = basename($ruta);
    $link = rtrim(base_url(), '/\\') . '/reportes/' . rawurlencode($nombre);

    http_response_code(201);
    echo json_encode([
        "status" => "exito",
        "nombre" => $nombre,
        "link" => $link
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}