<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_can_run_twice_without_duplicates_or_resetting_passwords(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $admin->update(['password' => 'changed-password']);
        $hash = $admin->fresh()->password;
        $this->seed();
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 30);
        $this->assertTrue($admin->isAdmin());
        $this->assertSame($hash, $admin->fresh()->password);
        $this->get('/')->assertOk()->assertSee('Sản phẩm mới');
    }

    public function test_all_order_relationships_and_snapshot_values_work(): void
    {
        [$category, $product] = $this->catalog();
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'total_amount' => 200000, 'shipping_address' => 'TP.HCM', 'phone' => '0901234567',
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 2, 'price' => 100000,
        ]);
        $product->update(['price' => 150000]);
        $this->assertTrue($product->category->is($category));
        $this->assertCount(1, $category->products);
        $this->assertTrue($order->user->is($user));
        $this->assertTrue($item->product->is($product));
        $this->assertTrue($item->order->is($order));
        $this->assertCount(1, $product->orderItems);
        $this->assertSame('100000.00', $item->fresh()->price);
        $this->assertSame('Chờ xác nhận', $order->status_label);
        $order->delete();
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        [$category] = $this->catalog();
        $this->expectException(QueryException::class);
        $category->delete();
    }

    public function test_invalid_product_foreign_key_is_rejected(): void
    {
        $this->expectException(QueryException::class);
        Product::create(['category_id' => 9999, 'name' => 'Invalid', 'price' => 100000, 'stock' => 1]);
    }

    public function test_product_in_an_order_cannot_be_deleted(): void
    {
        [, $product] = $this->catalog();
        $order = User::factory()->create()->orders()->create([
            'total_amount' => 100000, 'shipping_address' => 'TP.HCM', 'phone' => '0901234567',
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'price' => 100000, 'quantity' => 1]);
        $this->expectException(QueryException::class);
        $product->delete();
    }

    private function catalog(): array
    {
        $category = Category::create(['name' => 'Điện tử', 'slug' => 'dien-tu']);
        $product = $category->products()->create(['name' => 'Tai nghe', 'price' => 100000, 'stock' => 10]);

        return [$category, $product];
    }
}
