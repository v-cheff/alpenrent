FROM php:8.4-cli

# 1. Устанавливаем системные зависимости Linux (нужны для Composer и расширений PHP)
# Добавлены libsqlite3-dev и libzip-dev для сборки pdo_sqlite и zip
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    && rm -rf /var/lib/apt/lists/*

# 2. Устанавливаем базовые расширения PHP, необходимые для работы Laravel
# Добавлены pdo_sqlite, pdo_mysql и zip
RUN docker-php-ext-install mbstring bcmath gd xml zip

WORKDIR /app

# 3. Подтягиваем Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Копируем файлы зависимостей прямо из папки backend/
COPY composer.json composer.lock ./

# 5. Качаем vendor-пакеты (шаг сработает быстро благодаря кэшу Docker)
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# 6. Копируем весь остальной код из папки backend/ в /app контейнера
COPY . .

# 7. Генерируем автозагрузку классов
RUN composer dump-autoload --optimize

# 8. Создаем служебные папки, файл базы данных и выставляем права для Linux
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE 8000

# 9. Автоматическая генерация .env и APP_KEY при отсутствии + запуск сервера
CMD ["sh", "-c", "if [ ! -f .env ]; then cp .env.example .env && php artisan key:generate; fi && php artisan serve --host=0.0.0.0 --port=8000"]
