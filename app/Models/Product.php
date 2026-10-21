<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'price',
        'stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Relasi ke kategori: satu produk dimiliki oleh satu kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke supplier: satu produk dapat memiliki banyak supplier (Many-to-Many)
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers');
    }

    /**
     * Relasi ke transaksi stok: riwayat perubahan stok produk ini
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Cek apakah stok menipis (<= 5)
     */
    public function isLowStock(): bool
    {
        return $this->stock <= 5 && $this->stock > 0;
    }

    /**
     * Cek apakah stok habis (0)
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }
}
