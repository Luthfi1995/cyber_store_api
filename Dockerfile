FROM serversideup/php:8.3-fpm-nginx-alpine

# Install PHP extensions required for image resizing
RUN install-php-extensions gd

# Set the working directory
WORKDIR /var/www/html

# Copy all files with correct ownership for www-data
COPY --chown=www-data:www-data . .

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
