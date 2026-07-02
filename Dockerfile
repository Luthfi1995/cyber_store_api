FROM richarvey/nginx-php-fpm:latest

# Salin semua file proyek ke direktori web root di container
COPY . /var/www/html

# Mengonfigurasi variabel lingkungan untuk base image richarvey
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# KUNCI UNTUK CLOUD RUN: Beritahu image richarvey untuk mengubah port Nginx ke 8080
ENV PORT 8080

# Otomatis menginstall composer dependencies saat build/runtime:
ENV SKIP_COMPOSER 0
ENV COMPOSER_ALLOW_SUPERUSER 1

# Konfigurasi Laravel untuk Produksi
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# KUNCI PERBAIKAN: Jalankan composer secara manual saat build image
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Buka port 8080 sesuai standar Google Cloud Run
EXPOSE 8080

# Jalankan perintah startup default dari image
CMD ["/start.sh"]
