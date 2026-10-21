@extends('layouts.app')

@section('title', 'Transaksi Stok')

@section('content')
<h1>Transaksi &amp; Mutasi Stok Barang</h1>

<!-- STATISTIK RINGKASAN MUTASI -->
<div class="grid">
    <section>
        <small>Total Transaksi Masuk (IN)</small>
        <div class="stat" style="color: #2f855a;">{{ $stats['total_in_trx'] }}</div>
    </section>
    <section>
        <small>Total Unit Masuk</small>
        <div class="stat" style="color: #2f855a;">+{{ $stats['total_in_qty'] }} <span style="font-size: 14px; font-weight: normal;">unit</span></div>
    </section>
    <section>
        <small>Total Transaksi Keluar (OUT)</small>
        <div class="stat" style="color: #c53030;">{{ $stats['total_out_trx'] }}</div>
    </section>
    <section>
        <small>Total Unit Keluar</small>
        <div class="stat" style="color: #c53030;">-{{ $stats['total_out_qty'] }} <span style="font-size: 14px; font-weight: normal;">unit</span></div>
    </section>
</div>

<!-- FORM PENCATATAN TRANSAKSI STOK -->
<section class="panel" style="border-top: 4px solid #6f4e37;">
    <h2>Form Catat Transaksi Stok</h2>
    <p style="font-size: 13.5px; color: #718096; margin-top: -6px; margin-bottom: 16px;">
        Setiap mutasi fisik barang (masuk dari supplier atau keluar ke pelanggan/departemen) wajib dicatat secara resmi di sini.
    </p>

    <form method="POST" action="{{ route('stock.store') }}">
        @csrf

        <div class="form-row" style="margin-bottom: 14px;">
            <div style="flex: 2;">
                <label><strong>Pilih Produk *</strong></label>
                <select name="product_id" required style="width: 100%;">
                    <option value="">-- Pilih Barang --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id', request('product_id')) == $p->id ? 'selected' : '' }}>
                            [{{ $p->sku }}] {{ $p->name }} &mdash; (Sisa Stok: {{ $p->stock }} unit)
                        </option>
                    @endforeach
                </select>
                @error('product_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div style="flex: 1;">
                <label><strong>Jenis Mutasi *</strong></label>
                <select name="type" required style="width: 100%;">
                    <option value="IN" {{ old('type', request('type')) === 'IN' ? 'selected' : '' }}>Masuk (IN) &mdash; Tambah Stok</option>
                    <option value="OUT" {{ old('type', request('type')) === 'OUT' ? 'selected' : '' }}>Keluar (OUT) &mdash; Kurangi Stok</option>
                </select>
                @error('type') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div style="flex: 1;">
                <label><strong>Jumlah Unit *</strong></label>
                <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required style="width: 100%;">
                @error('quantity') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-bottom: 18px;">
            <label><strong>Keterangan / Nomor Dokumen / Alasan Mutasi (Opsional)</strong></label>
            <input type="text" name="note" value="{{ old('note') }}" placeholder="Contoh: Penerimaan PO-102 dari supplier, Penjualan invoice #551, barang rusak, dll." style="width: 100%;">
            @error('note') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="button button-success">
            Simpan Transaksi Stok
        </button>
    </form>
</section>

<!-- FILTER & RIWAYAT MUTASI STOK -->
<section class="panel">
    <h2>Riwayat Mutasi Transaksi Stok</h2>

    <form method="GET" action="{{ route('stock.index') }}" class="form-row" style="margin-bottom: 14px;">
        <div>
            <label style="font-size: 13px; font-weight: 600;">Jenis Mutasi</label>
            <select name="type" style="width: 100%;">
                <option value="">-- Semua Jenis --</option>
                <option value="IN" {{ request('type') === 'IN' ? 'selected' : '' }}>Masuk (IN)</option>
                <option value="OUT" {{ request('type') === 'OUT' ? 'selected' : '' }}>Keluar (OUT)</option>
            </select>
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Filter Produk</label>
            <select name="product_id" style="width: 100%;">
                <option value="">-- Semua Produk --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%;">
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%;">
        </div>

        <div style="display: flex; gap: 6px; align-self: flex-end; padding-bottom: 4px;">
            <button type="submit">Filter</button>
            <a href="{{ route('stock.index') }}" class="button button-secondary">Reset</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th style="width: 160px;">Waktu Transaksi</th>
                <th>Barang / Produk</th>
                <th style="text-align: center; width: 120px;">Tipe</th>
                <th style="text-align: right; width: 100px;">Jumlah</th>
                <th>Petugas</th>
                <th>Catatan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trx)
                <tr>
                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <code>{{ $trx->product->sku ?? '-' }}</code>
                        <strong>{{ $trx->product->name ?? 'Produk Terhapus' }}</strong>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $trx->type === 'IN' ? 'badge-in' : 'badge-out' }}">
                            {{ $trx->type === 'IN' ? 'Masuk (IN)' : 'Keluar (OUT)' }}
                        </span>
                    </td>
                    <td style="text-align: right; font-weight: bold; color: {{ $trx->type === 'IN' ? '#22543d' : '#9b2c2c' }};">
                        {{ $trx->type === 'IN' ? '+' : '-' }}{{ $trx->quantity }} unit
                    </td>
                    <td>{{ $trx->user->full_name ?? '-' }}</td>
                    <td style="color: #4a5568;">{{ $trx->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #a0aec0; padding: 25px;">
                        Belum ada riwayat transaksi mutasi stok yang tercatat.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-links">
            {{ $transactions->links() }}
        </div>
    </div>
</section>
@endsection
