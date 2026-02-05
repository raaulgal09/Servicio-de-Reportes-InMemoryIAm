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

usort($files, function ($a, $b) { return $b['mtime'] <=> $a['mtime']; });

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    return $scheme . '://' . $host . $scriptDir;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
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
  <title>InMemoryIAM - Reportes | UASLP</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --uaslp-azul: #1a365d;
      --uaslp-azul-brillante: #2c5282;
      --uaslp-dorado: #d69e2e;
      --uaslp-dorado-hover: #b7791f;
      --gris-fondo: #f8fafc;
      --gris-borde: #e2e8f0;
      --blanco: #ffffff;
      --texto: #2d3748;
      --rojo-pdf: #c53030;
    }

    * { box-sizing: border-box; }
    body { font-family: 'Open Sans', sans-serif; background: var(--gris-fondo); color: var(--texto); margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
    
    header.main-nav { 
      background: linear-gradient(135deg, var(--uaslp-azul) 0%, var(--uaslp-azul-brillante) 100%); 
      color: #fff; 
      padding: 30px 20px; 
      border-bottom: 5px solid var(--uaslp-dorado);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .container { max-width: 1100px; margin: 0 auto; width: 100%; }
    
    header h1 { margin: 0; font-family: 'Montserrat', sans-serif; font-size: 28px; font-weight: 800; letter-spacing: 0.5px; }
    header p { margin: 5px 0 0; font-size: 14px; opacity: 0.9; font-weight: 600; color: #ecc94b; }

    main { flex: 1; padding: 40px 20px; }

    .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .summary .box {
      background: var(--blanco);
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.04);
      border: 1px solid var(--gris-borde);
      display: flex; align-items: center; gap: 20px;
    }
    .box-icon { 
      width: 48px; height: 48px; border-radius: 10px; 
      display: flex; align-items: center; justify-content: center;
      background: #ebf4ff; color: var(--uaslp-azul-brillante);
    }
    .box-data span { font-size: 12px; color: #718096; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
    .box-data strong { display: block; font-size: 22px; color: var(--uaslp-azul); font-family: 'Montserrat', sans-serif; }

    .card { background: var(--blanco); border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid var(--gris-borde); }
    .card-header { 
      background: #f1f5f9; padding: 20px 25px; 
      border-bottom: 1px solid var(--gris-borde);
      font-family: 'Montserrat', sans-serif; font-weight: 700; color: var(--uaslp-azul);
      display: flex; align-items: center; gap: 12px;
    }

    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 600px; }
    th { background: #f8fafc; padding: 15px 25px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid var(--gris-borde); }
    td { padding: 18px 25px; border-bottom: 1px solid var(--gris-borde); font-size: 14px; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fbff; }

    .file-info { display: flex; align-items: center; gap: 15px; }
    .file-icon {
      width: 40px; height: 40px;
      background: #fff5f5; color: var(--rojo-pdf);
      border: 1px solid #feb2b2; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 10px; flex-shrink: 0;
    }

    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 20px;
      background: var(--uaslp-azul); color: #fff;
      text-decoration: none; border-radius: 8px;
      font-size: 13px; font-weight: 600;
      transition: all 0.2s ease;
      border: none; cursor: pointer;
    }
    .btn:hover { background: var(--uaslp-dorado); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(214, 158, 46, 0.3); }

    footer { background: var(--uaslp-azul); color: #fff; padding: 40px 20px; text-align: center; }
    footer p { margin: 5px 0; opacity: 0.8; font-size: 13px; }
    .footer-logo { max-width: 120px; margin-bottom: 15px; filter: brightness(0) invert(1); opacity: 0.9; }
  </style>
</head>
<body>

<header class="main-nav">
  <div class="container">
    <h1>InMemoryIAM</h1>
    <p>UNIVERSIDAD AUTÓNOMA DE SAN LUIS POTOSÍ</p>
  </div>
</header>

<main class="container">
  <section class="summary">
    <div class="box">
      <div class="box-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
      </div>
      <div class="box-data">
        <span>Reportes totales</span>
        <strong><?php echo count($files); ?> Archivos</strong>
      </div>
    </div>
    <div class="box">
      <div class="box-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
      </div>
      <div class="box-data">
        <span>Espacio utilizado</span>
        <strong><?php echo formatBytes($totalSize); ?></strong>
      </div>
    </div>
  </section>

  <div class="card">
    <div class="card-header">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
      Repositorio de Documentos Generados
    </div>

    <div class="table-container">
      <?php if (empty($files)): ?>
        <div style="padding: 60px; text-align: center; color: #94a3b8;">
          <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom: 15px; opacity: 0.5;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <p style="font-size: 16px; font-weight: 600;">No se encontraron reportes PDF</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Nombre del Documento</th>
              <th>Fecha de Creación</th>
              <th>Tamaño</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file): ?>
              <tr>
                <td>
                  <div class="file-info">
                    <div class="file-icon">PDF</div>
                    <span style="font-weight: 600; color: var(--uaslp-azul);"><?php echo htmlspecialchars($file['name']); ?></span>
                  </div>
                </td>
                <td style="color: #475569; font-weight: 500;"><?php echo date('d/m/Y H:i', $file['mtime']); ?></td>
                <td style="color: #64748b; font-size: 13px;"><?php echo formatBytes($file['size']); ?></td>
                <td>
                  <a class="btn" href="<?php echo $base . '/reportes/' . rawurlencode($file['name']); ?>" download>
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Descargar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</main>

<footer>
  <div class="container">
    <p><strong>UNIVERSIDAD AUTÓNOMA DE SAN LUIS POTOSÍ</strong></p>
    <p>InMemoryIAM &copy; <?php echo date('Y'); ?> | Sistema de Reportes Institucionales</p>
  </div>
</footer>

</body>
</html>