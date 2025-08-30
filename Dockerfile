FROM php:5.6-cli-alpine

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
        libcurl \
        libxml2-dev \
        curl-dev \
        libzip-dev \
        zip \
        autoconf \
        gcc \
        g++ \
        make \
        && docker-php-ext-install -j$(nproc) curl xml zip \
        && pecl install xdebug-2.5.5 \
        && docker-php-ext-enable xdebug

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /app

COPY . /app

RUN composer install

CMD ["php", "/app/vendor/bin/phpunit"]