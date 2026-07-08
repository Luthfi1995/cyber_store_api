FROM serversideup/php:8.3-fpm-nginx-alpine

# Temporarily switch to root to install PHP extensions
USER root
RUN install-php-extensions gd
# Switch back to www-data for composer and app runtime
USER www-data

# Set the working directory
WORKDIR /var/www/html

# Copy all files with correct ownership for www-data
COPY --chown=www-data:www-data . .

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
