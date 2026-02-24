FROM php:8.5-apache

# Set web root to htdocs
ENV APACHE_DOCUMENT_ROOT=/var/www/html/htdocs

# Install system dependencies and PHP extensions commonly used by modern PHP apps
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        zip \
        pdo \
        pdo_mysql \
        mysqli \
    && pecl install redis \
    && docker-php-ext-enable redis

# Enable Apache modules and set DocumentRoot
RUN a2enmod rewrite headers expires \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy custom vhost and PHP config
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/conf.d/zxart.ini

WORKDIR /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD curl -fsS http://localhost/ || exit 1
