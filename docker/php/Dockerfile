FROM php:8.4-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/compose

# Dependências de sistema (camada isolada — facilita ver falha de rede/apt)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensões mínimas para Laravel (sem gd/redis — build mais leve e estável)
# -j1 evita OOM em homelabs com pouca RAM
RUN docker-php-ext-install -j1 \
        pdo_mysql \
        mbstring \
        bcmath \
        zip \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/compose

COPY php.ini /usr/local/etc/php/conf.d/99-nosso-hub.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
