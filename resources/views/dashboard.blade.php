@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1>Dashboard Inventaris</h1>

<!-- KARTU STATISTIK UTAMA -->
<div class="grid">
    <section>
        <small>Total Jenis Produk</small>
        <div class="stat">{{ $totalProducts }}</div>
    </section>
    <section>
        <small>Total Kategori</small>
        <div class="stat">{{ $totalCategories }}</div>
    </section>
    <section>
        <small>Total Supplier</small>
        <div class="stat">{{ $totalSuppliers }}</div>
    </section>
    <section>
        <small>Stok Menipis / Habis (&le; 5)</small>
        <div class="stat" style="color: {{ $lowStockCount > 0 ? '#c53030' : '#2f855a' }};">
            {{ $lowStockCount }}
        </div>
    </section>
</div>

<!-- PANEL SAMBUTAN & AKSI CEPAT -->
<section class="panel">
    <h2>Selamat datang, {{ auth()->user()->full_name }}!</h2>
    <p style="margin-top: 4px; color: #4a5568;">
        Anda masuk sebagai:
        <span class="badge {{ auth()->user()->isAdmin() ? 'badge-admin' : 'badge-staff' }}">
            {{ auth()->user()->isAdmin() ? 'Administrator' : 'Staff Gudang' }}
        </span>
    </p>

    <div style="margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('stock.index') }}" class="button button-success">+ Catat Transaksi Stok</a>
        <a href="{{ route('products.index') }}" class="button" style="background:#2b6cb0;">Kelola Produk</a>
        <a href="{{ route('reports.index') }}" class="button">Lihat Laporan Inventaris</a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" class="button button-secondary">Kelola Pengguna</a>
        @endif
    </div>
</section>

<!-- PERINGATAN STOK MENIPIS / PERLU RESTOCK -->
@if($lowStockProducts->isNotEmpty())
    <section class="panel" style="border-left: 5px solid #dd6b20;">
        <h3 style="color: #c05621; margin-top: 0;">Perhatian: Produk Menipis &amp; Perlu Restock Segera</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">SKU</th>
                    <th>Nama Produk</th>
                    <th style="width: 25%;">Kategori</th>
                    <th style="width: 15%; text-align: center;">Sisa Stok</th>
                    <th style="width: 15%; text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $lp)
                    <tr>
                        <td><code>{{ $lp->sku }}</code></td>
                        <td><strong>{{ $lp->name }}</strong></td>
                        <td>{{ $lp->category->name ?? 'Tanpa kategori' }}</td>
                        <td style="text-align: center;">
                            @if($lp->stock <= 0)
                                <span class="badge badge-outstock">Habis (0)</span>
                            @else
                                <span class="badge badge-low">{{ $lp->stock }} unit</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('stock.index', ['product_id' => $lp->id, 'type' => 'IN']) }}" class="button button-success" style="padding: 4px 10px; font-size: 12.5px;">
                                + Restock
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif
@endsection
