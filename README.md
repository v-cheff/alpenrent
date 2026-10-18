# Alpenrent

## Запуск через Docker Compose

Нужны Docker Desktop и Docker Compose. Из корня проекта выполните:

```sh
docker compose up --build -d
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed --class=EquipmentSeeder
docker compose exec backend php artisan storage:link
```

После запуска приложение доступно на [http://localhost:8000](http://localhost:8000),
frontend — на [http://localhost:5173](http://localhost:5173).

Базовый URL API для frontend указан в `frontend/.env` в переменной
`VITE_API_URL`. Vite загружает файл автоматически; если адрес backend изменится,
обновите URL в этом файле.

PostgreSQL доступен backend-контейнеру по адресу `postgres:5432`, а с хоста —
по адресу `localhost:5432`. Для локальной разработки Compose по умолчанию
создает базу и пользователя `alpenrent` с паролем `alpenrent`. Эти значения можно
переопределить переменными окружения `DB_DATABASE`, `DB_USERNAME` и `DB_PASSWORD`.
Чтобы хранить настройки в файле, скопируйте `.env.example` в `.env` в корне
проекта и отредактируйте значения. Корневой `.env` игнорируется Git. Значения
по умолчанию предназначены только для локальной разработки.

Данные PostgreSQL хранятся в Docker volume `postgres_data` и сохраняются при
остановке контейнеров через `docker compose down`.

Фотографии загружаются в `backend/storage/app/public/equipment` и доступны через
Laravel по адресу `/storage/equipment/...`. Каталог `/admin` позволяет добавлять
equipment; новые записи сохраняются в PostgreSQL и сразу появляются в каталоге.
Поддерживаются JPG, PNG и WEBP размером до 5 МБ либо ссылка на изображение.

**Важно:** авторизации для `/admin` и API добавления пока нет — endpoint
предназначен только для локальной разработки и не должен быть открыт в интернет.
