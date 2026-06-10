<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->paginate(20)->withQueryString();
        $products  = Product::select('id', 'name')->orderBy('name')->get();

        return view('admin.stock-movements.index', compact('movements', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', \Illuminate\Validation\Rule::in(['in', 'out'])],
            'quantity'   => ['required', 'integer', 'min:1'],
            'reference'  => ['nullable', 'string', 'max:100'],
            'note'       => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($request->product_id);

        // Validasi stok cukup untuk out
        if ($request->type === 'out' && $product->stock < $request->quantity) {
            return back()->with('error', "Stok tidak cukup! Stok tersedia: {$product->stock}");
        }

        StockMovement::create([
            'product_id' => $request->product_id,
            'user_id'    => auth()->id(),
            'type'       => $request->type,
            'quantity'   => $request->quantity,
            'reference'  => $request->reference,
            'note'       => $request->note,
        ]);

        // Update stok produk
        if ($request->type === 'in') {
            $product->increment('stock', $request->quantity);
        } else {
            $product->decrement('stock', $request->quantity);
        }

        return back()->with('success', 'Mutasi stok berhasil dicatat.');
    }
}
