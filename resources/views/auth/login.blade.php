@extends('layouts.app')

@section('title', 'Masuk Sistem')

@section('content')
<section class="panel" style="max-width: 440px; margin: 50px auto;">
    <h2 style="margin-top: 0; color: #543924; text-align: center;">Masuk Sistem GudangKu</h2>
    <p style="color: #78665b; margin-bottom: 20px; text-align: center; font-size: 14px;">
        Silakan login untuk mengelola sistem inventaris GudangKu.
    </p>

    @if ($errors->has('username'))
        <x-alert type="error">
            {{ $errors->first('username') }}
        </x-alert>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <label style="display: block; margin-bottom: 12px;">
            <strong>Username</strong>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" style="width: 100%; margin-top: 4px;" placeholder="Masukkan username">
        </label>

        <label style="display: block; margin-bottom: 14px;">
            <strong>Password</strong>
            <input type="password" name="password" required autocomplete="current-password" style="width: 100%; margin-top: 4px;" placeholder="Masukkan password">
        </label>

        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-size: 13.5px; cursor: pointer;">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} style="margin: 0;">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" style="width: 100%; padding: 11px; font-weight: bold;">
            Masuk ke Aplikasi
        </button>
    </form>

    <div style="margin-top: 24px; padding-top: 14px; border-top: 1px dashed #d5c4b8; font-size: 13px; color: #78665b; text-align: center; line-height: 1.6;">
        <strong>Akun Default Pengujian:</strong><br>
        Admin: <code>admin</code> &middot; Password: <code>admin123</code><br>
        Staff: <code>staff</code> &middot; Password: <code>staff123</code>
    </div>
</section>
@endsection
