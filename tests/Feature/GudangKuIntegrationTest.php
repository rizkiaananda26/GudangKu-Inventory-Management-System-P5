<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GudangKuIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
        $this->staff = User::where('username', 'staff')->first();
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_success_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_can_access_all_pages()
    {
        $this->actingAs($this->admin);

        $this->get('/')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/products/create')->assertStatus(200);
        $this->get('/categories')->assertStatus(200);
        $this->get('/suppliers')->assertStatus(200);
        $this->get('/stock')->assertStatus(200);
        $this->get('/reports')->assertStatus(200);
        $this->get('/users')->assertStatus(200);
    }

    public function test_staff_cannot_access_user_management()
    {
        $this->actingAs($this->staff);

        $this->get('/')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/stock')->assertStatus(200);
        $this->get('/reports')->assertStatus(200);

        // Staff harus diblokir (403) saat mencoba mengakses /users
        $response = $this->get('/users');
        $response->assertStatus(403);
    }

    public function test_product_creation_and_business_rules()
    {
        $this->actingAs($this->admin);

        $cat = Category::first();
        $sup = Supplier::first();

        $response = $this->post('/products', [
            'sku'          => 'TEST-SKU-999',
            'name'         => 'Barang Uji Coba Integrasi',
            'category_id'  => $cat->id,
            'price'        => 75000,
            'description'  => 'Deskripsi uji integrasi',
            'supplier_ids' => [$sup->id],
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['sku' => 'TEST-SKU-999']);

        $product = Product::where('sku', 'TEST-SKU-999')->first();
        $this->assertEquals(0, $product->stock); // Stok awal wajib 0
        $this->assertTrue($product->suppliers->contains($sup->id)); // Pivot supplier terhubung
    }

    public function test_stock_transaction_atomicity_and_limit()
    {
        $this->actingAs($this->staff);

        $product = Product::create([
            'sku'   => 'TRX-TEST-001',
            'name'  => 'Produk Uji Transaksi',
            'price' => 50000,
            'stock' => 10,
        ]);

        // 1. Mutasi IN: 5 unit
        $this->post('/stock', [
            'product_id' => $product->id,
            'type'       => 'IN',
            'quantity'   => 5,
            'note'       => 'Restock tambahan',
        ])->assertRedirect(route('stock.index'));

        $product->refresh();
        $this->assertEquals(15, $product->stock);

        // 2. Mutasi OUT: 7 unit
        $this->post('/stock', [
            'product_id' => $product->id,
            'type'       => 'OUT',
            'quantity'   => 7,
            'note'       => 'Pengeluaran uji',
        ])->assertRedirect(route('stock.index'));

        $product->refresh();
        $this->assertEquals(8, $product->stock);

        // 3. Mutasi OUT melebihi stok (minta 100 saat sisa 8) -> Wajib Ditolak!
        $response = $this->post('/stock', [
            'product_id' => $product->id,
            'type'       => 'OUT',
            'quantity'   => 100,
            'note'       => 'Pengeluaran ilegal melebihi stok',
        ]);

        $response->assertSessionHasErrors('quantity');
        $product->refresh();
        $this->assertEquals(8, $product->stock); // Stok tetap aman 8, tidak minus!
    }

    public function test_product_with_stock_transactions_cannot_be_deleted()
    {
        $this->actingAs($this->admin);

        // Ambil produk yang sudah punya riwayat transaksi dari seeder
        $product = Product::whereHas('stockTransactions')->first();

        $response = $this->delete("/products/{$product->id}");
        $response->assertRedirect('/products');
        $response->assertSessionHas('error');

        // Pastikan produk tetap ada di database (tidak terhapus)
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
