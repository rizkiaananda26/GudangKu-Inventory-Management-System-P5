<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Awal (Sesuai P4: admin & staff)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator Gudang',
                'email' => 'admin@gudangku.test',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        $staff = User::firstOrCreate(
            ['username' => 'staff'],
            [
                'full_name' => 'Staff Operasional Gudang',
                'email' => 'staff@gudangku.test',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
            ]
        );

        // 2. Kategori Awal
        $categoriesData = [
            ['name' => 'Elektronik & Gadget', 'description' => 'Perangkat elektronik, aksesoris komputer dan gadget.'],
            ['name' => 'Alat Tulis Kantor', 'description' => 'Kebutuhan administrasi, kertas, pena, dan arsip kantor.'],
            ['name' => 'Perlengkapan Rumah Tangga', 'description' => 'Peralatan kebersihan dan perkakas rumah tangga.'],
            ['name' => 'Makanan & Minuman', 'description' => 'Produk konsumsi, bahan baku kering dan kemasan.'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Category::firstOrCreate(['name' => $data['name']], $data);
        }

        // 3. Supplier Awal
        $suppliersData = [
            [
                'name' => 'PT Sumber Elektronik Nusantara',
                'phone' => '0812-3456-7890',
                'email' => 'kontak@sumberelektronik.co.id',
                'address' => 'Jl. Industri Raya No. 45, Jakarta Barat',
            ],
            [
                'name' => 'CV ATK Makmur Sejahtera',
                'phone' => '0813-9876-5432',
                'email' => 'sales@atkmakmur.com',
                'address' => 'Kawasan Pergudangan Blok C3, Bandung',
            ],
            [
                'name' => 'PT Distribusi Logistik Utama',
                'phone' => '0821-1122-3344',
                'email' => 'cs@logistikutama.id',
                'address' => 'Jl. Rungkut Industri III No. 12, Surabaya',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $data) {
            $suppliers[] = Supplier::firstOrCreate(['name' => $data['name']], $data);
        }

        // 4. Produk Awal (Variasi Stok: Normal, Menipis <= 5, Habis = 0)
        $productsData = [
            [
                'sku' => 'ELK-001',
                'name' => 'Mouse Wireless Ergonomis',
                'category_id' => $categories[0]->id,
                'price' => 125000,
                'stock' => 25,
                'description' => 'Mouse wireless 2.4GHz dengan receiver USB nano dan silent click.',
                'suppliers' => [$suppliers[0]->id, $suppliers[2]->id],
            ],
            [
                'sku' => 'ELK-002',
                'name' => 'Keyboard Mechanical TKL',
                'category_id' => $categories[0]->id,
                'price' => 450000,
                'stock' => 4, // Stok menipis (<= 5)
                'description' => 'Keyboard mechanical layout 87 key switch merah dengan backlit LED.',
                'suppliers' => [$suppliers[0]->id],
            ],
            [
                'sku' => 'ATK-001',
                'name' => 'Kertas HVS A4 80gr (Rim)',
                'category_id' => $categories[1]->id,
                'price' => 52000,
                'stock' => 60,
                'description' => 'Kertas fotokopi putih derajat keputihan tinggi isi 500 lembar per rim.',
                'suppliers' => [$suppliers[1]->id, $suppliers[2]->id],
            ],
            [
                'sku' => 'ATK-002',
                'name' => 'Ballpoint Gel 0.5mm Hitam (Box)',
                'category_id' => $categories[1]->id,
                'price' => 38000,
                'stock' => 0, // Stok habis (= 0)
                'description' => 'Pulpen gel tinta hitam cepat kering, 1 pack isi 12 pcs.',
                'suppliers' => [$suppliers[1]->id],
            ],
            [
                'sku' => 'PRT-001',
                'name' => 'Lampu LED Bohlam 12 Watt',
                'category_id' => $categories[2]->id,
                'price' => 28000,
                'stock' => 18,
                'description' => 'Lampu LED hemat energi 6500K putih terang garansi 1 tahun.',
                'suppliers' => [$suppliers[0]->id, $suppliers[2]->id],
            ],
        ];

        foreach ($productsData as $item) {
            $supplierIds = $item['suppliers'];
            unset($item['suppliers']);

            $product = Product::firstOrCreate(['sku' => $item['sku']], $item);

            // Pasang relasi Many-to-Many ke Supplier
            $product->suppliers()->syncWithoutDetaching($supplierIds);

            // 5. Catat transaksi stok awal jika ada stok
            if ($product->stock > 0 && $product->stockTransactions()->count() === 0) {
                StockTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'type' => 'IN',
                    'quantity' => $product->stock,
                    'note' => 'Stok awal sistem (Seeder)',
                ]);
            }
        }
    }
}
