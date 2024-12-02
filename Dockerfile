FROM php:8.4.1-apache

# Instalacja wymaganych pakietów
RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql pgsql

# Włączenie mod_rewrite (potrzebne do działania niektórych aplikacji PHP)
RUN a2enmod rewrite