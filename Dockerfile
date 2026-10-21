# Dockerfile ejemplo para PHP-FPM (Laravel dev)

FROM php:8.2-fpm

# Herramientas del sistema y librerías para las extensiones PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libmagickwand-dev \
    ghostscript \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql gd zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Instalar Composer (gestor de dependencias de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuracion de rendimiento para desarrollo (OPcache sin validacion de timestamps)
COPY docker/php/zz-performance.ini /usr/local/etc/php/conf.d/zz-performance.ini

WORKDIR /var/www/html

# Crear y dar permisos a los directorios que Laravel necesita escribir
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]