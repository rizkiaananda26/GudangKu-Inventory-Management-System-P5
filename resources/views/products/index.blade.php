@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <h1>Daftar Produk Inventaris</h1>
    <a href="{{ route('products.create') }}" class="button button-success">+ Tambah Produk Baru</a>
</div>

<!-- PANEL PENCARIAN & FILTER -->
<section class="panel">
    <form method="GET" action="{{ route('products.index') }}" class="form-row">
        <div>
            <label style="font-size: 13px; font-weight: 600;">Cari Produk</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau SKU..." style="width: 100%;">
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Kategori</label>
            <select name="category_id" style="width: 100%;">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Status Stok</label>
            <select name="stock_status" style="width: 100%;">
                <option value="">-- Semua Status --</option>
                <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Tersedia (&gt; 0)</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Menipis (&le; 5)</option>
                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Habis (0)</option>
            </select>
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Baris per Halaman</label>
            <select name="per_page" style="width: 100%;">
                @foreach([3, 5, 10, 25] as $count)
                    <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }} data</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 6px; align-self: flex-end; padding-bottom: 4px;">
            <button type="submit">Filter</button>
            <a href="{{ route('products.index') }}" class="button button-secondary">Reset</a>
        </div>
    </form>
</section>

<!-- TABEL DATA PRODUK -->
<section class="panel">
    <div style="margin-bottom: 12px; color: #718096; font-size: 13.5px;">
        Menampilkan <strong>{{ $products->firstItem() ?? 0 }}</strong> - <strong>{{ $products->lastItem() ?? 0 }}</strong> dari total <strong>{{ $products->total() }}</strong> produk.
    </div>

    <table>
        <thead>
            <tr>
                <th>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'sku', 'order' => ($sortColumn === 'sku' && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}">
                        SKU {!! $sortColumn === 'sku' ? ($sortOrder === 'asc' ? '&uarr;' : '&darr;') : '&#8597;' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'name', 'order' => ($sortColumn === 'name' && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}">
                        Nama Produk {!! $sortColumn === 'name' ? ($sortOrder === 'asc' ? '&uarr;' : '&darr;') : '&#8597;' !!}
                    </a>
                </th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th style="text-align: right;">
                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'price', 'order' => ($sortColumn === 'price' && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}">
                        Harga {!! $sortColumn === 'price' ? ($sortOrder === 'asc' ? '&uarr;' : '&darr;') : '&#8597;' !!}
                    </a>
                </th>
                <th style="text-align: center;">
                    <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'stock', 'order' => ($sortColumn === 'stock' && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}">
                        Stok {!! $sortColumn === 'stock' ? ($sortOrder === 'asc' ? '&uarr;' : '&darr;') : '&#8597;' !!}
                    </a>
                </th>
                <th style="text-align: center; width: 180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                    </td>
                    <td>{{ $product->category->name ?? 'Tanpa kategori' }}</td>
                    <td>
                        @if($product->suppliers->isNotEmpty())
                            {{ $product->suppliers->pluck('name')->join(', ') }}
                        @else
                            <em style="color:#a0aec0;">Belum ada supplier</em>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        @if($product->stock <= 0)
                            <span class="badge badge-outstock">0 (Habis)</span>
                        @elseif($product->stock <= 5)
                            <span class="badge badge-low">{{ $product->stock }} (Menipis)</span>
                        @else
                            <span class="badge badge-ok">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('products.show', $product) }}" class="button button-secondary" style="padding: 4px 8px; font-size: 12.5px;">Detail</a>
                        <a href="{{ route('products.edit', $product) }}" class="button" style="padding: 4px 8px; font-size: 12.5px; background: #2b6cb0;">Edit</a>

                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk \'{{ $product->name }}\'?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button button-danger" style="padding: 4px 8px; font-size: 12.5px;">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px; color: #a0aec0;">
                        Tidak ada data produk yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-links">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
