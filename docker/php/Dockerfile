FROM php:8.3-fpm

# Install system dependencies for Debian-based images (apt-get)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    pkg-config \
    libzip-dev \
    nodejs \
    npm \
    libicu-dev \
    && docker-php-ext-configure intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory and copy application files
WORKDIR /var/www
COPY . /var/www/

# Copy entrypoint script and make it executable
COPY ./docker/php/php-entrypoint.sh /php-entrypoint.sh
RUN chmod +x /php-entrypoint.sh

# Set entrypoint and command
ENTRYPOINT ["/php-entrypoint.sh"]

CMD ["php-fpm"]
