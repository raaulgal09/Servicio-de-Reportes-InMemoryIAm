<?php
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ver_reportes.php');
    exit;
}

$archivo = basename($_POST['archivo'] ?? '');

if ($archivo === '') {
    header('Location: ver_reportes.php?error=archivo_vacio');
    exit;
}

if (strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) !== 'pdf') {
    header('Location: ver_reportes.php?error=archivo_no_valido');
    exit;
}

$ruta = $dir . DIRECTORY_SEPARATOR . $archivo;

if (!is_file($ruta)) {
    header('Location: ver_reportes.php?error=no_encontrado');
    exit;
}

if (unlink($ruta)) {
    header('Location: ver_reportes.php?eliminado=1');
    exit;
}

header('Location: ver_reportes.php?error=no_eliminado');
exit;