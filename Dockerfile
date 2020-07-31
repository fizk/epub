FROM php:7.4.8-cli-buster

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    imagemagick \
    libfreetype6-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libmagickwand-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    zip

RUN pecl install imagick; \
    docker-php-ext-configure zip; \
    docker-php-ext-install zip; \
    docker-php-ext-enable imagick ; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*;

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY ./composer.json /var/www/composer.json

RUN composer i

