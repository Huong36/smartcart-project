<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => ucfirst(fake()->words(3, true)),
            'price' => fake()->numberBetween(50000, 2000000),
            'stock' => fake()->numberBetween(1, 100),
            'image' => null,
            'description' => fake()->paragraph(),
        ];
    }
}
