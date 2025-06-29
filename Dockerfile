FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libpq-dev libonig-dev libzip-dev \
    libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Apache to serve from Symfony's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Set working directory (root du projet Symfony)
WORKDIR /var/www/html

# Copy full Symfony project
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
