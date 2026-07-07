<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected array $statusLabels = [
        'pending_payment' => 'Menunggu Bayar',
        'paid'            => 'Dibayar',
        'packed'          => 'Dikemas',
        'shipped'         => 'Dikirim',
        'arrived'         => 'Tiba',
        'completed'       => 'Selesai',
        'cancelled'       => 'Dibatalkan',
    ];

    public function index(Request $request)
    {
        $query = Order::with(['user', 'expedition'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                  ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(15)->withQueryString();
        $statusLabels = $this->statusLabels;

        return view('admin.orders.index', compact('orders', 'statusLabels'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'address', 'expedition', 'items.product', 'payment', 'trackings']);
        $statusLabels = $this->statusLabels;

        return view('admin.orders.show', compact('order', 'statusLabels'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in(array_keys($this->statusLabels))],
            'proof_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $updateData = ['status' => $request->status];

        // Otomatis input resi jika status dikirim ('shipped') dan belum ada resi
        if ($request->status === 'shipped' && empty($order->resi_number)) {
            $expName = preg_replace('/[^A-Za-z0-9]/', '', $order->expedition?->name ?? 'EXP');
            $updateData['resi_number'] = strtoupper($expName) . mt_rand(100000000, 999999999);
        }

        $order->update($updateData);

        $proofPhotoPath = null;
        if ($request->hasFile('proof_photo')) {
            $proofPhotoPath = $request->file('proof_photo')->store('order_proofs', 'public');
        }

        $location = 'Admin Panel';
        if ($request->status === 'packed' || $request->status === 'shipped') {
            $storeName = \App\Models\Setting::get('store_name', 'UBSI Store');
            $storeCity = \App\Models\Setting::get('store_city_name', 'Jakarta Pusat');
            $location = "$storeCity ($storeName)";
        }

        // Tambah tracking otomatis
        $order->trackings()->create([
            'status'      => $request->status,
            'description' => 'Status diperbarui oleh Admin: ' . ($this->statusLabels[$request->status] ?? $request->status),
            'location'    => $location,
            'proof_photo' => $proofPhotoPath,
        ]);

        if ($request->status === 'shipped') {
            $storeName = \App\Models\Setting::get('store_name', 'UBSI Store');
            $storeCity = \App\Models\Setting::get('store_city_name', 'Jakarta Pusat');
            $order->trackings()->create([
                'status'      => 'shipped',
                'description' => 'Paket diserahkan ke kurir untuk pengiriman.',
                'location'    => "$storeCity ($storeName)",
            ]);
        }

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    public function updateResi(Request $request, Order $order)
    {
        $request->validate([
            'resi_number' => ['required', 'string', 'max:100'],
        ]);

        $order->update(['resi_number' => $request->resi_number]);

        return back()->with('success', 'Nomor resi berhasil disimpan.');
    }

    public function trackWaybill(Order $order)
    {
        $result = $order->syncTracking();

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
