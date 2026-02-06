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
                'name' => $f,
                'mtime' => filemtime($path),
                'size' => $size
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
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

$base = rtrim(base_url(), '/\\');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InMemoryIAM - Reportes | UASLP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --uaslp-azul: #002d5e; 
      --uaslp-azul-light: #003d7a;
      --uaslp-dorado: #d69e2e;
      --uaslp-dorado-claro: #ecc94b;
      --gris-fondo: #f8fafc;
      --gris-borde: #e2e8f0;
      --blanco: #ffffff;
      --texto-oscuro: #1e293b;
      --texto-gris: #64748b;
      --rojo-pdf: #c53030;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Open Sans', sans-serif;
      background: var(--gris-fondo);
      color: var(--texto-oscuro);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header.main-header {
      background: var(--uaslp-azul);
      padding: 12px 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .logo-section { display: flex; align-items: center; gap: 20px; }
    .logo-container { width: 85px; height: auto; }
    .logo-container img { max-width: 100%; height: auto; display: block; }

    .header-text { color: var(--blanco); }
    .system-title { font-family: 'Montserrat', sans-serif; font-size: 26px; font-weight: 800; }
    .system-subtitle { font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1.5px; margin-top: -2px; }

    .golden-bar {
      height: 5px;
      background: linear-gradient(90deg, #b7791f, var(--uaslp-dorado), var(--uaslp-dorado-claro), var(--uaslp-dorado), #b7791f);
    }

    main { flex: 1; max-width: 1200px; width: 100%; margin: 0 auto; padding: 40px 24px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px; }

    .stat-card {
      background: var(--blanco);
      border-radius: 12px; padding: 24px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border-left: 5px solid var(--uaslp-azul);
      display: flex; align-items: center; gap: 20px;
    }

    .stat-icon { 
      width: 54px; height: 54px; border-radius: 12px; 
      display: flex; align-items: center; justify-content: center; 
      background: #f1f5f9; 
    }
    .stat-icon svg { width: 28px; height: 28px; }
    .icon-blue { stroke: var(--uaslp-azul); }
    .icon-gold { stroke: var(--uaslp-dorado); }
    .icon-red { stroke: var(--rojo-pdf); }

    .stat-info h3 { font-family: 'Montserrat', sans-serif; font-size: 22px; color: var(--texto-oscuro); font-weight: 700; }
    .stat-info p { font-size: 12px; color: var(--texto-gris); font-weight: 600; text-transform: uppercase; }

    .card { background: var(--blanco); border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid var(--gris-borde); }
    .card-header { background: #f8fafc; padding: 20px 24px; border-bottom: 1px solid var(--gris-borde); }
    .card-header h2 { font-family: 'Montserrat', sans-serif; font-size: 18px; color: var(--uaslp-azul); font-weight: 700; }

    table { width: 100%; border-collapse: collapse; }
    th { padding: 15px 24px; text-align: left; font-family: 'Montserrat', sans-serif; font-size: 11px; text-transform: uppercase; color: var(--texto-gris); background: #f1f5f9; letter-spacing: 1px; }
    td { padding: 16px 24px; border-bottom: 1px solid var(--gris-borde); font-size: 14px; }
    tbody tr:hover { background: #f9fafb; }

    .file-name { font-weight: 600; color: var(--uaslp-azul); text-decoration: none; }
    .file-icon-tag { background: #fee2e2; color: var(--rojo-pdf); padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; margin-right: 10px; }

    .btn-download {
      background: var(--uaslp-azul);
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-family: 'Montserrat', sans-serif;
      font-size: 12px;
      font-weight: 700;
      transition: 0.3s;
      display: inline-flex;
      align-items: center; gap: 8px;
    }
    .btn-download:hover { background: var(--uaslp-azul-light); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,45,94,0.3); }

    footer { background: var(--uaslp-azul); color: var(--blanco); padding: 40px 24px; margin-top: auto; border-top: 5px solid var(--uaslp-dorado); }
    .footer-container { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; opacity: 0.9; }
    
    @media (max-width: 768px) {
      .header-content, .footer-container { flex-direction: column; text-align: center; gap: 20px; }
      .stats-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <header class="main-header">
    <div class="header-content">
      <div class="logo-section">
        <div class="logo-container"><img src="img/EMBLEMA-AZUL-V.png" alt="UASLP"></div>
        <div class="header-text">
          <h1 class="system-title">InMemoryIAM</h1>
          <div class="system-subtitle">Universidad Autónoma de San Luis Potosí</div>
        </div>
      </div>
    </div>
  </header>
  <div class="golden-bar"></div>

  <main>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
        </div>
        <div class="stat-info">
          <h3><?php echo count($files); ?></h3>
          <p>Reportes Generados</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3m18 0c0-1.66-4-3-9-3s-9 1.34-9 3m18 0v6c0 1.66-4 3-9 3s-9-1.34-9-3v-6"></path></svg>
        </div>
        <div class="stat-info">
          <h3><?php echo formatBytes($totalSize); ?></h3>
          <p>Tamaño en Disco</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
        <div class="stat-info">
          <h3>PDF</h3>
          <p>Formato de Salida</p>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Listado de Documentos Disponibles</h2>
      </div>
      
      <?php if (empty($files)): ?>
        <div style="padding: 60px; text-align: center; color: var(--texto-gris);">
          <p>No hay archivos disponibles en el repositorio.</p>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Nombre del Archivo</th>
              <th>Fecha de Emisión</th>
              <th>Tamaño</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file): ?>
              <tr>
                <td>
                  <span class="file-icon-tag">PDF</span>
                  <span class="file-name"><?php echo htmlspecialchars($file['name']); ?></span>
                </td>
                <td style="color: var(--texto-gris);"><?php echo date('d M, Y | H:i', $file['mtime']); ?></td>
                <td style="color: var(--texto-gris);"><?php echo formatBytes($file['size']); ?></td>
                <td>
                  <a class="btn-download" href="<?php echo $base . '/reportes/' . rawurlencode($file['name']); ?>" download>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"></path></svg>
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
    <div class="footer-container">
      <div class="footer-text">
        <p><strong>UNIVERSIDAD AUTÓNOMA DE SAN LUIS POTOSÍ</strong></p>
        <p>Sistema InMemoryIAM - Gestión Documental</p>
      </div>
      <div class="footer-text" style="text-align: right;">
        <p>&copy; <?php echo date('Y'); ?> UASLP</p>
        <p>San Luis Potosí, México</p>
      </div>
    </div>
  </footer>
</body>
</html>