<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.user'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('virtual_account_number', 'like', "%{$search}%")
                  ->orWhereHas('order', fn ($o) => $o->where('invoice_number', 'like', "%{$search}%"))
                  ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('bank')) {
            $query->where('bank_code', $request->bank);
        }

        $payments = $query->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.user', 'order.items.product', 'order.address']);
        return view('admin.payments.show', compact('payment'));
    }
}
