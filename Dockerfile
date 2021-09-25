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
    pecl install xdebug; \
    docker-php-ext-configure zip; \
    docker-php-ext-install zip; \
    docker-php-ext-enable imagick ; \
    docker-php-ext-enable xdebug; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*; \
    echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.remote_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY ./composer.json /var/www/composer.json

RUN composer i

