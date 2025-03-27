FROM php:8.1-fpm

# Cài đặt dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git curl libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy source code Laravel vào container
WORKDIR /var/www
COPY . .

# Cấp quyền cho storage và bootstrap
RUN chmod -R 777 storage bootstrap/cache

CMD ["php-fpm"]