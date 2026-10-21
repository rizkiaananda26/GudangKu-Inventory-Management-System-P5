@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <h1>Edit Produk: {{ $product->name }}</h1>
    <a href="{{ route('products.index') }}" class="button button-secondary">&larr; Kembali ke Daftar Produk</a>
</div>

<section class="panel" style="max-width: 800px;">
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')

        <div class="form-row" style="margin-bottom: 14px;">
            <div>
                <label><strong>Kode SKU Produk *</strong></label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required style="width: 100%;">
                @error('sku') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label><strong>Nama Produk *</strong></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required style="width: 100%;">
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row" style="margin-bottom: 14px;">
            <div>
                <label><strong>Kategori</strong></label>
                <select name="category_id" style="width: 100%;">
                    <option value="">-- Tanpa Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label><strong>Harga Satuan (Rp) *</strong></label>
                <input type="number" step="100" min="0" name="price" value="{{ old('price', $product->price) }}" required style="width: 100%;">
                @error('price') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-bottom: 14px;">
            <label><strong>Pemasok / Supplier</strong></label>
            @php
                $selectedSuppliers = old('supplier_ids', $product->suppliers->pluck('id')->toArray());
            @endphp
            <div style="margin-top: 6px; padding: 12px; background: #faf7f5; border: 1px solid #e8ddd4; border-radius: 6px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px;">
                @forelse($suppliers as $sup)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                        <input type="checkbox" name="supplier_ids[]" value="{{ $sup->id }}" {{ in_array($sup->id, $selectedSuppliers) ? 'checked' : '' }} style="margin: 0;">
                        {{ $sup->name }}
                    </label>
                @empty
                    <em style="color: #a0aec0; font-size: 13px;">Belum ada data supplier.</em>
                @endforelse
            </div>
            @error('supplier_ids') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 18px;">
            <label><strong>Deskripsi / Catatan Barang</strong></label>
            <textarea name="description" rows="3" style="width: 100%;">{{ old('description', $product->description) }}</textarea>
            @error('description') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div style="padding: 10px 14px; background: #edf2f7; border-radius: 6px; margin-bottom: 18px; font-size: 13.5px;">
            Stok saat ini: <strong>{{ $product->stock }} unit</strong>. 
            <small>(Stok hanya dapat diubah melalui menu <a href="{{ route('stock.index') }}">Transaksi Stok</a>).</small>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="button button-success">Perbarui Data Produk</button>
            <a href="{{ route('products.index') }}" class="button button-secondary">Batal</a>
        </div>
    </form>
</section>
@endsection
