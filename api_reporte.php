<?php
require 'generarReporteLocal.php';

$json_recibido = file_get_contents('php://input');

if (empty($json_recibido)) {
    http_response_code(400);
    echo json_encode(["error" => "No se recibió JSON en el body."]);
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
    $link = rtrim(base_url(), '/\\') . '/reportes/' . $nombre;
    http_response_code(201);
    echo json_encode(["status" => "exito", "nombre" => $nombre, "link" => $link]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}