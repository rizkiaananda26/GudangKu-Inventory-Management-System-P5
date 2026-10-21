@extends('layouts.app')

@section('title', 'Kelola Supplier')

@section('content')
<h1>Kelola Data Supplier</h1>

@php
    $editSupplier = null;
    if (request('edit_id')) {
        $editSupplier = $suppliers->firstWhere('id', request('edit_id'));
    }
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; align-items: start;">
    <!-- FORM TAMBAH / EDIT SUPPLIER -->
    <section class="panel">
        <h2>{{ $editSupplier ? 'Edit Supplier: ' . $editSupplier->name : 'Tambah Supplier Baru' }}</h2>

        <form method="POST" action="{{ $editSupplier ? route('suppliers.update', $editSupplier) : route('suppliers.store') }}">
            @csrf
            @if($editSupplier)
                @method('PUT')
            @endif

            <label style="display: block; margin-bottom: 12px;">
                <strong>Nama Supplier / Perusahaan *</strong>
                <input type="text" name="name" value="{{ old('name', $editSupplier->name ?? '') }}" required placeholder="Contoh: PT Sumber Rejeki" style="width: 100%;">
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 12px;">
                <strong>Nomor Telepon / WhatsApp</strong>
                <input type="text" name="phone" value="{{ old('phone', $editSupplier->phone ?? '') }}" placeholder="Contoh: 0812-3456-7890" style="width: 100%;">
                @error('phone') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 12px;">
                <strong>Alamat Email</strong>
                <input type="email" name="email" value="{{ old('email', $editSupplier->email ?? '') }}" placeholder="kontak@supplier.com" style="width: 100%;">
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 16px;">
                <strong>Alamat Kantor / Gudang</strong>
                <textarea name="address" rows="2" placeholder="Alamat lengkap..." style="width: 100%;">{{ old('address', $editSupplier->address ?? '') }}</textarea>
                @error('address') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="button button-success">
                    {{ $editSupplier ? 'Simpan Perubahan' : 'Tambah Supplier' }}
                </button>
                @if($editSupplier)
                    <a href="{{ route('suppliers.index') }}" class="button button-secondary">Batal</a>
                @endif
            </div>
        </form>
    </section>

    <!-- TABEL DAFTAR SUPPLIER -->
    <section class="panel">
        <h2>Daftar Supplier</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Supplier</th>
                    <th>Kontak &amp; Alamat</th>
                    <th style="text-align: center; width: 100px;">Produk</th>
                    <th style="text-align: center; width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $sup)
                    <tr>
                        <td>
                            <strong>{{ $sup->name }}</strong>
                        </td>
                        <td style="font-size: 13.5px; line-height: 1.4;">
                            @if($sup->phone) <div>Telp: {{ $sup->phone }}</div> @endif
                            @if($sup->email) <div>Email: {{ $sup->email }}</div> @endif
                            @if($sup->address) <div style="color: #718096;">{{ $sup->address }}</div> @endif
                            @if(!$sup->phone && !$sup->email && !$sup->address) <span style="color:#a0aec0;">-</span> @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #edf2f7; color: #4a5568;">
                                {{ $sup->products_count }} item
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('suppliers.index', ['edit_id' => $sup->id]) }}" class="button" style="padding: 3px 8px; font-size: 12px; background: #2b6cb0;">
                                Edit
                            </a>

                            @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('suppliers.destroy', $sup) }}" style="display: inline;" onsubmit="return confirm('Hapus supplier \'{{ $sup->name }}\'?');">
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
                            Belum ada supplier yang ditambahkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
