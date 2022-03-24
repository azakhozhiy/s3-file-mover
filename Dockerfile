FROM php:8.0.17-fpm-alpine3.15

RUN apk upgrade --update && apk add \
            libpng-dev \
            libmcrypt-dev

RUN apk add build-base zlib-dev
ENV LIBRARY_PATH=/lib:/usr/lib

RUN docker-php-ext-install gd \
    opcache

COPY ./docker/php.ini "$PHP_INI_DIR/php.ini"
COPY ./ /var/www/s3-file-mover

RUN curl https://getcomposer.org/download/2.2.9/composer.phar --output /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

WORKDIR /var/www/s3-file-mover
RUN /usr/local/bin/composer install --prefer-dist --no-interaction --no-plugins --no-scripts
