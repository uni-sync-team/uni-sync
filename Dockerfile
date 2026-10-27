FROM php:8.3-apache

ARG HOST_UID=1000
ARG HOST_GID=1000

# PDO for MySQL + URL rewriting
RUN docker-php-ext-install pdo pdo_mysql \
 && a2enmod rewrite

# Serve only the public/ folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Make Apache's user match your host user (fixes root-owned files on Linux)
RUN usermod -o -u ${HOST_UID} www-data \
 && groupmod -o -g ${HOST_GID} www-data

RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer
