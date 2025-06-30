FROM php:8.2-apache

# 1) Installer les dépendances système et les extensions PHP
RUN apt-get update \
 && apt-get install -y git unzip zip curl libicu-dev libpq-dev libonig-dev libzip-dev \
      libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev \
 && docker-php-ext-install intl pdo pdo_mysql zip opcache \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# 2) Dire à Apache de servir le dossier public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# 3) Pré‑copier composer.json + composer.lock (cache Docker)
WORKDIR /var/www/html
COPY composer.json composer.lock ./

# 4) Installer Composer et les dépendances sans scripts auto-scripts
RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer \
 && COMPOSER_ALLOW_SUPERUSER=1 \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 5) Copier tout le reste du projet (src/, public/, config/, vendor/, …)
COPY . .

# 6) Mettre les bons droits pour Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# 7) Au démarrage, copier les clés JWT injectées par Render, exécuter les migrations et lancer Apache
CMD ["sh", "-c", "\
      mkdir -p config/jwt && \
      if [ -f /etc/secrets/private.pem ]; then cp /etc/secrets/private.pem config/jwt/private.pem && chmod 644 config/jwt/private.pem; fi && \
      if [ -f /etc/secrets/public.pem ]; then cp /etc/secrets/public.pem config/jwt/public.pem && chmod 644 config/jwt/public.pem; fi && \
      php bin/console doctrine:migrations:migrate --no-interaction && \
      apache2-foreground"]

