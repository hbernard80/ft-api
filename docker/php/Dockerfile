FROM php:8.5.12-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl mbstring pdo_mysql zip \
    && a2enmod rewrite \
    && mkdir -p /var/www/html/vendor /var/www/html/var \
    && chown -R www-data:www-data /var/www/html/vendor /var/www/html/var \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
