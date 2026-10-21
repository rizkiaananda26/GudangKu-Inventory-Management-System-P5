<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan. Gunakan nama lain.',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui data kategori
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah digunakan oleh kategori lain.',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori (Khusus Administrator)
     */
    public function destroy(Category $category)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Administrator yang berhak menghapus kategori.');
        }

        $productCount = $category->products()->count();
        $name = $category->name;
        $category->delete();

        if ($productCount > 0) {
            return redirect()->route('categories.index')
                ->with('info', 'Kategori "' . $name . '" berhasil dihapus. Sebanyak ' . $productCount . ' produk terkait kini berstatus "Tanpa kategori".');
        }

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}
