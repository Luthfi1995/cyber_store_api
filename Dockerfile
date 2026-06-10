FROM richarvey/nginx-php-fpm:latest

# Salin semua file proyek ke direktori web root di container
COPY . /var/www/html

# Mengonfigurasi variabel lingkungan untuk base image richarvey
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Nonaktifkan Composer install otomatis di runtime jika kita ingin Docker membangunnya,
# atau biarkan base image menjalankannya secara otomatis. Kita atur agar otomatis menginstall composer dependencies:
ENV SKIP_COMPOSER 0
ENV COMPOSER_ALLOW_SUPERUSER 1

# Konfigurasi Laravel untuk Produksi
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Buka port 80 untuk lalu lintas web
EXPOSE 80

# Jalankan perintah startup default dari image
CMD ["/start.sh"]
