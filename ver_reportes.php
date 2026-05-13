<?php
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
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'reportes';

$busqueda = trim($_GET['q'] ?? '');
$preview = basename($_GET['preview'] ?? '');

$files = [];
$totalSize = 0;

if (is_dir($dir)) {
    foreach (scandir($dir) as $archivo) {
        if ($archivo === '.' || $archivo === '..') {
            continue;
        }

        $ruta = $dir . DIRECTORY_SEPARATOR . $archivo;

        if (is_file($ruta) && strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf') {
            if ($busqueda !== '' && stripos($archivo, $busqueda) === false) {
                continue;
            }

            $size = filesize($ruta);
            $totalSize += $size;

            $files[] = [
                'name' => $archivo,
                'mtime' => filemtime($ruta),
                'size' => $size,
                'link' => $base . '/reportes/' . rawurlencode($archivo)
            ];
        }
    }
}

usort($files, function ($a, $b) {
    return $b['mtime'] <=> $a['mtime'];
});

$previewExiste = false;
$previewLink = '';

if ($preview !== '') {
    $previewPath = $dir . DIRECTORY_SEPARATOR . $preview;

    if (
        is_file($previewPath) &&
        strtolower(pathinfo($previewPath, PATHINFO_EXTENSION)) === 'pdf'
    ) {
        $previewExiste = true;
        $previewLink = $base . '/reportes/' . rawurlencode($preview);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>InMemoryIAM | Reportes Generados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    :root {
      --uaslp-azul: #002D5E;
      --uaslp-azul-light: #004C97;
      --uaslp-dorado: #B38E5D;
      --gris-fondo: #F4F6F9;
      --gris-borde: #E5E7EB;
      --texto-principal: #1F2937;
      --texto-gris: #6B7280;
      --blanco: #FFFFFF;
      --rojo-pdf: #DC2626;
      --verde: #15803D;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      font-family: Arial, Helvetica, sans-serif;
      background: var(--gris-fondo);
      color: var(--texto-principal);
      display: flex;
      flex-direction: column;
    }

    .main-header {
     background: linear-gradient(135deg, var(--uaslp-azul), #004C97);
     border-bottom: none;
     padding: 20px 24px;
     color: white;
    }

    .header-content {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .logo-container {
      width: 70px;
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-container img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .system-title {
    font-size: 28px;
    color: white;
    font-weight: 800;
    letter-spacing: -0.5px;
    }

    .system-subtitle {
    font-size: 14px;
    color: #dbeafe;
    margin-top: 4px;
    }

    .header-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .btn-top {
      background: var(--uaslp-azul);
      color: white;
      text-decoration: none;
      padding: 11px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-top.secondary {
    background: rgba(255, 255, 255, 0.12);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .btn-top.secondary:hover {
    background: rgba(255, 255, 255, 0.22);
    }

    .golden-bar {
      height: 6px;
      background: var(--uaslp-dorado);
    }

    main {
      width: 100%;
      max-width: 1280px;
      margin: 0 auto;
      padding: 32px 24px 50px;
      flex: 1;
    }

    .page-title {
      margin-bottom: 24px;
    }

    .page-title h2 {
      font-size: 30px;
      color: var(--uaslp-azul);
      margin-bottom: 8px;
    }

    .page-title p {
      color: var(--texto-gris);
      line-height: 1.6;
      max-width: 780px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: var(--blanco);
      border: 1px solid var(--gris-borde);
      border-radius: 16px;
      padding: 22px;
      display: flex;
      align-items: center;
      gap: 18px;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #EFF6FF;
    }

    .stat-icon svg {
      width: 30px;
      height: 30px;
    }

    .icon-blue {
      color: var(--uaslp-azul-light);
    }

    .icon-gold {
      color: var(--uaslp-dorado);
    }

    .icon-red {
      color: var(--rojo-pdf);
    }

    .stat-info h3 {
      font-size: 26px;
      color: var(--uaslp-azul);
      margin-bottom: 4px;
    }

    .stat-info p {
      color: var(--texto-gris);
      font-size: 14px;
    }

    .toolbar {
      background: var(--blanco);
      border: 1px solid var(--gris-borde);
      border-radius: 16px;
      padding: 16px;
      margin-bottom: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .toolbar-title {
      color: var(--uaslp-azul);
      font-weight: 800;
    }

    .search-form {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .search-form input {
      min-width: 280px;
      border: 1px solid var(--gris-borde);
      border-radius: 8px;
      padding: 11px 12px;
      outline: none;
      font-size: 14px;
    }

    .search-form input:focus {
      border-color: var(--uaslp-azul-light);
    }

    .btn-search,
    .btn-clear {
      border: none;
      border-radius: 8px;
      padding: 11px 14px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      font-size: 13px;
    }

    .btn-search {
      background: var(--uaslp-azul);
      color: white;
    }

    .btn-clear {
      background: #E5E7EB;
      color: var(--texto-principal);
    }

    .layout {
      display: grid;
      grid-template-columns: 1fr 1.05fr;
      gap: 24px;
      align-items: start;
    }

    .card {
      background: var(--blanco);
      border: 1px solid var(--gris-borde);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .card-header {
      padding: 18px 22px;
      border-bottom: 1px solid var(--gris-borde);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
    }

    .card-header h2 {
      font-size: 18px;
      color: var(--uaslp-azul);
      font-weight: 800;
    }

    .table-container {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      padding: 14px 18px;
      text-align: left;
      font-size: 11px;
      text-transform: uppercase;
      color: var(--texto-gris);
      background: #F1F5F9;
      letter-spacing: 0.8px;
    }

    td {
      padding: 16px 18px;
      border-bottom: 1px solid var(--gris-borde);
      font-size: 14px;
      vertical-align: middle;
    }

    tbody tr:hover {
      background: #F9FAFB;
    }

    .file-name {
      font-weight: 700;
      color: var(--uaslp-azul);
      word-break: break-word;
    }

    .file-icon-tag {
      background: #FEE2E2;
      color: var(--rojo-pdf);
      padding: 4px 8px;
      border-radius: 5px;
      font-size: 10px;
      font-weight: 800;
      margin-right: 10px;
      display: inline-block;
    }

    .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .btn-action {
      border: none;
      border-radius: 7px;
      padding: 9px 11px;
      font-size: 12px;
      font-weight: 800;
      text-decoration: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-preview {
      background: #DBEAFE;
      color: var(--uaslp-azul);
    }

    .btn-download {
      background: var(--uaslp-azul);
      color: white;
    }

    .btn-delete {
      background: #FEE2E2;
      color: #B91C1C;
    }

    .preview-card {
      position: sticky;
      top: 18px;
    }

    .preview-empty {
      min-height: 480px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 36px;
      color: var(--texto-gris);
      background: #F8FAFC;
    }

    .preview-empty strong {
      display: block;
      color: var(--uaslp-azul);
      font-size: 20px;
      margin-bottom: 8px;
    }

    iframe {
      width: 100%;
      height: 680px;
      border: none;
      background: #F8FAFC;
    }

    .empty-list {
      padding: 60px;
      text-align: center;
      color: var(--texto-gris);
    }

    footer {
      background: var(--uaslp-azul);
      color: var(--blanco);
      padding: 34px 24px;
      border-top: 5px solid var(--uaslp-dorado);
    }

    .footer-container {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      gap: 20px;
      align-items: center;
      opacity: 0.95;
      font-size: 14px;
      line-height: 1.6;
    }

    .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.62);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    z-index: 9999;
    }

    .modal-overlay.show {
    display: flex;
    }

    .modal-box {
    width: 100%;
    max-width: 430px;
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 25px 70px rgba(15, 23, 42, 0.35);
    text-align: center;
    animation: modalIn 0.18s ease-out;
    }

    @keyframes modalIn {
    from {
        transform: translateY(12px) scale(0.98);
        opacity: 0;
    }

    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    }

    .modal-icon {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #fee2e2;
    color: #b91c1c;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 28px;
    font-weight: 900;
    }

    .modal-box h2 {
    color: var(--uaslp-azul);
    font-size: 24px;
    margin-bottom: 10px;
    }

    .modal-box p {
    color: var(--texto-gris);
    line-height: 1.6;
    margin-bottom: 16px;
    }

    .modal-file {
    background: #f8fafc;
    border: 1px solid var(--gris-borde);
    color: var(--texto-principal);
    border-radius: 12px;
    padding: 12px;
    font-size: 14px;
    font-weight: 700;
    word-break: break-word;
    margin-bottom: 22px;
    }

    .modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    }

    .btn-modal-cancel,
    .btn-modal-delete {
    border: none;
    border-radius: 10px;
    padding: 12px 18px;
    font-weight: 800;
    cursor: pointer;
    font-size: 14px;
    }

    .btn-modal-cancel {
    background: #e5e7eb;
    color: #374151;
    }

    .btn-modal-cancel:hover {
    background: #d1d5db;
    }

    .btn-modal-delete {
    background: #dc2626;
    color: white;
    }

    .btn-modal-delete:hover {
    background: #b91c1c;
    }

    @media (max-width: 1100px) {
      .layout {
        grid-template-columns: 1fr;
      }

      .preview-card {
        position: static;
      }

      iframe {
        height: 520px;
      }
    }

    @media (max-width: 768px) {
      .header-content,
      .footer-container,
      .toolbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .search-form,
      .search-form input {
        width: 100%;
        min-width: auto;
      }

      .system-title {
        font-size: 24px;
      }
    }
  </style>
</head>

<body>
  <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
            <div class="logo-container">
                <img src="img/uni.png" alt="UASLP">
            </div>

            <div class="header-text">
                <h1 class="system-title">InMemoryIAM</h1>
                <div class="system-subtitle">Universidad Autónoma de San Luis Potosí</div>
            </div>
            </div>

            <div class="header-actions">
            <a class="btn-top secondary" href="<?= $base ?>/inmemoryiam_reportes.php">
                Volver al inicio
            </a>
            </div>
        </div>
    </header>

  <div class="golden-bar"></div>

  <main>
    <section class="page-title">
      <h2>Gestión de reportes generados</h2>
      <p>
        Consulta, busca, visualiza, descarga o elimina los documentos PDF generados por el
        microservicio de reportes de InMemoryIAM.
      </p>
    </section>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
        </div>
        <div class="stat-info">
          <h3><?= count($files) ?></h3>
          <p>Reportes Generados</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3m18 0c0-1.66-4-3-9-3s-9 1.34-9 3m18 0v6c0 1.66-4 3-9 3s-9-1.34-9-3v-6"></path>
          </svg>
        </div>
        <div class="stat-info">
          <h3><?= formatBytes($totalSize) ?></h3>
          <p>Tamaño en Disco</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <svg class="icon-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="stat-info">
          <h3>PDF</h3>
          <p>Formato de Salida</p>
        </div>
      </div>
    </div>

    <section class="toolbar">
      <div class="toolbar-title">Listado de documentos disponibles</div>

      <form class="search-form" method="GET" action="ver_reportes.php">
        <input
          type="text"
          name="q"
          placeholder="Buscar por nombre del archivo..."
          value="<?= htmlspecialchars($busqueda) ?>"
        >

        <button class="btn-search" type="submit">Buscar</button>

        <?php if ($busqueda !== ''): ?>
          <a class="btn-clear" href="ver_reportes.php">Limpiar</a>
        <?php endif; ?>
      </form>
    </section>

    <div class="layout">
      <section class="card">
        <div class="card-header">
          <h2>Documentos disponibles</h2>
          <span style="color: var(--texto-gris); font-size: 13px;">
            <?= count($files) ?> resultado(s)
          </span>
        </div>

        <?php if (empty($files)): ?>
          <div class="empty-list">
            <p>No hay archivos disponibles en el repositorio.</p>
          </div>
        <?php else: ?>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Nombre del Archivo</th>
                  <th>Fecha de Emisión</th>
                  <th>Tamaño</th>
                  <th>Acciones</th>
                </tr>
              </thead>

              <tbody>
                <?php foreach ($files as $file): ?>
                  <tr>
                    <td>
                      <span class="file-icon-tag">PDF</span>
                      <span class="file-name"><?= htmlspecialchars($file['name']) ?></span>
                    </td>

                    <td style="color: var(--texto-gris);">
                      <?= date('d/m/Y H:i', $file['mtime']) ?>
                    </td>

                    <td style="color: var(--texto-gris);">
                      <?= formatBytes($file['size']) ?>
                    </td>

                    <td>
                      <div class="actions">
                        <a
                          class="btn-action btn-preview"
                          href="ver_reportes.php?<?= http_build_query([
                            'q' => $busqueda,
                            'preview' => $file['name']
                          ]) ?>"
                        >
                          Vista previa
                        </a>

                        <a
                          class="btn-action btn-download"
                          href="<?= htmlspecialchars($file['link']) ?>"
                          download
                        >
                          Descargar
                        </a>

                        <button
                            class="btn-action btn-delete"
                            type="button"
                            onclick="abrirModalEliminar('<?= htmlspecialchars($file['name'], ENT_QUOTES) ?>')"
                            >
                            Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <aside class="card preview-card">
        <div class="card-header">
          <h2>Vista previa del reporte</h2>
          <?php if ($previewExiste): ?>
            <span style="color: var(--texto-gris); font-size: 13px; word-break: break-word;">
              <?= htmlspecialchars($preview) ?>
            </span>
          <?php endif; ?>
        </div>

        <?php if ($previewExiste): ?>
          <iframe src="<?= htmlspecialchars($previewLink) ?>"></iframe>
        <?php else: ?>
          <div class="preview-empty">
            <div>
              <strong>Sin reporte seleccionado</strong>
              <p>Haz clic en “Vista previa” para mostrar el PDF antes de descargarlo.</p>
            </div>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </main>

  <footer>
    <div class="footer-container">
      <div>
        <p><strong>UNIVERSIDAD AUTÓNOMA DE SAN LUIS POTOSÍ</strong></p>
        <p>Sistema InMemoryIAM - Gestión Documental</p>
      </div>

      <div style="text-align: right;">
        <p>&copy; <?= date('Y') ?> UASLP</p>
        <p>San Luis Potosí, México</p>
      </div>
    </div>
  </footer>
  <div id="modalEliminar" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon">
        !
        </div>

        <h2>Eliminar reporte</h2>

        <p>
        ¿Seguro que deseas eliminar este reporte? Esta acción no se puede deshacer.
        </p>

        <div class="modal-file" id="nombreReporteEliminar">
        reporte.pdf
        </div>

        <form method="POST" action="eliminar_reporte.php">
        <input type="hidden" name="archivo" id="archivoEliminar">

        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="cerrarModalEliminar()">
            Cancelar
            </button>

            <button type="submit" class="btn-modal-delete">
            Sí, eliminar
            </button>
        </div>
        </form>
    </div>
    </div>

    <script>
    function abrirModalEliminar(nombreArchivo) {
        document.getElementById('archivoEliminar').value = nombreArchivo;
        document.getElementById('nombreReporteEliminar').textContent = nombreArchivo;
        document.getElementById('modalEliminar').classList.add('show');
    }

    function cerrarModalEliminar() {
        document.getElementById('modalEliminar').classList.remove('show');
        document.getElementById('archivoEliminar').value = '';
        document.getElementById('nombreReporteEliminar').textContent = '';
    }

    document.getElementById('modalEliminar').addEventListener('click', function(event) {
        if (event.target === this) {
        cerrarModalEliminar();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
        cerrarModalEliminar();
        }
    });
    </script>
</body>
</html>