<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generarHtmlReporte(array $data): string
{
    $info = $data['reporte_info'];
    $folio = strtoupper(uniqid("REP-"));

    $logoRealPath = realpath(__DIR__ . '/img/uaslp-logo.png');

    if (!$logoRealPath || !is_file($logoRealPath)) {
        throw new Exception('No se encontró el logo en img/uaslp-logo.png');
    }

    $logoData = base64_encode(file_get_contents($logoRealPath));
    $logoPath = 'data:image/png;base64,' . $logoData;

    $html = "
    <html>
    <head>
    <meta charset='UTF-8'>
    <style>

    @page {
        margin: 120px 40px 100px 40px;
    }

    body {
        font-family: 'Times New Roman', serif;
        font-size: 12px;
        color: #2c2c2c;
    }

    header {
        position: fixed;
        top: -100px;
        left: 0;
        right: 0;
        height: 90px;
    }

    .top-bar {
        background-color: #0B3D91;
        height: 8px;
        width: 100%;
    }

    .header-table {
        width: 100%;
        margin-top: 5px;
    }

    .logo {
        width: 70px;
        height: auto;
    }

    .title-block {
        font-size: 12px;
    }

    .watermark {
        position: fixed;
        top: 45%;
        left: 20%;
        opacity: 0.05;
        font-size: 80px;
        transform: rotate(-30deg);
        color: #000;
    }

    h1 {
        text-align: center;
        font-size: 22px;
        color: #0B3D91;
        margin-top: 30px;
    }

    h2 {
        font-size: 15px;
        color: #0B3D91;
        margin-top: 35px;
        border-bottom: 2px solid #0B3D91;
        padding-bottom: 5px;
    }

    .cover-box {
        text-align: center;
        margin-top: 40px;
        margin-bottom: 30px;
    }

    .cover-box p {
        margin: 5px 0;
    }

    .info-box {
        background-color: #f2f4f8;
        padding: 12px;
        border-left: 5px solid #0B3D91;
        margin-bottom: 25px;
    }

    .kpi-container {
        width: 100%;
        margin-bottom: 25px;
    }

    .kpi-box {
        width: 48%;
        display: inline-block;
        text-align: center;
        padding: 15px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }

    .kpi-value {
        font-size: 22px;
        font-weight: bold;
        color: #0B3D91;
    }

    .kpi-label {
        font-size: 11px;
        text-transform: uppercase;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        border-radius: 6px;
        overflow: hidden;
    }

    th {
        background-color: #123B7A;
        color: white;
        padding: 7px;
        font-size: 11px;
        text-transform: uppercase;
    }

    td {
        border: 1px solid #ccc;
        padding: 6px;
        text-align: center;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    footer {
        position: fixed;
        bottom: -70px;
        left: 0;
        right: 0;
        height: 60px;
        font-size: 10px;
        text-align: center;
        color: #555;
    }

    </style>
    </head>
    <body>

    <div class='watermark'>INMEMORYIAM</div>

    <header>
        <div class='top-bar'></div>
        <table class='header-table'>
            <tr>
                <td width='80'>
                    <img src='$logoPath' class='logo' alt='Logo UASLP'>
                </td>
                <td class='title-block'>
                    <strong>Universidad Autónoma de San Luis Potosi</strong><br>
                    Sistema InMemoryIAM<br>
                    Reporte Oficial de Interacciones<br>
                    <strong>Folio:</strong> $folio
                </td>
            </tr>
        </table>
        <hr>
    </header>

    <footer>
        <hr>
        Documento confidencial – Uso interno<br>
        Universidad Autónoma | Sistema InMemoryIAM<br>
        Generado el: " . date('d/m/Y H:i') . "
    </footer>

    <main>

    <div class='cover-box'>
        <h1>" . htmlspecialchars($info['titulo']) . "</h1>
        <p><strong>Periodo:</strong> " . htmlspecialchars($info['mes']) . " " . htmlspecialchars($info['ano']) . "</p>
        <p><strong>Total de Interacciones:</strong> " . htmlspecialchars($info['total_interacciones']) . "</p>
    </div>

    <h2>Resumen Ejecutivo</h2>
    <div class='info-box'>
        Durante el periodo reportado se registraron 
        <strong>" . htmlspecialchars($info['total_interacciones']) . "</strong> interacciones 
        dentro del sistema InMemoryIAM.
    </div>

    <h2>Indicadores Clave</h2>
    <div class='kpi-container'>
        <div class='kpi-box'>
            <div class='kpi-value'>" . htmlspecialchars($info['total_interacciones']) . "</div>
            <div class='kpi-label'>Total Interacciones</div>
        </div>
        <div class='kpi-box'>
            <div class='kpi-value'>" . count($data['estadisticas_personalidad']) . "</div>
            <div class='kpi-label'>Personalidades Activas</div>
        </div>
    </div>

    <h2>Interacciones Destacadas</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Personalidad</th>
            <th>Estilo</th>
            <th>ID Sesión</th>
        </tr>";

    foreach ($data['interacciones_destacadas'] as $interaccion) {
        $html .= "
        <tr>
            <td>" . htmlspecialchars($interaccion['id_interaccion']) . "</td>
            <td>" . htmlspecialchars($interaccion['fecha']) . "</td>
            <td>" . htmlspecialchars($interaccion['usuario']) . "</td>
            <td>" . htmlspecialchars($interaccion['personalidad']) . "</td>
            <td>" . htmlspecialchars($interaccion['estilo']) . "</td>
            <td>" . htmlspecialchars($interaccion['id_sesion']) . "</td>
        </tr>";
    }

    $html .= "
    </table>

    <h2>Estadísticas por Personalidad</h2>
    <table>
        <tr>
            <th>Personalidad</th>
            <th>Total Usos</th>
            <th>Calificación Promedio</th>
        </tr>";

    foreach ($data['estadisticas_personalidad'] as $stat) {
        $html .= "
        <tr>
            <td>" . htmlspecialchars($stat['personalidad']) . "</td>
            <td>" . htmlspecialchars($stat['usos']) . "</td>
            <td>" . number_format($stat['calificacion_promedio'], 2) . "</td>
        </tr>";
    }

    $html .= "
    </table>

    </main>
    </body>
    </html>";

    return $html;
}

function generarReporteLocal(string $jsonData, string $directorioSalida, string $nombreBase): string
{
    $datos = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    $html = generarHtmlReporte($datos);

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Times-Roman');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $canvas->page_text(480, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0,0,0));

    $pdfOutput = $dompdf->output();

    if (!is_dir($directorioSalida)) {
        mkdir($directorioSalida, 0777, true);
    }

    $nombreArchivo = $nombreBase . "_" . date("Y-m-d_His") . ".pdf";
    $rutaCompleta = $directorioSalida . DIRECTORY_SEPARATOR . $nombreArchivo;

    file_put_contents($rutaCompleta, $pdfOutput);

    return $rutaCompleta;
}

