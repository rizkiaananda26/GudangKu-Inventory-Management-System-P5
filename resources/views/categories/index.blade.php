@extends('layouts.app')

@section('title', 'Kelola Kategori')

@section('content')
<h1>Kelola Kategori Barang</h1>

@php
    $editCategory = null;
    if (request('edit_id')) {
        $editCategory = $categories->firstWhere('id', request('edit_id'));
    }
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; align-items: start;">
    <!-- FORM TAMBAH / EDIT KATEGORI -->
    <section class="panel">
        <h2>{{ $editCategory ? 'Edit Kategori: ' . $editCategory->name : 'Tambah Kategori Baru' }}</h2>

        <form method="POST" action="{{ $editCategory ? route('categories.update', $editCategory) : route('categories.store') }}">
            @csrf
            @if($editCategory)
                @method('PUT')
            @endif

            <label style="display: block; margin-bottom: 12px;">
                <strong>Nama Kategori *</strong>
                <input type="text" name="name" value="{{ old('name', $editCategory->name ?? '') }}" required placeholder="Contoh: Elektronik, ATK..." style="width: 100%;">
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 16px;">
                <strong>Deskripsi (Opsional)</strong>
                <textarea name="description" rows="3" placeholder="Keterangan singkat kelompok barang..." style="width: 100%;">{{ old('description', $editCategory->description ?? '') }}</textarea>
                @error('description') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="button button-success">
                    {{ $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                </button>
                @if($editCategory)
                    <a href="{{ route('categories.index') }}" class="button button-secondary">Batal</a>
                @endif
            </div>
        </form>
    </section>

    <!-- TABEL DAFTAR KATEGORI -->
    <section class="panel">
        <h2>Daftar Kategori Terdaftar</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th style="text-align: center; width: 110px;">Jml Produk</th>
                    <th style="text-align: center; width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td><strong>{{ $cat->name }}</strong></td>
                        <td style="color: #4a5568; font-size: 13.5px;">{{ $cat->description ?? '-' }}</td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #edf2f7; color: #4a5568;">
                                {{ $cat->products_count }} item
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('categories.index', ['edit_id' => $cat->id]) }}" class="button" style="padding: 3px 8px; font-size: 12px; background: #2b6cb0;">
                                Edit
                            </a>

                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('categories.destroy', $cat) }}" style="display: inline;" onsubmit="return confirm('Hapus kategori \'{{ $cat->name }}\'? Produk terkait akan menjadi tanpa kategori.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button button-danger" style="padding: 3px 8px; font-size: 12px;">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #a0aec0; padding: 20px;">
                            Belum ada kategori yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
