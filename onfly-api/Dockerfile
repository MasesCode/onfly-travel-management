FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    default-mysql-client \
    git \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    nodejs \
    npm \
    unzip \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy package files
COPY package*.json ./

# Install Node.js dependencies
RUN npm install

# Copy application code
COPY . .

# Copy environment file and generate key
COPY .env.example .env
RUN php artisan key:generate

# Create storage structure and set permissions
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache

# Switch to www-data user
USER www-data

# Expose port and start server
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
