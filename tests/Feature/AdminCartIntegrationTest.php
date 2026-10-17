<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCartIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_changes_are_reflected_in_search_and_cart(): void
    {
        $category = Category::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $attributes = [
            'name' => 'Tai nghe tích hợp',
            'category_id' => $category->id,
            'price' => 150000,
            'stock' => 3,
        ];

        $this->actingAs($admin)->post(route('admin.products.store'), $attributes)
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', $attributes['name'])->firstOrFail();

        $this->getJson('/search/suggest?q=Tai')->assertOk()
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.stock', 3);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()->assertJsonPath('cart_count', 2);

        $this->put(route('admin.products.update', $product), array_replace($attributes, ['price' => 200000]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
        $this->getJson('/cart')->assertOk()->assertJsonPath('raw_total_price', 400000);

        $this->put(route('admin.products.update', $product), array_replace($attributes, ['stock' => 0]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
        $this->getJson('/search/suggest?q=Tai')->assertOk()
            ->assertJsonPath('data.0.stock', 0)->assertJsonPath('data.0.is_out_of_stock', true);
        $this->postJson('/cart/add', ['product_id' => $product->id])->assertUnprocessable()
            ->assertJsonPath('success', false);
        $this->patchJson('/cart/update', ['product_id' => $product->id, 'quantity' => 3])
            ->assertUnprocessable()->assertJsonPath('success', false);
        $this->getJson('/cart')->assertOk()->assertJsonPath('items.0.quantity', 2);
    }
}
