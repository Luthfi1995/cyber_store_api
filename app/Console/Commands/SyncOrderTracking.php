<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncOrderTracking extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:sync-tracking
                            {--limit=50 : Maksimal jumlah pesanan yang diproses per eksekusi}';

    /**
     * The console command description.
     */
    protected $description = 'Sinkronisasi tracking resi secara otomatis untuk pesanan yang sedang dikirim (status: shipped).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        // Ambil semua pesanan berstatus 'shipped' yang memiliki nomor resi
        $orders = Order::where('status', Order::STATUS_SHIPPED)
            ->whereNotNull('resi_number')
            ->where('resi_number', '!=', '')
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Tidak ada pesanan yang perlu disinkronkan.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$orders->count()} pesanan untuk disinkronkan...");

        $successCount = 0;
        $failCount    = 0;

        foreach ($orders as $order) {
            try {
                $result = $order->syncTracking();

                if ($result['success']) {
                    $successCount++;
                    $this->line("  ✓ Order #{$order->invoice_number}: {$result['message']}");
                } else {
                    $failCount++;
                    $this->warn("  ✗ Order #{$order->invoice_number}: {$result['message']}");
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->error("  ✗ Order #{$order->invoice_number}: Exception — {$e->getMessage()}");
                Log::error("SyncOrderTracking gagal untuk order #{$order->invoice_number}", [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Selesai. Berhasil: {$successCount} | Gagal: {$failCount}");

        return self::SUCCESS;
    }
}
