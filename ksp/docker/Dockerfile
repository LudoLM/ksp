FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt update && apt install -yqq nodejs npm

COPY . /var/www/

COPY ./docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN cd /var/www/ && \
    composer install && \
    npm install --force && \
    npm run build

WORKDIR /var/www/

EXPOSE 80