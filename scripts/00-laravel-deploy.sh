#!/usr/bin/env bash

# Berpindah ke direktori root Laravel
cd /var/www/html

# Optimasi Laravel untuk produksi
echo "Optimizing Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan migrasi database
echo "Running database migrations..."
php artisan migrate --force

# Jalankan database seeder
echo "Running database seeders..."
php artisan db:seed --force

