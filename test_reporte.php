<?php
require 'generarReporteLocal.php';


$datosPrueba = [
    "reporte_info" => [
        "titulo" => "Reporte Mensual de Interacciones",
        "mes" => "Febrero",
        "ano" => "2026",
        "total_interacciones" => 125
    ],
    "interacciones_destacadas" => [
        [
            "id_interaccion" => 1,
            "fecha" => "2026-02-01",
            "usuario" => "raul.garcia",
            "personalidad" => "Asistente Académico",
            "estilo" => "Formal",
            "id_sesion" => "SES-001"
        ],
        [
            "id_interaccion" => 2,
            "fecha" => "2026-02-05",
            "usuario" => "maria.lopez",
            "personalidad" => "Tutor IA",
            "estilo" => "Didáctico",
            "id_sesion" => "SES-002"
        ]
    ],
    "estadisticas_personalidad" => [
        [
            "personalidad" => "Asistente Académico",
            "usos" => 80,
            "calificacion_promedio" => 4.7
        ],
        [
            "personalidad" => "Tutor IA",
            "usos" => 45,
            "calificacion_promedio" => 4.5
        ]
    ]
];

$json = json_encode($datosPrueba);

$ruta = generarReporteLocal(
    $json,
    __DIR__ . "/reportes_prueba",
    "reporte_test"
);

echo "Reporte generado correctamente:<br>";
echo $ruta;