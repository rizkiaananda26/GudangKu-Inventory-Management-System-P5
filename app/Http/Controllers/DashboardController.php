<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama dashboard dengan ringkasan statistik inventaris
     */
    public function index()
    {
        $totalProducts   = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers  = Supplier::count();
        $lowStockCount   = Product::where('stock', '<=', 5)->count();

        // 5 produk dengan stok paling sedikit untuk peringatan restock
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'lowStockCount',
            'lowStockProducts'
        ));
    }
}
