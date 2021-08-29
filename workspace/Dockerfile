FROM php:7.4.8-cli-buster

WORKDIR /app

ENV COMPOSER_HOME=/app

RUN apt-get update; \
    apt-get install -y --no-install-recommends \
    imagemagick \
    libfreetype6-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libmagickwand-dev \
    libxml2-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    zip; \
    pecl install imagick; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-configure zip; \
    docker-php-ext-install zip; \
    docker-php-ext-install exif; \
    docker-php-ext-enable imagick ; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*; \
    cd /usr/local/etc/php/conf.d/ && \
    echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    mkdir -p /app/.composer;



RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/app --filename=composer --version=2.1.3;

# USER www-data

COPY ./composer.json .
# COPY ./composer.lock .
# COPY --chown=www-data:www-data ./composer.json .
# COPY --chown=www-data:www-data ./composer.lock .

RUN /app/composer install --prefer-source --no-interaction --no-dev; \
    /app/composer dump-autoload;

WORKDIR /app/src