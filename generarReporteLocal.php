<?php
// Cargar el autoloader de Composer
require 'vendor/autoload.php';

// Usar solo la clase de Dompdf
use Dompdf\Dompdf;

/**
 * Función para generar el HTML del reporte de Interacciones
 *
 * @param array $data Los datos decodificados de JSON.
 * @return string El HTML formateado.
 */
function generarHtmlReporte(array $data): string
{
    // Estilos CSS
    $html = "<html><head><style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #f4f4f4; text-align: left; }
        .info-header { font-size: 14px; margin-bottom: 15px; }
    </style></head><body>";

    // --- Sección 1: Cabecera del Reporte ---
    $info = $data['reporte_info'];
    $html .= "<h1>" . htmlspecialchars($info['titulo']) . "</h1>";
    $html .= "<div class='info-header'>";
    $html .= "<p><strong>Mes:</strong> " . htmlspecialchars($info['mes']) . " " . htmlspecialchars($info['ano']) . "</p>";
    $html .= "<p><strong>Total de Interacciones:</strong> " . htmlspecialchars($info['total_interacciones']) . "</p>";
    $html .= "</div>";


    // --- Sección 2: Tabla de Interacciones Destacadas ---
    $html .= "<h2>Interacciones Destacadas</h2>";
    $html .= "<table>";
    $html .= "<tr>
                <th>ID Inter.</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Personalidad</th>
                <th>Estilo</th>
                <th>ID Sesión</th>
              </tr>";

    foreach ($data['interacciones_destacadas'] as $interaccion) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($interaccion['id_interaccion']) . "</td>";
        $html .= "<td>" . htmlspecialchars($interaccion['fecha']) . "</td>";
        $html .= "<td>" . htmlspecialchars($interaccion['usuario']) . "</td>";
        $html .= "<td>" . htmlspecialchars($interaccion['personalidad']) . "</td>";
        $html .= "<td>" . htmlspecialchars($interaccion['estilo']) . "</td>";
        $html .= "<td>" . htmlspecialchars($interaccion['id_sesion']) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";


    // --- Sección 3: Tabla de Estadísticas por Personalidad ---
    $html .= "<h2>Estadísticas por Personalidad</h2>";
    $html .= "<table>";
    $html .= "<tr>
                <th>Personalidad</th>
                <th>Total Usos (Mes)</th>
                <th>Calificación Promedio</th>
              </tr>";

    foreach ($data['estadisticas_personalidad'] as $stat) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($stat['personalidad']) . "</td>";
        $html .= "<td>" . htmlspecialchars($stat['usos']) . "</td>";
        $html .= "<td>" . number_format($stat['calificacion_promedio'], 2) . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";

    $html .= "</body></html>";

    return $html;
}

/**
 * Función para crear el PDF y guardarlo localmente.
 *
 * @param string $jsonData El string JSON de la base de datos.
 * @param string $directorioSalida El directorio donde se guardará el PDF.
 * @param string $nombreBase El nombre base para el archivo (ej. "reporte_interacciones")
 * @return string La ruta completa del archivo PDF guardado.
 */
function generarReporteLocal(string $jsonData, string $directorioSalida, string $nombreBase): string
{
    // --- 1. Decodificar JSON y Generar HTML ---
    
    $datos = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar el JSON: " . json_last_error_msg());
    }

    $html = generarHtmlReporte($datos);

    // --- 2. Generar el PDF con Dompdf ---

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    $pdfOutput = $dompdf->output();
    
    // --- 3. Guardar el archivo localmente ---
    
    if (!is_dir($directorioSalida)) {
        // Intenta crear el directorio si no existe
        if (!mkdir($directorioSalida, 0777, true) && !is_dir($directorioSalida)) {
            throw new Exception("No se pudo crear el directorio de salida: " . $directorioSalida);
        }
    }

    // Definir el nombre y la ruta del archivo
    $nombreArchivo = $nombreBase . "_" . date("Y-m-d_His") . ".pdf";
    $rutaCompleta = $directorioSalida . DIRECTORY_SEPARATOR . $nombreArchivo;

    if (file_put_contents($rutaCompleta, $pdfOutput) === false) {
        throw new Exception("No se pudo escribir el archivo PDF en el disco. Verifica los permisos de la carpeta.");
    }

    return $rutaCompleta;
}

// Nota: No hay más código después de esto.