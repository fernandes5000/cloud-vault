FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    zip \
    unzip \
  && docker-php-ext-install bcmath intl pdo_pgsql pgsql pdo_sqlite pcntl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/backend

CMD ["php", "-v"]
