<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventaris') - GudangKu</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @auth
    <header class="no-print">
        <strong>GudangKu</strong>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Kategori</a>
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">Supplier</a>
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Produk</a>
            <a href="{{ route('stock.index') }}" class="{{ request()->routeIs('stock.*') ? 'active' : '' }}">Transaksi Stok</a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Laporan</a>
            
            @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Pengguna</a>
            @endif

            <span style="font-size:13px;color:#f1e6dd;margin-left:8px;padding-left:10px;border-left:1px solid rgba(255,255,255,0.3);">
                {{ auth()->user()->full_name }}
                <span class="badge {{ auth()->user()->isAdmin() ? 'badge-admin' : 'badge-staff' }}" style="margin-left:4px;">
                    {{ auth()->user()->role }}
                </span>
            </span>

            <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;">
                @csrf
                <button type="submit" style="background:transparent;border:1px solid rgba(255,255,255,0.4);color:#f1e6dd;padding:5px 10px;font-size:13px;border-radius:5px;cursor:pointer;">
                    Keluar
                </button>
            </form>
        </nav>
    </header>
    @endauth

    <main>
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif

        @if(session('info'))
            <x-alert type="info">{{ session('info') }}</x-alert>
        @endif

        @if(session('warning'))
            <x-alert type="warning">{{ session('warning') }}</x-alert>
        @endif

        @if($errors->any() && !request()->routeIs('login'))
            <x-alert type="error">
                <strong>Terdapat beberapa kesalahan pengisian formulir:</strong>
                <ul style="margin:6px 0 0 18px;padding:0;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @yield('content')
    </main>

    <footer class="no-print">
        GudangKu &middot; Mini Project Inventory Management System (Laravel MVC)
    </footer>
</body>
</html>
