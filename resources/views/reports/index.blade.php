@extends('layouts.app')

@section('title', 'Laporan Inventaris')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h1>Laporan Inventaris Gudang</h1>
        <p style="margin-top: -14px; color: #78665b; font-size: 14px;">Rekapitulasi nilai aset persediaan barang dan arus mutasi gudang.</p>
    </div>
    <button onclick="window.print()" class="button no-print" style="background: #2b6cb0;">
        &fnof; Cetak Laporan (Print)
    </button>
</div>

<!-- ====================================================================== -->
<!-- BAGIAN 1: LAPORAN NILAI PERSEDIAAN BARANG                             -->
<!-- ====================================================================== -->
<section class="panel">
    <h2>1. Laporan Nilai Aset Persediaan Barang (Valuasi Stok)</h2>

    <form method="GET" action="{{ route('reports.index') }}" class="form-row no-print" style="margin-bottom: 14px;">
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
        <input type="hidden" name="date_to" value="{{ $dateTo }}">

        <div>
            <label style="font-size: 13px; font-weight: 600;">Filter Kategori</label>
            <select name="category_id" style="width: 100%;">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 6px; align-self: flex-end; padding-bottom: 4px;">
            <button type="submit">Filter Valuasi</button>
            <a href="{{ route('reports.index') }}" class="button button-secondary">Reset</a>
        </div>
    </form>

    <div class="grid" style="margin-bottom: 14px;">
        <section>
            <small>Jumlah Item Produk</small>
            <div class="stat">{{ $valuationProducts->count() }} <span style="font-size: 14px; font-weight: normal;">item</span></div>
        </section>
        <section>
            <small>Total Kuantitas Fisik</small>
            <div class="stat">{{ number_format($totalValuationUnits, 0, ',', '.') }} <span style="font-size: 14px; font-weight: normal;">unit</span></div>
        </section>
        <section style="grid-column: span 2;">
            <small>Total Estimasi Nilai Aset Persediaan</small>
            <div class="stat" style="color: #2b6cb0;">
                Rp {{ number_format($totalValuationAmount, 0, ',', '.') }}
            </div>
        </section>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 140px;">SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th style="text-align: center; width: 110px;">Stok Fisik</th>
                <th style="text-align: right; width: 140px;">Harga Satuan</th>
                <th style="text-align: right; width: 160px;">Total Nilai Aset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($valuationProducts as $vp)
                <tr>
                    <td><code>{{ $vp->sku }}</code></td>
                    <td><strong>{{ $vp->name }}</strong></td>
                    <td>{{ $vp->category->name ?? 'Tanpa kategori' }}</td>
                    <td style="text-align: center; font-weight: 600;">
                        {{ number_format($vp->stock, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right;">
                        Rp {{ number_format($vp->price, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #2b6cb0;">
                        Rp {{ number_format($vp->stock * $vp->price, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #a0aec0; padding: 20px;">
                        Tidak ada data produk.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($valuationProducts->isNotEmpty())
            <tfoot>
                <tr style="background: #f7f0e9; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">TOTAL PERSIAAN KESELURUHAN:</td>
                    <td style="text-align: center;">{{ number_format($totalValuationUnits, 0, ',', '.') }} unit</td>
                    <td></td>
                    <td style="text-align: right; color: #2b6cb0;">Rp {{ number_format($totalValuationAmount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</section>

<!-- ====================================================================== -->
<!-- BAGIAN 2: LAPORAN REKAPITULASI MUTASI STOK                            -->
<!-- ====================================================================== -->
<section class="panel">
    <h2>2. Laporan Rekapitulasi Mutasi Stok per Periode</h2>

    <form method="GET" action="{{ route('reports.index') }}" class="form-row no-print" style="margin-bottom: 14px;">
        <input type="hidden" name="category_id" value="{{ request('category_id') }}">

        <div>
            <label style="font-size: 13px; font-weight: 600;">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" required style="width: 100%;">
        </div>

        <div>
            <label style="font-size: 13px; font-weight: 600;">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" required style="width: 100%;">
        </div>

        <div style="display: flex; gap: 6px; align-self: flex-end; padding-bottom: 4px;">
            <button type="submit">Filter Periode</button>
            <a href="{{ route('reports.index') }}" class="button button-secondary">Bulan Ini</a>
        </div>
    </form>

    <div class="grid" style="margin-bottom: 14px;">
        <section>
            <small>Total Masuk Periode Ini</small>
            <div class="stat" style="color: #2f855a;">+{{ number_format($periodTotalIn, 0, ',', '.') }} <span style="font-size: 14px; font-weight: normal;">unit</span></div>
        </section>
        <section>
            <small>Total Keluar Periode Ini</small>
            <div class="stat" style="color: #c53030;">-{{ number_format($periodTotalOut, 0, ',', '.') }} <span style="font-size: 14px; font-weight: normal;">unit</span></div>
        </section>
        <section>
            <small>Arus Bersih (Net Mutasi)</small>
            <div class="stat" style="color: {{ $periodNet >= 0 ? '#2f855a' : '#c53030' }};">
                {{ $periodNet >= 0 ? '+' : '' }}{{ number_format($periodNet, 0, ',', '.') }} <span style="font-size: 14px; font-weight: normal;">unit</span>
            </div>
        </section>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 140px;">SKU</th>
                <th>Nama Produk</th>
                <th style="text-align: right; width: 140px;">Total Masuk (IN)</th>
                <th style="text-align: right; width: 140px;">Total Keluar (OUT)</th>
                <th style="text-align: right; width: 150px;">Selisih Net Periode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutationProducts as $mp)
                <tr>
                    <td><code>{{ $mp['sku'] }}</code></td>
                    <td><strong>{{ $mp['name'] }}</strong></td>
                    <td style="text-align: right; color: #2f855a; font-weight: 600;">
                        +{{ number_format($mp['total_in'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #c53030; font-weight: 600;">
                        -{{ number_format($mp['total_out'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold; color: {{ $mp['net'] >= 0 ? '#2f855a' : '#c53030' }};">
                        {{ $mp['net'] >= 0 ? '+' : '' }}{{ number_format($mp['net'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #a0aec0; padding: 20px;">
                        Tidak ada data produk.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($mutationProducts) > 0)
            <tfoot>
                <tr style="background: #f7f0e9; font-weight: bold;">
                    <td colspan="2" style="text-align: right;">TOTAL ARUS PERIODE:</td>
                    <td style="text-align: right; color: #2f855a;">+{{ number_format($periodTotalIn, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #c53030;">-{{ number_format($periodTotalOut, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: {{ $periodNet >= 0 ? '#2f855a' : '#c53030' }};">
                        {{ $periodNet >= 0 ? '+' : '' }}{{ number_format($periodNet, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</section>
@endsection
