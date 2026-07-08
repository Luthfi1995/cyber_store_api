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

# ─── Jalankan Laravel Scheduler sebagai background process ───────────
# Railway tidak memiliki cron daemon, sehingga kita menjalankan scheduler
# dalam loop setiap 60 detik menggunakan 'while true' di background.
# Laravel Schedule::command('orders:sync-tracking')->hourly() akan
# memastikan perintah sebenarnya hanya dieksekusi satu kali per jam.
echo "Starting Laravel Scheduler in background..."
while true; do
    php artisan schedule:run --no-interaction >> /dev/null 2>&1
    sleep 60
done &
echo "Laravel Scheduler started (PID: $!)."
