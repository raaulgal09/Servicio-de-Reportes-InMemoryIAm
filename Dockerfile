FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

RUN mkdir -p /var/www/html/reportes /var/www/html/reportes_prueba \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/reportes /var/www/html/reportes_prueba