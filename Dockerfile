FROM php:5.6-cli-alpine

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache \
        libcurl \
        libxml2-dev \
        curl-dev \
        libzip-dev \
        zip \
        && docker-php-ext-install -j$(nproc) curl xml zip

WORKDIR /app

COPY . /app

RUN composer install --optimize-autoloader

CMD ["php", "/app/vendor/bin/phpunit"]