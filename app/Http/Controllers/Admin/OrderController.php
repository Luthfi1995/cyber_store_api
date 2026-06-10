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
        ]);

        $order->update(['status' => $request->status]);

        // Tambah tracking otomatis
        $order->trackings()->create([
            'status'      => $request->status,
            'description' => 'Status diperbarui oleh Admin: ' . ($this->statusLabels[$request->status] ?? $request->status),
            'location'    => 'Admin Panel',
        ]);

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
}
