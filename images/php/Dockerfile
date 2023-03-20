FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        zip \
        unzip \
        libonig-dev \
        libzip-dev \
        libpq-dev  \
        libcurl4-openssl-dev  \
        pkg-config  \
        libssl-dev \
    && pecl install xdebug ds mongodb redis \
    && docker-php-ext-enable xdebug ds mongodb redis \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql zip pcntl bcmath sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www

CMD ["php-fpm"]
