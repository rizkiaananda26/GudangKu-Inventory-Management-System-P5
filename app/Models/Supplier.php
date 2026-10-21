<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
    ];

    /**
     * Relasi ke produk: satu supplier dapat memasok banyak produk (Many-to-Many)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_suppliers');
    }
}
