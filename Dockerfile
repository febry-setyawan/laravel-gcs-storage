FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    ca-certificates \
    nginx \
    supervisor \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    soap

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www/html

# Copy nginx config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set up environment file for Laravel before running composer dan set permissions
RUN cp /var/www/html/.env.docker /var/www/html/.env && \
    mkdir -p /var/www/html/storage /var/www/html/storage/logs /var/www/html/bootstrap/cache /var/www/html/docker/logs&& \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/bootstrap/cache && \
    chmod -R 777 /var/www/html/storage /var/www/html/storage/logs /var/www/html/docker/logs

# Install composer dependencies
RUN composer config --global disable-tls true && composer install --no-dev --optimize-autoloader

# Expose port 80 for nginx
EXPOSE 80

# Start supervisor (which will run both php-fpm and nginx)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

