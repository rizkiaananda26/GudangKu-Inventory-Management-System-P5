@extends('layouts.app')

@section('title', 'Tambah Produk Baru')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
    <h1>Tambah Produk Baru</h1>
    <a href="{{ route('products.index') }}" class="button button-secondary">&larr; Kembali ke Daftar Produk</a>
</div>

<section class="panel" style="max-width: 800px;">
    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        <div class="form-row" style="margin-bottom: 14px;">
            <div>
                <label><strong>Kode SKU Produk *</strong></label>
                <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="Contoh: ELK-001" style="width: 100%;">
                @error('sku') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label><strong>Nama Produk *</strong></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama barang..." style="width: 100%;">
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-row" style="margin-bottom: 14px;">
            <div>
                <label><strong>Kategori</strong></label>
                <select name="category_id" style="width: 100%;">
                    <option value="">-- Pilih Kategori (Opsional) --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label><strong>Harga Satuan (Rp) *</strong></label>
                <input type="number" step="100" min="0" name="price" value="{{ old('price', 0) }}" required style="width: 100%;">
                @error('price') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-bottom: 14px;">
            <label><strong>Pemasok / Supplier (Bisa memilih lebih dari satu)</strong></label>
            <div style="margin-top: 6px; padding: 12px; background: #faf7f5; border: 1px solid #e8ddd4; border-radius: 6px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px;">
                @forelse($suppliers as $sup)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; cursor: pointer;">
                        <input type="checkbox" name="supplier_ids[]" value="{{ $sup->id }}" {{ is_array(old('supplier_ids')) && in_array($sup->id, old('supplier_ids')) ? 'checked' : '' }} style="margin: 0;">
                        {{ $sup->name }}
                    </label>
                @empty
                    <em style="color: #a0aec0; font-size: 13px;">Belum ada master data supplier.</em>
                @endforelse
            </div>
            @error('supplier_ids') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div style="margin-bottom: 18px;">
            <label><strong>Deskripsi / Catatan Barang</strong></label>
            <textarea name="description" rows="3" style="width: 100%;" placeholder="Spesifikasi atau informasi tambahan barang...">{{ old('description') }}</textarea>
            @error('description') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <p style="font-size: 13px; color: #718096; margin-bottom: 16px;">
            <em>* Catatan: Stok awal produk baru otomatis 0. Penambahan stok fisik dicatat secara resmi melalui menu <strong>Transaksi Stok</strong> demi akuntabilitas audit gudang.</em>
        </p>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="button button-success">Simpan Produk Baru</button>
            <a href="{{ route('products.index') }}" class="button button-secondary">Batal</a>
        </div>
    </form>
</section>
@endsection
