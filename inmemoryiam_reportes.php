<?php
function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

    return $scheme . '://' . $host . $scriptDir;
}

$base = rtrim(base_url(), '/\\');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>InMemoryIAM | Generador de Reportes</title>
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
      --cyan: #38BDF8;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, Helvetica, sans-serif;
      background: var(--gris-fondo);
      color: var(--texto-principal);
    }

    .hero {
      min-height: 100vh;
      background:
        radial-gradient(circle at top right, rgba(56, 189, 248, 0.25), transparent 35%),
        linear-gradient(135deg, #001B3F, var(--uaslp-azul), var(--uaslp-azul-light));
      color: white;
      padding: 24px 8% 70px;
    }

    .navbar {
      max-width: 1280px;
      margin: 0 auto 70px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .brand-logo {
      width: 62px;
      height: 62px;
      background: white;
      border-radius: 16px;
      padding: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    .brand-logo img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .brand-text h1 {
      font-size: 24px;
      line-height: 1;
    }

    .brand-text span {
      color: #DBEAFE;
      font-size: 13px;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 22px;
      flex-wrap: wrap;
    }

    .nav-links a {
      color: #E0F2FE;
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
    }

    .nav-links a:hover {
      color: white;
    }

    .btn-nav {
      background: var(--uaslp-dorado);
      color: white !important;
      padding: 11px 16px;
      border-radius: 10px;
    }

    .hero-content {
      max-width: 1280px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      gap: 50px;
      align-items: center;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.22);
      padding: 8px 13px;
      border-radius: 999px;
      color: #E0F2FE;
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 22px;
    }

    .hero-text h2 {
      font-size: 54px;
      line-height: 1.08;
      margin-bottom: 22px;
      letter-spacing: -1px;
    }

    .hero-text h2 span {
      color: var(--cyan);
    }

    .hero-text p {
      color: #DBEAFE;
      font-size: 18px;
      line-height: 1.75;
      max-width: 680px;
      margin-bottom: 34px;
    }

    .actions {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    .btn {
      padding: 14px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: 0.2s ease;
    }

    .btn-primary {
      background: var(--uaslp-dorado);
      color: white;
      box-shadow: 0 12px 30px rgba(179, 142, 93, 0.35);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      background: #9f7d4f;
    }

    .btn-secondary {
      background: rgba(255,255,255,0.1);
      color: white;
      border: 1px solid rgba(255,255,255,0.3);
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.18);
      transform: translateY(-2px);
    }

    .dashboard-preview {
      background: rgba(255,255,255,0.95);
      color: var(--texto-principal);
      border-radius: 26px;
      padding: 22px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
      border: 1px solid rgba(255,255,255,0.45);
    }

    .preview-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--gris-borde);
      padding-bottom: 16px;
      margin-bottom: 18px;
    }

    .preview-header strong {
      color: var(--uaslp-azul);
      font-size: 18px;
    }

    .preview-pill {
      background: #DBEAFE;
      color: var(--uaslp-azul);
      padding: 7px 11px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
    }

    .mockup-grid {
      display: grid;
      grid-template-columns: 0.9fr 1.1fr;
      gap: 16px;
    }

    .json-card,
    .pdf-card,
    .stat-card {
      border: 1px solid var(--gris-borde);
      border-radius: 16px;
      padding: 16px;
      background: white;
    }

    .json-card {
      background: #0F172A;
      color: #D1FAE5;
      font-family: Consolas, monospace;
      font-size: 12px;
      line-height: 1.65;
    }

    .json-card span {
      color: #38BDF8;
    }

    .pdf-card {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .pdf-line {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px;
      background: #F8FAFC;
      border-radius: 12px;
      border: 1px solid #E2E8F0;
    }

    .pdf-icon {
      width: 42px;
      height: 50px;
      background: #FEE2E2;
      color: #DC2626;
      border-radius: 9px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 12px;
      font-weight: 900;
    }

    .pdf-info strong {
      display: block;
      color: var(--uaslp-azul);
      font-size: 14px;
      margin-bottom: 3px;
    }

    .pdf-info small {
      color: var(--texto-gris);
    }

    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 16px;
    }

    .stat-card strong {
      color: var(--uaslp-azul);
      display: block;
      font-size: 22px;
      margin-bottom: 4px;
    }

    .stat-card span {
      color: var(--texto-gris);
      font-size: 12px;
    }

    .section {
      padding: 70px 8%;
    }

    .section-inner {
      max-width: 1280px;
      margin: 0 auto;
    }

    .section-title {
      text-align: center;
      margin-bottom: 42px;
    }

    .section-title h2 {
      color: var(--uaslp-azul);
      font-size: 36px;
      margin-bottom: 12px;
    }

    .section-title p {
      color: var(--texto-gris);
      line-height: 1.7;
      max-width: 760px;
      margin: 0 auto;
    }

    .features {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .feature-card {
      background: white;
      border: 1px solid var(--gris-borde);
      border-radius: 22px;
      padding: 28px;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    .feature-icon {
      width: 58px;
      height: 58px;
      border-radius: 16px;
      background: #EFF6FF;
      color: var(--uaslp-azul);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 18px;
    }

    .feature-card h3 {
      color: var(--uaslp-azul);
      margin-bottom: 10px;
    }

    .feature-card p {
      color: var(--texto-gris);
      line-height: 1.65;
      font-size: 15px;
    }

    .process {
      background: white;
    }

    .steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .step {
      background: #F8FAFC;
      border: 1px solid var(--gris-borde);
      border-radius: 22px;
      padding: 28px;
      text-align: center;
    }

    .step-number {
      width: 48px;
      height: 48px;
      margin: 0 auto 18px;
      border-radius: 50%;
      background: var(--uaslp-azul);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
    }

    .step h3 {
      color: var(--uaslp-azul);
      margin-bottom: 10px;
    }

    .step p {
      color: var(--texto-gris);
      line-height: 1.65;
      font-size: 15px;
    }

    .cta {
      background: linear-gradient(135deg, var(--uaslp-azul), var(--uaslp-azul-light));
      color: white;
      text-align: center;
      padding: 60px 8%;
    }

    .cta h2 {
      font-size: 34px;
      margin-bottom: 12px;
    }

    .cta p {
      color: #DBEAFE;
      max-width: 680px;
      margin: 0 auto 28px;
      line-height: 1.7;
    }

    footer {
      background: #001B3F;
      color: #DBEAFE;
      border-top: 6px solid var(--uaslp-dorado);
      padding: 32px 8%;
    }

    .footer-inner {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      font-size: 14px;
      line-height: 1.6;
    }

    .nav-right {
    display: flex;
    align-items: center;
    gap: 24px;
    }

    .faculty-logo {
    width: 74px;
    height: 74px;
    background: white;
    border-radius: 16px;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    .faculty-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    }

    @media (max-width: 1000px) {
      .hero-content,
      .mockup-grid,
      .features,
      .steps {
        grid-template-columns: 1fr;
      }

      .hero-text h2 {
        font-size: 40px;
      }

      .navbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-row {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 650px) {
      .hero,
      .section,
      .cta,
      footer {
        padding-left: 5%;
        padding-right: 5%;
      }

      .brand {
        align-items: flex-start;
      }

      .brand-logo {
        width: 54px;
        height: 54px;
      }

      .hero-text h2 {
        font-size: 34px;
      }

      .nav-links {
        gap: 12px;
      }
    }
  </style>
</head>

<body>

<section class="hero" id="inicio">
  <nav class="navbar">
        <div class="brand">
            <div class="brand-logo">
            <img src="img/EMBLEMA-AZUL-V.png" alt="UASLP">
            </div>

            <div class="brand-text">
            <h1>InMemoryIAM</h1>
            <span>Generador de Reportes</span>
            </div>
        </div>

        <div class="nav-right">
            <div class="nav-links">
            <a href="#inicio">Inicio</a>
            <a href="#funciones">Funciones</a>
            <a href="#proceso">Cómo funciona</a>
            <a class="btn-nav" href="<?= $base ?>/ver_reportes.php">Ver reportes</a>
            </div>

            <div class="faculty-logo">
            <img src="img/facultad.png" alt="Facultad de Ingeniería">
            </div>
        </div>
    </nav>

  <div class="hero-content">
    <div class="hero-text">
      <div class="badge">Módulo documental · PDF · API REST</div>

      <h2>
        Generador de reportes para <span>InMemoryIAM</span>
      </h2>

      <p>
        Este módulo permite generar reportes PDF a partir de información enviada en formato JSON.
        Los documentos se almacenan dentro del servicio y pueden consultarse, visualizarse,
        descargarse o eliminarse desde una vista administrativa.
      </p>

      <div class="actions">
        <a class="btn btn-primary" href="<?= $base ?>/ver_reportes.php">
          Ver reportes generados
        </a>

        <a class="btn btn-secondary" href="#proceso">
          Conocer funcionamiento
        </a>
      </div>
    </div>

    <div class="dashboard-preview">
      <div class="preview-header">
        <strong>Vista del módulo</strong>
        <span class="preview-pill">PDF generado</span>
      </div>

      <div class="mockup-grid">
        <div class="json-card">
          {<br>
          &nbsp;&nbsp;<span>"titulo"</span>: "Reporte VR",<br>
          &nbsp;&nbsp;<span>"usuario"</span>: "Admin",<br>
          &nbsp;&nbsp;<span>"modulo"</span>: "Museo",<br>
          &nbsp;&nbsp;<span>"formato"</span>: "PDF"<br>
          }
        </div>

        <div class="pdf-card">
          <div class="pdf-line">
            <div class="pdf-icon">PDF</div>
            <div class="pdf-info">
              <strong>Reporte Museo VR</strong>
              <small>Generado correctamente</small>
            </div>
          </div>

          <div class="pdf-line">
            <div class="pdf-icon">PDF</div>
            <div class="pdf-info">
              <strong>Resumen administrativo</strong>
              <small>Disponible para descarga</small>
            </div>
          </div>

          <div class="pdf-line">
            <div class="pdf-icon">PDF</div>
            <div class="pdf-info">
              <strong>Evidencia del sistema</strong>
              <small>Vista previa habilitada</small>
            </div>
          </div>
        </div>
      </div>

      <div class="stats-row">
        <div class="stat-card">
          <strong>API</strong>
          <span>Entrada JSON</span>
        </div>

        <div class="stat-card">
          <strong>PDF</strong>
          <span>Salida del reporte</span>
        </div>

        <div class="stat-card">
          <strong>Local</strong>
          <span>Almacenamiento</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="funciones">
  <div class="section-inner">
    <div class="section-title">
      <h2>Funciones principales</h2>
      <p>
        El generador de reportes funciona como un microservicio independiente para apoyar
        la documentación, evidencia y administración del proyecto InMemoryIAM.
      </p>
    </div>

    <div class="features">
      <div class="feature-card">
        <div class="feature-icon">📄</div>
        <h3>Generación automática de PDF</h3>
        <p>
          Recibe datos estructurados en formato JSON y los convierte en documentos PDF
          listos para consulta o descarga.
        </p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🔎</div>
        <h3>Consulta de reportes</h3>
        <p>
          Permite buscar reportes, visualizar una vista previa, descargarlos y mantener
          una gestión ordenada de los archivos generados.
        </p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">⚙️</div>
        <h3>Servicio independiente</h3>
        <p>
          El módulo trabaja separado de la aplicación principal, facilitando pruebas,
          mantenimiento e integración con otros componentes.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="section process" id="proceso">
  <div class="section-inner">
    <div class="section-title">
      <h2>¿Cómo funciona?</h2>
      <p>
        El flujo del módulo es simple: recibe información, genera el documento y lo deja
        disponible para su administración desde la vista de reportes.
      </p>
    </div>

    <div class="steps">
      <div class="step">
        <div class="step-number">1</div>
        <h3>Se envían datos JSON</h3>
        <p>
          Otro módulo o cliente realiza una petición HTTP enviando los datos necesarios
          para construir el reporte.
        </p>
      </div>

      <div class="step">
        <div class="step-number">2</div>
        <h3>Se procesa la información</h3>
        <p>
          El servicio valida la entrada, organiza el contenido y prepara la estructura
          visual del documento.
        </p>
      </div>

      <div class="step">
        <div class="step-number">3</div>
        <h3>Se genera el reporte</h3>
        <p>
          El PDF se guarda en la carpeta de reportes y queda disponible para vista previa,
          descarga o eliminación.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="cta">
  <h2>Administra los reportes generados</h2>
  <p>
    Accede a la vista de reportes para consultar los documentos disponibles, usar filtros,
    visualizar el contenido antes de descargarlo y eliminar archivos cuando sea necesario.
  </p>

  <a class="btn btn-primary" href="<?= $base ?>/ver_reportes.php">
    Ir a reportes
  </a>
</section>

<footer>
  <div class="footer-inner">
    <div>
      <strong>Universidad Autónoma de San Luis Potosí</strong>
      <p>Facultad de Ingeniería · Área de Ciencias de la Computación</p>
    </div>

    <div>
      <strong>Proyecto InMemoryIAM</strong>
      <p>Generador de reportes PDF · <?= date('Y') ?></p>
    </div>
  </div>
</footer>

</body>
</html>