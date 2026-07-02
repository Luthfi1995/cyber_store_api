# 1. Kunci versi PHP secara eksplisit ke 8.3 menggunakan tag resmi dari richarvey
FROM richarvey/nginx-php-fpm:php8.3-latest

# Silensikan warning root dari composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Salin semua file proyek ke direktori web root di container
COPY . /var/www/html

# Format ENV yang benar (key=value)
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Jalankan composer secara manual saat build image
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Konfigurasi Laravel untuk Produksi
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Buka port 80 untuk lalu lintas web Railway
EXPOSE 80

# Jalankan perintah startup default dari image
CMD ["/start.sh"]