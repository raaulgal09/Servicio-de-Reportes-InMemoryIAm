<?php
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';
$files = [];
$totalSize = 0;

if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;

        if (is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            $size = filesize($path);
            $totalSize += $size;

            $files[] = [
                'name'  => $f,
                'mtime' => filemtime($path),
                'size'  => $size
            ];
        }
    }
}

usort($files, function ($a, $b) {
    return $b['mtime'] <=> $a['mtime'];
});

$lastUpdate = !empty($files) ? $files[0]['mtime'] : null;

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $scheme . '://' . $host . $scriptDir;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
}

$base = rtrim(base_url(), '/\\');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InMemoryIAM reportes</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f9;color:#222;margin:0}
    header{background:#1f2937;color:#fff;padding:16px}
    h1{margin:0;font-size:20px}
    main{max-width:960px;margin:24px auto;padding:0 16px}

    .summary{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
      gap:16px;
      margin-bottom:24px;
    }
    .summary .box{
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:8px;
      padding:16px;
      box-shadow:0 1px 2px rgba(0,0,0,0.06);
      font-size:14px;
    }
    .summary .box strong{
      display:block;
      font-size:18px;
      margin-top:6px;
    }

    .card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.06);overflow:hidden}
    .card header{background:#f3f4f6;color:#111;padding:12px 16px;border-bottom:1px solid #e5e7eb}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left}
    th{background:#fafafa;font-weight:600}
    tr:hover{background:#f9fafb}
    .empty{padding:16px;color:#6b7280}
    .btn{display:inline-block;padding:8px 12px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px}
    .size{color:#6b7280;font-size:13px}
  </style>
</head>
<body>

<header>
  <h1>InMemoryIAM reportes</h1>
</header>

<main>

  <!-- RESUMEN -->
  <section class="summary">
    <div class="box">
      Total de reportes
      <strong><?php echo count($files); ?></strong>
    </div>
    <div class="box">
      Tamaño total
      <strong><?php echo formatBytes($totalSize); ?></strong>
    </div>
    <div class="box">
      Última actualización
      <strong>
        <?php echo $lastUpdate ? date('Y-m-d H:i', $lastUpdate) : '--'; ?>
      </strong>
    </div>
  </section>

  <div class="card">
    <header>
      <strong>Listado de reportes PDF</strong>
    </header>

    <?php if (empty($files)): ?>
      <div class="empty">No hay reportes generados.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Tamaño</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($files as $file): ?>
            <tr>
              <td><?php echo htmlspecialchars($file['name']); ?></td>
              <td><?php echo date('Y-m-d H:i:s', $file['mtime']); ?></td>
              <td class="size"><?php echo formatBytes($file['size']); ?></td>
              <td>
                <a class="btn" href="<?php echo $base . '/reportes/' . rawurlencode($file['name']); ?>" download>
                  Descargar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</main>
</body>
</html>
