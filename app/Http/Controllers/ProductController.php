<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk dengan fitur pencarian, filter, sorting, dan pagination
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'suppliers']);

        // 1. Pencarian nama atau SKU
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        // 2. Filter Kategori
        if ($request->filled('category_id') && (int)$request->category_id > 0) {
            $query->where('category_id', (int)$request->category_id);
        }

        // 3. Filter Status Stok
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($status === 'low') {
                $query->where('stock', '>', 0)->where('stock', '<=', 5);
            } elseif ($status === 'available') {
                $query->where('stock', '>', 0);
            }
        }

        // 4. Sorting Kolom
        $allowedSort = ['sku', 'name', 'price', 'stock'];
        $sortColumn  = in_array($request->sort, $allowedSort, true) ? $request->sort : 'name';
        $sortOrder   = strtolower($request->order ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortColumn, $sortOrder);

        // 5. Pagination
        $allowedPerPage = [3, 5, 10, 25];
        $perPage = in_array((int)$request->per_page, $allowedPerPage, true) ? (int)$request->per_page : 5;
        $products = $query->paginate($perPage)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers', 'sortColumn', 'sortOrder', 'perPage'));
    }

    /**
     * Form tambah produk baru
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Simpan produk baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'          => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name'         => ['required', 'string', 'max:150'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'price'        => ['required', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
        ], [
            'sku.required'   => 'SKU wajib diisi.',
            'sku.unique'     => 'SKU sudah digunakan oleh produk lain. Gunakan SKU unik.',
            'name.required'  => 'Nama produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min'      => 'Harga produk tidak boleh bernilai negatif.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'sku'         => $validated['sku'],
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'] ?: null,
                'price'       => $validated['price'],
                'stock'       => 0, // Stok awal selalu 0, diubah melalui Transaksi Stok
                'description' => $validated['description'] ?? null,
            ]);

            if (!empty($validated['supplier_ids'])) {
                $product->suppliers()->sync($validated['supplier_ids']);
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Detail informasi produk
     */
    public function show(Product $product)
    {
        $product->load(['category', 'suppliers', 'stockTransactions.user']);

        return view('products.show', compact('product'));
    }

    /**
     * Form edit produk
     */
    public function edit(Product $product)
    {
        $product->load('suppliers');
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Perbarui data produk
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku'          => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name'         => ['required', 'string', 'max:150'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'price'        => ['required', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
        ], [
            'sku.required'   => 'SKU wajib diisi.',
            'sku.unique'     => 'SKU sudah digunakan oleh produk lain.',
            'name.required'  => 'Nama produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min'      => 'Harga tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($product, $validated) {
            $product->update([
                'sku'         => $validated['sku'],
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'] ?: null,
                'price'       => $validated['price'],
                'description' => $validated['description'] ?? null,
            ]);

            $product->suppliers()->sync($validated['supplier_ids'] ?? []);
        });

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $product->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus produk dengan proteksi integritas transaksi stok
     */
    public function destroy(Product $product)
    {
        // Proteksi Otorisasi: Hanya Admin yang dapat menghapus produk
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak: Hanya Administrator yang berhak menghapus produk.');
        }

        // Aturan Bisnis #10 P4: Cek riwayat transaksi stok
        $trxCount = $product->stockTransactions()->count();
        if ($trxCount > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat ' . $trxCount . ' transaksi stok. Ini untuk menjaga integritas data riwayat gudang.');
        }

        $name = $product->name;
        $product->suppliers()->detach();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk "' . $name . '" berhasil dihapus.');
    }
}
