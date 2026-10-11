FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema y extensiones de PHP
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev

# Instalar extensiones requeridas para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip intl

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar archivos de la aplicación
COPY . /var/www

# Permisos para storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
