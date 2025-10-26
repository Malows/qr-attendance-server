FROM php:8.4-fpm

# Instalar dependencias del sistema y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar archivos de dependencias
COPY composer.json composer.lock ./

# Instalar dependencias PHP
RUN composer install --no-scripts --no-autoloader --no-dev --prefer-dist

# Copiar el resto de archivos de la app
COPY . .

# Completar instalación de composer
RUN composer dump-autoload --optimize

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]
