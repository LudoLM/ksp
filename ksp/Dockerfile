FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt update && apt install -yqq nodejs npm

# Remontez d'un niveau pour copier le contenu de la racine du projet
COPY ../ /var/www/

COPY apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/

RUN composer install
RUN npm install --force
RUN npm run build

EXPOSE 80
