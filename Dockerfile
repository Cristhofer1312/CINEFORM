# Dockerfile ejemplo para PHP-FPM (Laravel dev)

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql pgsql gd \
	&& pecl install xdebug \
	&& docker-php-ext-enable xdebug
	
# Configura e instala GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-enable gd

# Instalar librerías necesarias y extensiones PHP
RUN apt-get update && apt-get install -y libpq-dev libzip-dev unzip git && \
    docker-php-ext-install pdo pdo_pgsql pgsql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Crea los directorios storage y bootstrap/cache para evitar errores al hacer chown
RUN mkdir -p storage bootstrap/cache

# Ajustar permisos para el usuario www-data
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer puerto PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
