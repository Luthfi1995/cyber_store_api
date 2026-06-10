<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use Illuminate\Http\Request;

class ExpeditionController extends Controller
{
    public function index(Request $request)
    {
        $query = Expedition::withCount('orders');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $expeditions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.expeditions.index', compact('expeditions'));
    }

    public function create()
    {
        return view('admin.expeditions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'code'           => ['required', 'string', 'max:50', 'unique:expeditions,code'],
            'service'        => ['required', 'string', 'max:20'],
            'base_cost'      => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1'],
            'is_active'      => ['boolean'],
        ]);

        Expedition::create([
            'name'           => $validated['name'],
            'code'           => strtolower($validated['code']),
            'service'        => strtoupper($validated['service']),
            'base_cost'      => $validated['base_cost'],
            'estimated_days' => $validated['estimated_days'],
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil ditambahkan.');
    }

    public function edit(Expedition $expedition)
    {
        return view('admin.expeditions.edit', compact('expedition'));
    }

    public function update(Request $request, Expedition $expedition)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'code'           => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('expeditions', 'code')->ignore($expedition->id)],
            'service'        => ['required', 'string', 'max:20'],
            'base_cost'      => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1'],
            'is_active'      => ['boolean'],
        ]);

        $expedition->update([
            'name'           => $validated['name'],
            'code'           => strtolower($validated['code']),
            'service'        => strtoupper($validated['service']),
            'base_cost'      => $validated['base_cost'],
            'estimated_days' => $validated['estimated_days'],
            'is_active'      => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil diperbarui.');
    }

    public function destroy(Expedition $expedition)
    {
        if ($expedition->orders()->exists()) {
            return back()->with('error', 'Ekspedisi tidak dapat dihapus karena sudah digunakan di order.');
        }

        $expedition->delete();
        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil dihapus.');
    }

    public function toggleActive(Expedition $expedition)
    {
        $expedition->update(['is_active' => !$expedition->is_active]);
        $status = $expedition->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Ekspedisi {$expedition->name} berhasil {$status}.");
    }
}
