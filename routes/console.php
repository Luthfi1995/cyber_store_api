<?php

use App\Console\Commands\SyncOrderTracking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi tracking pesanan otomatis setiap 1 jam
// Mengecek semua pesanan berstatus 'shipped' dan memperbarui ke 'arrived' jika sudah terkirim
Schedule::command('orders:sync-tracking')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/order-tracking-sync.log'));
