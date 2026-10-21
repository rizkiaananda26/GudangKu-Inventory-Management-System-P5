<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransactionController extends Controller
{
    /**
     * Tampilkan form transaksi dan riwayat mutasi stok
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('name')->get();

        // Statistik ringkasan transaksi
        $stats = [
            'total_in_trx'  => StockTransaction::where('type', 'IN')->count(),
            'total_out_trx' => StockTransaction::where('type', 'OUT')->count(),
            'total_in_qty'  => StockTransaction::where('type', 'IN')->sum('quantity'),
            'total_out_qty' => StockTransaction::where('type', 'OUT')->sum('quantity'),
        ];

        // Query riwayat transaksi
        $query = StockTransaction::with(['product', 'user'])->latest();

        if ($request->filled('type') && in_array($request->type, ['IN', 'OUT'], true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id') && (int)$request->product_id > 0) {
            $query->where('product_id', (int)$request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(10)->withQueryString();

        return view('stock.index', compact('products', 'stats', 'transactions'));
    }

    /**
     * Catat transaksi stok masuk (IN) atau stok keluar (OUT) secara atomik
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:IN,OUT'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'note'       => ['nullable', 'string', 'max:255'],
        ], [
            'product_id.required' => 'Pilih produk yang ingin ditransaksikan.',
            'type.required'       => 'Pilih jenis mutasi (Stok Masuk atau Stok Keluar).',
            'quantity.required'   => 'Jumlah barang wajib diisi minimal 1.',
            'quantity.min'        => 'Jumlah barang minimal 1 unit.',
        ]);

        $productName = '';
        $newStock = 0;

        try {
            DB::transaction(function () use ($validated, &$productName, &$newStock) {
                // Pessimistic Locking (FOR UPDATE) mencegah race condition / stok minus
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
                $productName = $product->name;
                $currentStock = $product->stock;
                $qty = (int)$validated['quantity'];

                // Aturan Bisnis P4: Stok keluar tidak boleh melebihi sisa stok
                if ($validated['type'] === 'OUT' && $currentStock < $qty) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stok keluar gagal! Jumlah permintaan (' . $qty . ' unit) melebihi stok yang tersedia saat ini (' . $currentStock . ' unit).'
                    ]);
                }

                $newStock = ($validated['type'] === 'IN') ? ($currentStock + $qty) : ($currentStock - $qty);

                // Update stok produk
                $product->update(['stock' => $newStock]);

                // Catat mutasi ke tabel stock_transactions
                StockTransaction::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
                    'type'       => $validated['type'],
                    'quantity'   => $qty,
                    'note'       => $validated['note'] ?? null,
                ]);
            });

            $tipeTeks = ($validated['type'] === 'IN') ? 'masuk' : 'keluar';
            return redirect()->route('stock.index')
                ->with('success', 'Transaksi berhasil dicatat: ' . $validated['quantity'] . ' unit ' . $productName . ' (' . $tipeTeks . '). Sisa stok sekarang: ' . $newStock . ' unit.');

        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }
}
