<?php
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $scheme . '://' . $host . $scriptDir;
}

$base = rtrim(base_url(), '/\\');
$list = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            $list[] = [
                'nombre' => $f,
                'link' => $base . '/reportes/' . rawurlencode($f)
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['reportes' => $list]);