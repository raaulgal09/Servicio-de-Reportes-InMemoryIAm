# Servicio de Reportes InMemoryIAM

Microservicio en PHP que recibe datos en formato JSON a través de una API REST y genera reportes en formato PDF para su descarga. No utiliza base de datos; todo el procesamiento se realiza en memoria.

## Tecnologías

- PHP 8.x con Dompdf para generación de PDFs
- Docker y Docker Compose para contenedorización
- Composer para gestión de dependencias

## Estructura del Proyecto

    ├── img/                            # Imágenes utilizadas en los reportes (logos, emblemas)
    ├── reportes/                       # Carpeta de salida de reportes generados
    ├── reportes_prueba/                # Carpeta de salida de reportes de prueba
    ├── vendor/                         # Dependencias PHP (Dompdf vía Composer)
    ├── .dockerignore
    ├── api_reporte.php                 # Endpoint: recibe JSON y genera el PDF
    ├── api_listar_reportes.php         # Endpoint: lista los reportes generados
    ├── docker-compose.yml              # Orquestación del contenedor
    ├── Dockerfile                      # Definición de la imagen Docker
    ├── generarReporteLocal.php         # Generación de reportes de forma local
    ├── inmemoryiam_reportes.php        # Lógica principal de generación de reportes
    ├── test_reporte.php                # Script de pruebas
    └── README.md

## Requisitos Previos

- Docker 20.10+ - https://docs.docker.com/get-docker/
- Docker Compose 2.0+ - https://docs.docker.com/compose/install/
- Git 2.30+ - https://git-scm.com/downloads

No es necesario tener PHP ni Composer instalados localmente; todo se ejecuta dentro del contenedor.

## Instalación y Ejecución Local

1. Clonar el repositorio

        git clone https://github.com/tu-usuario/servicio-de-reportes-inmemoryiam.git
        cd servicio-de-reportes-inmemoryiam

2. Construir y levantar el contenedor

        docker compose up -d --build

3. Verificar que está activo

        docker compose ps

El servicio estará disponible en http://localhost:PUERTO (ver puerto en docker-compose.yml).

## Uso de la API

### Generar un reporte

    curl -X POST http://localhost:PUERTO/api_reporte.php \
      -H "Content-Type: application/json" \
      -d '{ "campo1": "valor1", "campo2": "valor2" }'

### Listar reportes generados

    curl http://localhost:PUERTO/api_listar_reportes.php

## Despliegue en Servidor (Producción)

### 1. Instalar Docker en el servidor

    ssh usuario@ip-del-servidor

    sudo apt update
    sudo apt install -y ca-certificates curl gnupg
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg

    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
      https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
      sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    sudo usermod -aG docker $USER

### 2. Clonar y levantar el proyecto

    cd /opt
    sudo git clone https://github.com/tu-usuario/servicio-de-reportes-inmemoryiam.git
    cd servicio-de-reportes-inmemoryiam
    docker compose up -d --build

### 3. Verificar el despliegue

    docker compose ps
    docker compose logs -f
    curl http://localhost:PUERTO/api_listar_reportes.php

## Actualización en Producción

    cd /opt/servicio-de-reportes-inmemoryiam
    git pull origin main
    docker compose up -d --build

## Comandos Útiles

- Levantar el servicio: `docker compose up -d`
- Detener el servicio: `docker compose down`
- Ver logs en tiempo real: `docker compose logs -f`
- Acceder al contenedor: `docker compose exec app bash`
- Reconstruir después de cambios: `docker compose up -d --build`
- Ver estado del contenedor: `docker compose ps`

## Notas

- Sin base de datos. Los PDFs se generan en memoria a partir del JSON recibido.
- Los reportes generados se almacenan en la carpeta reportes/.
- Las imágenes en img/ (logos, emblemas) se incrustan en los PDFs. Para cambiar el branding, reemplazar los archivos en esa carpeta.
- Dependencias en vendor/ se reinstalan con: `docker compose exec app composer install`

## Solución de Problemas

- El contenedor no inicia: revisar logs con `docker compose logs -f`
- Error de permisos en reportes: ejecutar `docker compose exec app chmod -R 775 /var/www/html/reportes`
- PDF se genera vacío: verificar formato del JSON enviado
- Puerto ya en uso: cambiar puerto en docker-compose.yml o liberar el existente
- Imágenes no aparecen en el PDF: verificar que existen en img/
