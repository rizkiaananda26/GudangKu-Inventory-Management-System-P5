<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-####'),
            'name' => fake()->words(3, true),
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'stock' => fake()->numberBetween(0, 100),
            'description' => fake()->sentence(),
        ];
    }
}
