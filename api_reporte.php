<?php
require 'generarReporteLocal.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(int $codigo, array $payload): void
{
    http_response_code($codigo);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

    return $scheme . '://' . $host . $scriptDir;
}

function validarEstructuraMinima(array $datos): array
{
    $errores = [];

    if (!isset($datos['reporte_info']) || !is_array($datos['reporte_info'])) {
        $errores[] = "Falta el objeto 'reporte_info'.";
    } else {
        $camposReporte = ['titulo', 'mes', 'ano', 'total_interacciones'];

        foreach ($camposReporte as $campo) {
            if (!array_key_exists($campo, $datos['reporte_info']) || $datos['reporte_info'][$campo] === '') {
                $errores[] = "Falta el campo obligatorio 'reporte_info.$campo'.";
            }
        }
    }

    if (!isset($datos['interacciones_destacadas']) || !is_array($datos['interacciones_destacadas'])) {
        $errores[] = "Falta el arreglo 'interacciones_destacadas'.";
    }

    if (!isset($datos['estadisticas_personalidad']) || !is_array($datos['estadisticas_personalidad'])) {
        $errores[] = "Falta el arreglo 'estadisticas_personalidad'.";
    }

    return $errores;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responderJson(405, [
        "error" => "Método no permitido. Usa POST."
    ]);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if ($contentType !== '' && stripos($contentType, 'application/json') === false) {
    responderJson(415, [
        "error" => "Content-Type no soportado. Usa application/json."
    ]);
}

$json_recibido = file_get_contents('php://input');

if ($json_recibido === false || trim($json_recibido) === '') {
    responderJson(400, [
        "error" => "No se recibió JSON en el body."
    ]);
}

$datos = json_decode($json_recibido, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    responderJson(400, [
        "error" => "JSON inválido: " . json_last_error_msg()
    ]);
}

$erroresValidacion = validarEstructuraMinima($datos);

if (!empty($erroresValidacion)) {
    responderJson(422, [
        "error" => "El JSON es válido, pero está incompleto o mal estructurado.",
        "detalles" => $erroresValidacion
    ]);
}

try {
    $directorio = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';
    $ruta = generarReporteLocal($json_recibido, $directorio, 'reporte_InMemoryIAM');

    if (!is_file($ruta)) {
        throw new Exception("El archivo PDF no fue generado correctamente.");
    }

    $nombre = basename($ruta);
    $link = rtrim(base_url(), '/\\') . '/reportes/' . rawurlencode($nombre);

    responderJson(201, [
        "status" => "exito",
        "nombre" => $nombre,
        "link" => $link
    ]);

} catch (Throwable $e) {
    error_log("Error al generar reporte: " . $e->getMessage());

    responderJson(500, [
        "error" => "Ocurrió un error al generar el reporte."
    ]);
}