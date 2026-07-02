# Menggunakan tag php8.3 agar sesuai dengan kebutuhan Laravel Anda
FROM richarvey/nginx-php-fpm:php8.3-latest

# Salin semua file proyek ke direktori web root di container
COPY . /var/www/html

# MEMPERBAIKI FORMAT ENV (Menggunakan key=value untuk menghindari warning linter)
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
