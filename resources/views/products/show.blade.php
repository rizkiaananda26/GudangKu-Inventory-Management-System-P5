@extends('layouts.app')

@section('title', 'Detail Produk - ' . $product->name)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <h1>Detail Produk</h1>
    <div>
        <a href="{{ route('products.edit', $product) }}" class="button" style="background: #2b6cb0;">Edit Produk</a>
        <a href="{{ route('stock.index', ['product_id' => $product->id]) }}" class="button button-success">+ Catat Stok</a>
        <a href="{{ route('products.index') }}" class="button button-secondary">&larr; Kembali</a>
    </div>
</div>

<section class="panel">
    <h2>Informasi Barang</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-top: 14px;">
        <div>
            <small>Kode SKU</small>
            <div style="font-size: 18px; font-weight: bold; margin-top: 2px;"><code>{{ $product->sku }}</code></div>
        </div>
        <div>
            <small>Nama Produk</small>
            <div style="font-size: 18px; font-weight: bold; margin-top: 2px;">{{ $product->name }}</div>
        </div>
        <div>
            <small>Kategori</small>
            <div style="font-size: 16px; margin-top: 2px;">{{ $product->category->name ?? 'Tanpa kategori' }}</div>
        </div>
        <div>
            <small>Harga Satuan</small>
            <div style="font-size: 18px; font-weight: bold; color: #6f4e37; margin-top: 2px;">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </div>
        </div>
        <div>
            <small>Stok Tersedia</small>
            <div style="font-size: 18px; font-weight: bold; margin-top: 2px;">
                @if($product->stock <= 0)
                    <span class="badge badge-outstock">0 (Stok Habis)</span>
                @elseif($product->stock <= 5)
                    <span class="badge badge-low">{{ $product->stock }} (Stok Menipis)</span>
                @else
                    <span class="badge badge-ok">{{ $product->stock }} unit</span>
                @endif
            </div>
        </div>
        <div>
            <small>Total Valuasi Aset Stok</small>
            <div style="font-size: 18px; font-weight: bold; color: #2b6cb0; margin-top: 2px;">
                Rp {{ number_format($product->stock * $product->price, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #edf2f7;">
        <small>Pemasok / Supplier Terkait</small>
        <div style="margin-top: 6px;">
            @forelse($product->suppliers as $sup)
                <span style="display: inline-block; background: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 13.5px; margin-right: 6px; margin-bottom: 6px;">
                    &bull; {{ $sup->name }}
                </span>
            @empty
                <em style="color: #a0aec0;">Belum ada supplier yang ditautkan ke produk ini.</em>
            @endforelse
        </div>
    </div>

    @if($product->description)
        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid #edf2f7;">
            <small>Deskripsi / Keterangan</small>
            <p style="margin-top: 4px; line-height: 1.6;">{{ $product->description }}</p>
        </div>
    @endif
</section>

<!-- RIWAYAT MUTASI STOK PRODUK INI -->
<section class="panel">
    <h3>Riwayat Transaksi Stok untuk Produk Ini</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 180px;">Waktu Transaksi</th>
                <th style="width: 120px; text-align: center;">Tipe Mutasi</th>
                <th style="width: 120px; text-align: right;">Jumlah Unit</th>
                <th>Dicatat Oleh</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($product->stockTransactions()->latest()->get() as $trx)
                <tr>
                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $trx->type === 'IN' ? 'badge-in' : 'badge-out' }}">
                            {{ $trx->type === 'IN' ? 'Masuk (IN)' : 'Keluar (OUT)' }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ $trx->type === 'IN' ? '+' : '-' }}{{ $trx->quantity }}
                    </td>
                    <td>{{ $trx->user->full_name ?? '-' }}</td>
                    <td>{{ $trx->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #a0aec0; padding: 20px;">
                        Belum ada riwayat transaksi mutasi stok untuk produk ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
