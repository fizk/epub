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
    rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    echo "\n[xdebug] \n\
    xdebug.mode=develop,debug \n\
    xdebug.client_host=host.docker.internal \n\
    xdebug.start_with_request=yes \n\
    xdebug.client_port = 9003 \n\
    xdebug.discover_client_host=false \n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY ./composer.json /app/composer.json

RUN composer i

