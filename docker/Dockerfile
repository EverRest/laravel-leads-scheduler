# Use the official PHP 8.2 FPM Alpine image as the base image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk --no-cache add \
    libzip-dev \
    libpng \
    libpng-dev \
    oniguruma-dev \
    freetype \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    libwebp \
    libwebp-dev \
    zlib-dev \
    nginx \
    nodejs \
    npm \
    autoconf \
    g++ \
    make \
    pkgconfig

# Install additional PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip exif gd

# Set the working directory
WORKDIR /var/www/html

# Copy Laravel files
COPY .   .

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install npm packages
RUN apk --no-cache add \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    zlib-dev \
    && npm install -g npm

# Set permissions
RUN  chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap

# Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose ports
EXPOSE 9000

ENTRYPOINT ["composer", "install", "--no-interaction"]
