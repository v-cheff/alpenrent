# Alpenrent

## Запуск через Docker Compose

Нужны Docker Desktop и Docker Compose. Из корня проекта выполните:

```sh
docker compose up --build -d
docker compose exec backend php artisan migrate
```

После запуска приложение доступно на [http://localhost:8000](http://localhost:8000),
frontend — на [http://localhost:5173](http://localhost:5173).

PostgreSQL доступен backend-контейнеру по адресу `postgres:5432`, а с хоста —
по адресу `localhost:5432`. Для локальной разработки Compose по умолчанию
создает базу и пользователя `alpenrent` с паролем `alpenrent`. Эти значения можно
переопределить переменными окружения `DB_DATABASE`, `DB_USERNAME` и `DB_PASSWORD`.
Чтобы хранить настройки в файле, скопируйте `.env.example` в `.env` в корне
проекта и отредактируйте значения. Корневой `.env` игнорируется Git. Значения
по умолчанию предназначены только для локальной разработки.

Данные PostgreSQL хранятся в Docker volume `postgres_data` и сохраняются при
остановке контейнеров через `docker compose down`.
