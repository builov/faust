FROM php:8.1-fpm

# 1. Устанавливаем системные зависимости, нужные для Composer и PHP расширений
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Копируем Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Устанавливаем рабочую директорию
WORKDIR /var/www/html
