@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
<h1>Manajemen Pengguna Sistem</h1>

@php
    $editUser = null;
    if (request('edit_id')) {
        $editUser = $users->firstWhere('id', request('edit_id'));
    }
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; align-items: start;">
    <!-- FORM TAMBAH / EDIT PENGGUNA -->
    <section class="panel">
        <h2>{{ $editUser ? 'Edit Pengguna: ' . $editUser->username : 'Tambah Pengguna Baru' }}</h2>

        <form method="POST" action="{{ $editUser ? route('users.update', $editUser) : route('users.store') }}">
            @csrf
            @if($editUser)
                @method('PUT')
            @endif

            <label style="display: block; margin-bottom: 12px;">
                <strong>Username *</strong>
                <input type="text" name="username" value="{{ old('username', $editUser->username ?? '') }}" {{ $editUser ? 'readonly' : 'required' }} style="width: 100%; {{ $editUser ? 'background:#edf2f7;' : '' }}" placeholder="username_login">
                @if($editUser)
                    <small>Username tidak dapat diubah setelah dibuat.</small>
                @endif
                @error('username') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 12px;">
                <strong>Nama Lengkap *</strong>
                <input type="text" name="full_name" value="{{ old('full_name', $editUser->full_name ?? '') }}" required placeholder="Nama lengkap staf / admin" style="width: 100%;">
                @error('full_name') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 12px;">
                <strong>Alamat Email (Opsional)</strong>
                <input type="email" name="email" value="{{ old('email', $editUser->email ?? '') }}" placeholder="user@perusahaan.com" style="width: 100%;">
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 12px;">
                <strong>Peran (Role) *</strong>
                <select name="role" required style="width: 100%;">
                    <option value="staff" {{ old('role', $editUser->role ?? '') === 'staff' ? 'selected' : '' }}>Staff Gudang (Operasional Mutasi &amp; Katalog)</option>
                    <option value="admin" {{ old('role', $editUser->role ?? '') === 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh &amp; Master Data)</option>
                </select>
                @error('role') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: block; margin-bottom: 16px;">
                <strong>{{ $editUser ? 'Ganti Password (Kosongkan jika tidak ingin diubah)' : 'Password Awal *' }}</strong>
                <input type="password" name="{{ $editUser ? 'new_password' : 'password' }}" {{ $editUser ? '' : 'required' }} minlength="6" placeholder="Minimal 6 karakter" style="width: 100%;">
                @error($editUser ? 'new_password' : 'password') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="button button-success">
                    {{ $editUser ? 'Simpan Perubahan' : 'Buat Akun Baru' }}
                </button>
                @if($editUser)
                    <a href="{{ route('users.index') }}" class="button button-secondary">Batal</a>
                @endif
            </div>
        </form>
    </section>

    <!-- TABEL DAFTAR PENGGUNA -->
    <section class="panel">
        <h2>Daftar Akun Pengguna</h2>
        <table>
            <thead>
                <tr>
                    <th>Username &amp; Nama</th>
                    <th style="text-align: center; width: 110px;">Peran</th>
                    <th style="text-align: center; width: 110px;">Jejak Audit</th>
                    <th style="text-align: center; width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <code>{{ $u->username }}</code>
                            @if($u->id === auth()->id())
                                <span class="badge" style="background:#feebc8;color:#7b341e;font-size:11px;">Anda</span>
                            @endif
                            <div style="font-weight: 600; margin-top: 2px;">{{ $u->full_name }}</div>
                            @if($u->email)
                                <div style="color: #718096; font-size: 12.5px;">{{ $u->email }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $u->role === 'admin' ? 'badge-admin' : 'badge-staff' }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span title="Jumlah transaksi stok yang dicatat user ini" style="font-size: 13px; color: #4a5568;">
                                {{ $u->stock_transactions_count }} transaksi
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('users.index', ['edit_id' => $u->id]) }}" class="button" style="padding: 3px 8px; font-size: 12px; background: #2b6cb0;">
                                Edit
                            </a>

                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" style="display: inline;" onsubmit="return confirm('Hapus akun pengguna \'{{ $u->username }}\'?');">
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
                            Belum ada akun pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
