FROM php:8.4-cli

# System libraries for Composer and the PHP extensions below.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    libpq-dev \
    zip \
    && rm -rf /var/lib/apt/lists/*

# PostgreSQL for the app, SQLite for the test suite.
RUN docker-php-ext-install mbstring bcmath gd xml zip pdo_pgsql pdo_sqlite
RUN printf 'upload_max_filesize=6M\npost_max_size=8M\n' > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dependencies first, so this layer is cached until composer.lock changes.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Kept outside /app (which is bind-mounted in development); CRs stripped in case
# the repository was checked out with Windows line endings.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint && chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

# Creates .env and APP_KEY if missing, runs migrations, seeds demo data on first start.
CMD ["entrypoint"]
