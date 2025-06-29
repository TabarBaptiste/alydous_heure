# --- Étape unique pour Symfony + Apache ---

FROM php:8.2-apache

# 1) Installer les dépendances système et PHP
RUN apt-get update \
    && apt-get install -y git unzip zip curl libicu-dev libpq-dev libonig-dev libzip-dev \
    libjpeg-dev libpng-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# 2) Configurer Apache pour servir /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf

# 3) Pré‑copier composer.* pour tirer parti du cache Docker
WORKDIR /var/www/html
COPY composer.json composer.lock ./

# 4) Installer Composer et les dépendances PHP
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 5) Copier tout le code de l’app
COPY . .

# 6) (Optionnel) Exécuter les scripts post‑install une fois la BDD dispo
#    Tu pourras lancer manuellement via un entrypoint ou Render Start Command
RUN composer run-script post-install-cmd --no-interaction

# 7) Mettre les bons droits
RUN chown -R www-data:www-data /var/www/html

# 8) Exposer le port HTTP
EXPOSE 80

# 9) Lancer Apache au démarrage
CMD ["apache2-foreground"]
