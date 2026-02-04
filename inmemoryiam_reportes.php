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
  <title>InMemoryIAM - Gestión de Reportes | UASLP</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --uaslp-azul: #1a365d;
      --uaslp-dorado: #d69e2e;
      --gris-fondo: #f7fafc;
      --gris-borde: #e2e8f0;
      --blanco: #ffffff;
      --texto: #2d3748;
    }

    body { font-family: 'Open Sans', sans-serif; background: var(--gris-fondo); color: var(--texto); margin: 0; }
    
    header.main-nav { 
      background: var(--uaslp-azul); 
      color: #fff; 
      padding: 20px; 
      border-bottom: 4px solid var(--uaslp-dorado);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    header.main-nav h1 { margin: 0; font-family: 'Montserrat', sans-serif; font-size: 22px; }
    header.main-nav p { margin: 4px 0 0; font-size: 13px; opacity: 0.8; }

    main { max-width: 1000px; margin: 30px auto; padding: 0 20px; }

    .summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .summary .box {
      background: var(--blanco);
      border-left: 4px solid var(--uaslp-dorado);
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .summary .box span { font-size: 13px; color: #718096; text-transform: uppercase; font-weight: 600; }
    .summary .box strong { display: block; font-size: 22px; margin-top: 8px; color: var(--uaslp-azul); }

    .card { background: var(--blanco); border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid var(--gris-borde); }
    .card-header { 
      background: #f8fafc; 
      padding: 16px 20px; 
      border-bottom: 1px solid var(--gris-borde);
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      color: var(--uaslp-azul);
    }

    table { width: 100%; border-collapse: collapse; }
    th { background: #f1f5f9; padding: 12px 20px; text-align: left; font-size: 12px; color: #64748b; text-transform: uppercase; }
    td { padding: 16px 20px; border-bottom: 1px solid var(--gris-borde); font-size: 14px; }
    tr:hover { background: #fcfcfd; }

    .file-info { display: flex; align-items: center; gap: 12px; }
    .file-icon {
      width: 40px; height: 40px;
      background: #fff5f5; color: #c53030;
      border: 1px solid #feb2b2; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 11px;
    }

    .status {
      padding: 4px 12px; border-radius: 20px;
      font-size: 12px; font-weight: 600;
      background: #c6f6d5; color: #166534;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      background: var(--uaslp-azul);
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      transition: background 0.2s;
    }
    .btn:hover { background: #2c5282; }

    footer { text-align: center; padding: 40px 0; color: #718096; font-size: 13px; }
  </style>
</head>
<body>

<header class="main-nav">
  <div style="max-width: 1000px; margin: 0 auto;">
    <h1>InMemoryIAM</h1>
    <p>Universidad Autónoma de San Luis Potosí</p>
  </div>
</header>

<main>
  <section class="summary">
    <div class="box">
      <span>Total de reportes</span>
      <strong><?php echo count($files); ?> Archivos</strong>
    </div>
    <div class="box">
      <span>Espacio en disco</span>
      <strong><?php echo formatBytes($totalSize); ?></strong>
    </div>
  </section>

  <div class="card">
    <div class="card-header">
      Listado de Documentos Institucionales
    </div>

    <?php if (empty($files)): ?>
      <div style="padding: 40px; text-align: center; color: #a0aec0;">No se han generado reportes PDF todavía.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Documento</th>
            <th>Generado el</th>
            <th>Tamaño</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($files as $file): ?>
            <tr>
              <td>
                <div class="file-info">
                  <div class="file-icon">PDF</div>
                  <span style="font-weight: 600;"><?php echo htmlspecialchars($file['name']); ?></span>
                </div>
              </td>
              <td style="color: #4a5568;"><?php echo date('d/m/Y H:i', $file['mtime']); ?></td>
              <td style="color: #718096;"><?php echo formatBytes($file['size']); ?></td>
              <td><span class="status">Listo</span></td>
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

<footer>
  &copy; <?php echo date('Y'); ?> UASLP - Sistema Institucional de Reportes
</footer>

</body>
</html>