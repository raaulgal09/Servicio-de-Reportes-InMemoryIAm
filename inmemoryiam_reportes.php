<?php
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';
$files = [];
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            $files[] = [
                'name' => $f,
                'mtime' => filemtime($path)
            ];
        }
    }
}
usort($files, function ($a, $b) { return $b['mtime'] <=> $a['mtime']; });

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $scheme . '://' . $host . $scriptDir;
}

$base = rtrim(base_url(), '/\\');
?><!DOCTYPE html>
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
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 1px 2px rgba(0,0,0,0.06);overflow:hidden}
    .card header{background:#f3f4f6;color:#111;padding:12px 16px;border-bottom:1px solid #e5e7eb}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left}
    th{background:#fafafa;font-weight:600}
    tr:hover{background:#f9fafb}
    .empty{padding:16px;color:#6b7280}
    .btn{display:inline-block;padding:8px 12px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px}
  </style>
</head>
<body>
  <header>
    <h1>InMemoryIAM reportes</h1>
  </header>
  <main>
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
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file): ?>
              <tr>
                <td><?php echo htmlspecialchars($file['name']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', $file['mtime']); ?></td>
                <td>
                  <a class="btn" href="<?php echo $base . '/reportes/' . rawurlencode($file['name']); ?>" download>Descargar</a>
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