<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan valuasi persediaan dan mutasi per periode
     */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        // ====================================================================
        // 1. LAPORAN NILAI PERSEDIAAN BARANG (VALUASI STOK)
        // ====================================================================
        $valQuery = Product::with('category')->orderBy('name');

        if ($request->filled('category_id') && (int)$request->category_id > 0) {
            $valQuery->where('category_id', (int)$request->category_id);
        }

        $valuationProducts = $valQuery->get();

        $totalValuationUnits = $valuationProducts->sum('stock');
        $totalValuationAmount = $valuationProducts->sum(function ($p) {
            return $p->stock * (float)$p->price;
        });

        // ====================================================================
        // 2. LAPORAN REKAPITULASI MUTASI STOK PER PERIODE
        // ====================================================================
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo   = $request->input('date_to', date('Y-m-d'));

        $mutationProducts = Product::with(['stockTransactions' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereDate('created_at', '>=', $dateFrom)
              ->whereDate('created_at', '<=', $dateTo);
        }])->orderBy('name')->get()->map(function ($product) {
            $inQty  = $product->stockTransactions->where('type', 'IN')->sum('quantity');
            $outQty = $product->stockTransactions->where('type', 'OUT')->sum('quantity');

            return [
                'id'        => $product->id,
                'sku'       => $product->sku,
                'name'      => $product->name,
                'total_in'  => $inQty,
                'total_out' => $outQty,
                'net'       => $inQty - $outQty,
            ];
        });

        $periodTotalIn  = $mutationProducts->sum('total_in');
        $periodTotalOut = $mutationProducts->sum('total_out');
        $periodNet      = $periodTotalIn - $periodTotalOut;

        return view('reports.index', compact(
            'categories',
            'valuationProducts',
            'totalValuationUnits',
            'totalValuationAmount',
            'mutationProducts',
            'dateFrom',
            'dateTo',
            'periodTotalIn',
            'periodTotalOut',
            'periodNet'
        ));
    }
}
