<?php
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

    return $scheme . '://' . $host . $scriptDir;
}

function formatBytes($bytes, $precision = 2): string {
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);

    $bytes = $bytes / (1024 ** $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

$base = rtrim(base_url(), '/\\');
$list = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $f;

        if (is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            $list[] = [
                'nombre' => $f,
                'link' => $base . '/reportes/' . rawurlencode($f),
                'fecha' => date('d/m/Y H:i', filemtime($path)),
                'timestamp' => filemtime($path),
                'tamano' => formatBytes(filesize($path))
            ];
        }
    }
}

usort($list, function ($a, $b) {
    return $b['timestamp'] <=> $a['timestamp'];
});

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'status' => 'exito',
    'total' => count($list),
    'reportes' => $list
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);