FROM cakephp:5.2-apache

# Install additional extensions
RUN apt-get update && apt-get install -y \
    libusb-1.0-0-dev \
    && docker-php-ext-install -j$(nproc) zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install Composer dependencies into the image
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-progress \
    && composer clear-cache

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Copy .env file to config directory (in case it wasn't copied)
COPY config/.env* /var/www/html/config/

# Ensure runtime directories exist and are writable by Apache
# (CakePHP requires these specific writable subdirectories)
RUN mkdir -p \
        /var/www/html/logs \
        /var/www/html/tmp/cache/models \
        /var/www/html/tmp/cache/persistent \
        /var/www/html/tmp/cache/views \
        /var/www/html/tmp/sessions \
        /var/www/html/tmp/tests \
    && chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
