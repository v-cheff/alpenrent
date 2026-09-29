FROM node:22-alpine

WORKDIR /app

# Копируем манифесты зависимостей
COPY package.json package-lock.json ./

# Устанавливаем точные версии пакетов из lock-файла
RUN npm ci

# Копируем весь исходный код фронтенда
COPY . .

EXPOSE 5173

# Запускаем dev-сервер Vite с выходом во внешнюю сеть контейнера
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]