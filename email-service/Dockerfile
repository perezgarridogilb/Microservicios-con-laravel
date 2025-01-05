FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml

RUN pecl install mongodb\
&& docker-php-ext-enable mongodb

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
RUN composer clear-cache

RUN chown -R www-data:www-data /var/www/html  \
    && composer install  && composer dumpautoload 

EXPOSE 80

COPY default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite
