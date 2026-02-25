<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generarHtmlReporte(array $data): string
{
    $info = $data['reporte_info'];
    $folio = strtoupper(uniqid("REP-"));

    $html = "
    <html>
    <head>
    <meta charset='UTF-8'>
    <style>

    @page {
        margin: 100px 40px 80px 40px;
    }

    body { 
        font-family: 'Times New Roman', serif; 
        font-size: 12px;
        color: #2c2c2c;
    }

    header {
        position: fixed;
        top: -80px;
        left: 0;
        right: 0;
        height: 70px;
    }

    .header-table {
        width: 100%;
    }

    .logo {
        width: 80px;
    }

    .title-block {
        text-align: left;
    }

    h1 {
        text-align: center;
        font-size: 18px;
        margin-bottom: 10px;
    }

    h2 {
        font-size: 14px;
        color: #0B3D91;
        margin-top: 25px;
        border-bottom: 1px solid #0B3D91;
        padding-bottom: 4px;
    }

    .info-box {
        background-color: #f2f4f8;
        padding: 10px;
        border: 1px solid #ccc;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }

    th {
        background-color: #0B3D91;
        color: white;
        padding: 6px;
        font-weight: bold;
    }

    th, td {
        border: 1px solid #999;
        padding: 6px;
        text-align: center;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    footer {
        position: fixed;
        bottom: -60px;
        left: 0;
        right: 0;
        height: 50px;
        font-size: 10px;
        text-align: center;
        color: #555;
    }

    </style>
    </head>
    <body>

    <header>
        <table class='header-table'>
            <tr>
                <td width='90'>
                    <img src='" . __DIR__ . "/img/EMBLEMA-AZUL-V.png' class='logo'>
                </td>
                <td class='title-block'>
                    <strong>Universidad Autónoma</strong><br>
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
        Generado el: " . date('d/m/Y H:i') . " | Sistema InMemoryIAM
    </footer>

    <main>

    <h1>" . htmlspecialchars($info['titulo']) . "</h1>

    <div class='info-box'>
        <strong>Mes:</strong> " . htmlspecialchars($info['mes']) . " " . htmlspecialchars($info['ano']) . "<br>
        <strong>Total de Interacciones:</strong> " . htmlspecialchars($info['total_interacciones']) . "
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
    $canvas->page_text(500, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0,0,0));

    $pdfOutput = $dompdf->output();

    if (!is_dir($directorioSalida)) {
        mkdir($directorioSalida, 0777, true);
    }

    $nombreArchivo = $nombreBase . "_" . date("Y-m-d_His") . ".pdf";
    $rutaCompleta = $directorioSalida . DIRECTORY_SEPARATOR . $nombreArchivo;

    file_put_contents($rutaCompleta, $pdfOutput);

    return $rutaCompleta;
}