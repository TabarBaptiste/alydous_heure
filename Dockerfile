FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libpq-dev libonig-dev libzip-dev \
    libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache mods
RUN a2enmod rewrite

# Set working dir
WORKDIR /var/www/html

# Copy app source
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Install PHP deps without scripts (no DB access)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80
