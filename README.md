# GudangKu — Inventory Management System (Laravel Edition)
> **Project 5 Pemrograman Web &middot; Migrasi PHP Native ke Laravel Application**  
> Mengimplementasikan Arsitektur MVC, Eloquent ORM, Migration, Seeder, Blade Template, dan Business Rules Inventaris.

---

## Daftar Isi
1. [Tentang Project](#tentang-project)
2. [Fitur Utama & Business Rules](#fitur-utama--business-rules)
3. [Arsitektur MVC & Alur Data](#arsitektur-mvc--alur-data)
4. [Demonstrasi Alur Lengkap (Form &rarr; Validasi &rarr; Simpan &rarr; Redirect)](#demonstrasi-alur-lengkap)
5. [Struktur Database & Relasi](#struktur-database--relasi)
6. [Panduan Instalasi & Menjalankan](#panduan-instalasi--menjalankan)
7. [Akun Pengujian Awal (Seeder)](#akun-pengujian-awal-seeder)
8. [Daftar Rute (Route List)](#daftar-rute-route-list)
9. [Dokumentasi Tangkapan Layar (Screenshots)](#dokumentasi-tangkapan-layar-screenshots)
10. [Checklist Pemenuhan Syarat Pertemuan 5](#checklist-pemenuhan-syarat-pertemuan-5)

---

## Tentang Project

Project ini merupakan hasil migrasi dan refaktorisasi dari **Project 4 (PHP Native, PDO, MySQL)** ke dalam ekosistem framework modern **Laravel (v12)** dengan PHP 8.2+. Seluruh rancangan relasi database, aturan bisnis (*business rules*), hak akses peran (*roles*), dan logika integritas data dari Project 4 tetap dipertahankan dan disempurnakan menggunakan standar arsitektur Laravel:

* **Separation of Concerns:** Memisahkan lapisan data (Model/Eloquent), logika proses (Controller), dan antarmuka pengguna (Blade View).
* **Robust Database Management:** Menggantikan query DDL manual dengan Database Migrations dan Seeders.
* **Form & Data Security:** Perlindungan CSRF token otomatis (`@csrf`), sanitasi output HTML Blade (`{{ }}`), validasi input terstruktur (*Server-side Validation*), serta pencegahan *race-condition* stok melalui *Pessimistic Locking* & transaksi atomik (`DB::transaction`).

---

## Fitur Utama & Business Rules

1. **Autentikasi & Otorisasi Pengguna:**
   * Login berbasis `username` dan `password` menggunakan `Auth::attempt()`.
   * Dua peran pengguna: **Administrator** (akses penuh ke master data dan pengguna) dan **Staff Gudang** (operasional transaksi dan katalog).
   * Middleware `AdminMiddleware` memblokir hak akses staff pada menu manajemen pengguna (HTTP 403).
2. **Katalog & Master Produk:**
   * Validasi SKU unik di seluruh sistem.
   * Relasi *Many-to-Many* ke Supplier (satu barang bisa dipasok oleh banyak supplier).
   * Pencarian fleksibel (*search*) berdasarkan nama atau kode SKU.
   * Filter kategori dan filter status stok (Tersedia, Menipis $\le 5$, Habis $= 0$).
   * Sorting tabel dinamis (SKU, nama, harga, stok) dan pagination (3, 5, 10, 25 baris) dengan parameter query string tetap dipertahankan (`withQueryString()`).
3. **Pencatatan Transaksi Stok Atomik:**
   * Mutasi stok Masuk (`IN`) dan Keluar (`OUT`).
   * Transaksi atomik dengan `DB::transaction()` dan `lockForUpdate()` mencegah stok minus.
   * Transaksi `OUT` yang melebihi kuantitas sisa stok ditolak secara aman oleh validasi.
4. **Proteksi Integritas Data:**
   * Produk atau Pengguna yang telah memiliki riwayat mutasi stok dilindungi dari penghapusan demi menjaga audit log gudang.
   * Menghapus kategori tidak menghapus produk terkait (mengubahnya menjadi berstatus tanpa kategori / `nullOnDelete`).
5. **Laporan Inventaris Lengkap:**
   * Laporan Valuasi Nilai Persediaan Barang (perhitungan kuantitas $\times$ harga satuan) dengan filter kategori.
   * Laporan Rekapitulasi Mutasi Stok per rentang tanggal (`date_from` s.d. `date_to`) menghitung total masuk, keluar, dan arus bersih (*net flow*).
   * Dilengkapi fitur cetak laporan (*Print Stylesheet* siap pakai).

---

## Arsitektur MVC & Alur Data

Sesuai capaian pembelajaran Pertemuan 5, aplikasi diorganisasikan dengan alur:  
**Request &rarr; Route &rarr; Controller &rarr; Model &rarr; Database &rarr; Response**

```
HTTP Request (Browser)
       │
       ▼
routes/web.php (Routing & Middleware)
       │
       ▼
app/Http/Controllers/* (Controller Logics)
       │
       ├────────────────────────┐
       ▼                        ▼
app/Models/* (Eloquent)    resources/views/* (Blade)
       │                        │
       ▼                        ▼
MySQL Database             HTTP Response (HTML/Redirect)
```

### Pembagian Tanggung Jawab Komponen:
1. **Route (`routes/web.php`):** Menerima HTTP Request, menerapkan filter middleware (`guest`, `auth`, `admin`), dan mengarahkan ke Controller yang bersangkutan menggunakan pola RESTful Resource Route (`Route::resource`).
2. **Controller (`app/Http/Controllers/`):** Bertindak sebagai koordinator proses. Menerima input dari `Request`, memvalidasi data form, memanggil Eloquent Model / Database Transaction, dan menentukan bentuk respon (`view()` atau `redirect()->with()`).
3. **Model (`app/Models/`):** Merepresentasikan tabel database (`Product`, `Category`, `Supplier`, `StockTransaction`, `User`). Mengatur proteksi *mass-assignment* (`$fillable`), *casting* tipe data, dan mendefinisikan relasi antar-tabel (`belongsTo`, `hasMany`, `belongsToMany`).
4. **View (`resources/views/`):** Menyajikan tampilan HTML ke browser menggunakan Blade Template Engine dengan layout induk (`layouts/app.blade.php`), komponen reusable (`components/alert.blade.php`), escaping output aman (`{{ }}`), directive pengkondisian (`@if`, `@forelse`), serta penanganan error form (`@error`).

---

## Demonstrasi Alur Lengkap

Berikut adalah demonstrasi alur nyata satu siklus: **Buka Form &rarr; Validasi &rarr; Simpan &rarr; Redirect &rarr; Data Tampil**:

### Contoh: Penambahan Produk Baru (`ProductController`)
1. **Membuka Form:**  
   Pengguna menekan tombol `+ Tambah Produk Baru` &rarr; Request `GET /products/create` diarahkan ke method `ProductController@create` &rarr; Controller mengambil data referensi kategori dan supplier, lalu merender view `products.create`.
2. **Validasi Input:**  
   Pengguna mengirimkan data form via `POST /products`. Method `ProductController@store` mengeksekusi aturan validasi:
   ```php
   $validated = $request->validate([
       'sku'          => ['required', 'string', 'max:50', 'unique:products,sku'],
       'name'         => ['required', 'string', 'max:150'],
       'category_id'  => ['nullable', 'exists:categories,id'],
       'price'        => ['required', 'numeric', 'min:0'],
       'supplier_ids' => ['nullable', 'array'],
       'supplier_ids.*' => ['exists:suppliers,id'],
   ]);
   ```
   *Jika validasi gagal (misal: SKU sudah ada atau harga minus):* Laravel otomatis mengembalikan user ke halaman form (`back()->withErrors()->withInput()`) dan menampilkan pesan error merah di bawah input terkait.
3. **Penyimpanan Database:**  
   Jika validasi lolos, Controller menjalankan transaksi database atomik:
   ```php
   DB::transaction(function () use ($validated) {
       $product = Product::create([...]);
       $product->suppliers()->sync($validated['supplier_ids'] ?? []);
   });
   ```
4. **Redirect dengan Flash Message:**  
   Controller mengirimkan HTTP Redirect 302 kembali ke katalog produk dengan pesan sukses:
   ```php
   return redirect()->route('products.index')
       ->with('success', 'Produk "' . $validated['name'] . '" berhasil ditambahkan.');
   ```
5. **Data Tampil di Katalog:**  
   Browser memuat halaman `GET /products`. Komponen `<x-alert type="success">` menampilkan notifikasi hijau, dan baris produk baru langsung muncul di tabel katalog.

---

## Struktur Database & Relasi

Aplikasi menggunakan database MySQL bernama **`gudangku_laravel`** dengan tabel-tabel utama:

| Tabel | Keterangan | Relasi Eloquent |
| :--- | :--- | :--- |
| `users` | Akun pengguna (`admin` & `staff`) | `hasMany(StockTransaction)` |
| `categories` | Pengelompokan jenis barang | `hasMany(Product)` |
| `suppliers` | Data perusahaan pemasok | `belongsToMany(Product, 'product_suppliers')` |
| `products` | Master barang gudang | `belongsTo(Category)`, `belongsToMany(Supplier)`, `hasMany(StockTransaction)` |
| `product_suppliers` | Tabel pivot relasi *Many-to-Many* | Composite PK (`product_id`, `supplier_id`) |
| `stock_transactions`| Riwayat mutasi masuk/keluar | `belongsTo(Product)`, `belongsTo(User)` |

---

## Panduan Instalasi & Menjalankan

Ikuti langkah-langkah berikut untuk menjalankan project di komputer baru:

### 1. Prasyarat Sistem
* XAMPP (Apache & MySQL aktif)
* PHP $\ge 8.2$
* Composer $\ge 2.0$

### 2. Langkah Setup
1. Pastikan servis **MySQL** sudah berjalan di XAMPP Control Panel.
2. Buka terminal (PowerShell / Command Prompt) dan arahkan ke folder project:
   ```powershell
   cd c:\xampp\htdocs\GudangKu_Inventory_Management_System\inventory-laravel
   ```
3. Salin file environment:
   ```powershell
   copy .env.example .env
   ```
4. Pastikan file `.env` memiliki pengaturan koneksi MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gudangku_laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. Install dependensi (jika folder vendor belum ada):
   ```powershell
   composer install
   ```
6. Generate application key:
   ```powershell
   php artisan key:generate
   ```
7. Jalankan migrasi dan seeder data awal:
   ```powershell
   php artisan migrate --seed
   ```
   *(Atau `php artisan migrate:fresh --seed` jika ingin membuat ulang database dari nol).*
8. Jalankan development server Laravel:
   ```powershell
   php artisan serve
   ```
9. Buka browser di URL:  
   **`http://127.0.0.1:8000`**

---

## Akun Pengujian Awal (Seeder)

Database seeder telah menyediakan dua akun bawaan untuk pengujian:

| Peran | Username | Password | Deskripsi Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Akses penuh: kelola produk, transaksi stok, laporan, kategori, supplier, dan manajemen pengguna. |
| **Staff Gudang** | `staff` | `staff123` | Akses operasional: melihat katalog, menambah/edit produk, mencatat mutasi stok, dan laporan. Dibatasi dari menu pengguna dan tombol hapus. |

---

## Daftar Rute (Route List)

Seluruh 30 rute aplikasi terdaftar dan dikelompokkan dengan rapi:

```
+-------------+--------------------------------+------------------------------------------------+
| Method      | URI                            | Name & Controller Action                       |
+-------------+--------------------------------+------------------------------------------------+
| GET|HEAD    | login                          | login › AuthController@showLoginForm          |
| POST        | login                          | login.post › AuthController@login              |
| POST        | logout                         | logout › AuthController@logout                 |
| GET|HEAD    | /                              | dashboard › DashboardController@index          |
| GET|HEAD    | dashboard                      | DashboardController@index                      |
| GET|HEAD    | products                       | products.index › ProductController@index       |
| GET|HEAD    | products/create                | products.create › ProductController@create     |
| POST        | products                       | products.store › ProductController@store       |
| GET|HEAD    | products/{product}             | products.show › ProductController@show         |
| GET|HEAD    | products/{product}/edit        | products.edit › ProductController@edit         |
| PUT|PATCH   | products/{product}             | products.update › ProductController@update     |
| DELETE      | products/{product}             | products.destroy › ProductController@destroy   |
| GET|HEAD    | categories                     | categories.index › CategoryController@index   |
| POST        | categories                     | categories.store › CategoryController@store   |
| PUT|PATCH   | categories/{category}          | categories.update › CategoryController@update  |
| DELETE      | categories/{category}          | categories.destroy › CategoryController@destroy|
| GET|HEAD    | suppliers                      | suppliers.index › SupplierController@index     |
| POST        | suppliers                      | suppliers.store › SupplierController@store     |
| PUT|PATCH   | suppliers/{supplier}           | suppliers.update › SupplierController@update   |
| DELETE      | suppliers/{supplier}           | suppliers.destroy › SupplierController@destroy |
| GET|HEAD    | stock                          | stock.index › StockTransactionController@index |
| POST        | stock                          | stock.store › StockTransactionController@store |
| GET|HEAD    | reports                        | reports.index › ReportController@index         |
| GET|HEAD    | users                          | users.index › UserController@index (Admin Only)|
| POST        | users                          | users.store › UserController@store (Admin Only)|
| PUT|PATCH   | users/{user}                   | users.update › UserController@update           |
| DELETE      | users/{user}                   | users.destroy › UserController@destroy         |
+-------------+--------------------------------+------------------------------------------------+
```

---

## Dokumentasi Tangkapan Layar (Screenshots)

Seluruh tangkapan layar antarmuka telah didokumentasikan dan tersimpan pada folder [`docs/screenshots/`](file:///c:/xampp/htdocs/GudangKu_Inventory_Management_System/inventory-laravel/docs/screenshots/):

1. **`01_login.png`** — Form autentikasi pengguna dengan info akun pengujian.
2. **`02_dashboard.png`** — Ringkasan 4 kartu statistik utama, aksi cepat, dan tabel peringatan stok menipis/habis ($\le 5$).
3. **`03_daftar_produk.png`** — Katalog produk dengan pencarian, filter kategori/stok, sorting kolom, dan pagination.
4. **`04_form_tambah_produk.png`** — Form input produk baru dengan penugasan multi-supplier (pivot M:M).
5. **`05_detail_produk.png`** — Informasi lengkap spesifikasi barang, daftar supplier pemasok, dan riwayat mutasi stok produk.
6. **`06_kelola_kategori.png`** — Form dan daftar master kategori barang dengan jumlah item terkait.
7. **`07_kelola_supplier.png`** — Form dan daftar data kontak & alamat supplier.
8. **`08_transaksi_stok.png`** — Form mutasi stok masuk/keluar atomik dan tabel riwayat transaksi mutasi.
9. **`09_laporan_inventaris.png`** — Laporan valuasi nilai aset persediaan barang dan rekapitulasi mutasi periode (print ready).
10. **`10_kelola_pengguna.png`** — Manajemen akun pengguna dan peran sistem (khusus admin).

---

## Checklist Pemenuhan Syarat Pertemuan 5

- [x] **Environment & Konfigurasi:** Composer terkonfigurasi, `.env` terpisah aman, `.env.example` tersedia, Artisan berfungsi normal.
- [x] **Arsitektur MVC:** Request &rarr; Route &rarr; Controller &rarr; Model &rarr; Database &rarr; Response terimplementasi bersih.
- [x] **Routing & Controller:** Named routes, resource controller, middleware grouping (`auth` & `admin`).
- [x] **Blade Templates:** Master layout (`layouts/app`), component alert (`components/alert`), form CSRF (`@csrf`), method spoofing (`@method`), escaping output, dan pagination links.
- [x] **Database & Migrations:** 6 migration terstruktur (`users`, `categories`, `suppliers`, `products`, `product_suppliers`, `stock_transactions`).
- [x] **Model & Eloquent:** `$fillable` mass-assignment protection, relasi `belongsTo`, `hasMany`, dan `belongsToMany`.
- [x] **Factory & Seeder:** `DatabaseSeeder`, `ProductFactory`, `CategoryFactory`, `SupplierFactory`, `UserFactory` siap uji.
- [x] **Business Rules P4:** SKU unik, stok tidak pernah minus, multi-supplier pivot, pencarian, filter status stok, sorting, pagination, proteksi hapus jika ada riwayat transaksi.
- [x] **Uji Otomatis:** Test suite feature integration lulus 100% (`9 passed, 41 assertions`).
- [x] **Dokumentasi & Deliverables:** README instalasi lengkap, panduan akun uji, route list, screenshot antarmuka, dan penjelasan alur lengkap.
