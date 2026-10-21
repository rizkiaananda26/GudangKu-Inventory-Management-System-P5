<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Tampilkan daftar supplier
     */
    public function index()
    {
        $suppliers = Supplier::withCount('products')->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Simpan supplier baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email'   => 'Format email supplier tidak valid.',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui data supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email'   => 'Format email supplier tidak valid.',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    /**
     * Hapus supplier (Khusus Administrator)
     */
    public function destroy(Supplier $supplier)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Administrator yang berhak menghapus supplier.');
        }

        $name = $supplier->name;
        $supplier->products()->detach();
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier "' . $name . '" berhasil dihapus.');
    }
}
