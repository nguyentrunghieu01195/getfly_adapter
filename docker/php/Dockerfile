FROM php:8.2-fpm

# Cài đặt dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tạo thư mục làm việc
WORKDIR /var/www

# Cấp quyền cho storage & bootstrap
RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]
