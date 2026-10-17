<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_index_returns_html_view_for_browser_requests(): void
    {
        $response = $this->get('/cart');

        $response->assertOk();
        $response->assertViewIs('cart.index');
        $response->assertSee('Giỏ hàng của bạn');
    }

    public function test_cart_index_returns_json_when_json_is_accepted(): void
    {
        $category = Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);
        $product = $category->products()->create([
            'name' => 'Sản phẩm Test',
            'price' => 150000,
            'stock' => 10,
        ]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->getJson('/cart')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'total_qty' => 2,
                'total_price' => '300.000 ₫',
                'raw_total_price' => 300000,
                'is_empty' => false,
            ])
            ->assertJsonPath('items.0.product_id', $product->id)
            ->assertJsonPath('items.0.quantity', 2)
            ->assertJsonPath('items.0.subtotal', 300000);
    }

    public function test_cart_data_endpoint_returns_json_payload(): void
    {
        $category = Category::create(['name' => 'Công nghệ', 'slug' => 'cong-nghe']);
        $product = $category->products()->create([
            'name' => 'Sản phẩm Alpha',
            'price' => 50000,
            'stock' => 5,
        ]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->get('/cart/data')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'total_qty' => 1,
                'total_price' => '50.000 ₫',
                'is_empty' => false,
            ]);
    }

    public function test_cart_synchronization_three_cases(): void
    {
        $category = Category::create(['name' => 'Gia dụng', 'slug' => 'gia-dung']);
        $product1 = $category->products()->create([
            'name' => 'Áo Thun Nam',
            'price' => 100000,
            'stock' => 10,
        ]);
        $product2 = $category->products()->create([
            'name' => 'Quần Jean Nam',
            'price' => 200000,
            'stock' => 5,
        ]);

        // Case 1: Giỏ hàng trống -> thêm sản phẩm từ Live Search / client
        $addRes1 = $this->postJson('/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);
        $addRes1->assertOk()->assertJson(['success' => true, 'cart_count' => 1]);

        $syncRes1 = $this->getJson('/cart');
        $syncRes1->assertOk()->assertJson([
            'success' => true,
            'total_qty' => 1,
            'total_price' => '100.000 ₫',
            'is_empty' => false,
        ]);
        $this->assertCount(1, $syncRes1->json('items'));

        // Case 2: Giỏ có hàng -> thêm sản phẩm mới
        $addRes2 = $this->postJson('/cart/add', [
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
        $addRes2->assertOk()->assertJson(['success' => true, 'cart_count' => 2]);

        $syncRes2 = $this->getJson('/cart');
        $syncRes2->assertOk()->assertJson([
            'success' => true,
            'total_qty' => 2,
            'total_price' => '300.000 ₫',
            'is_empty' => false,
        ]);
        $this->assertCount(2, $syncRes2->json('items'));

        // Case 3: Giỏ có hàng -> thêm tiếp sản phẩm đã có (product1)
        $addRes3 = $this->postJson('/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        $addRes3->assertOk()->assertJson(['success' => true, 'cart_count' => 4]);

        $syncRes3 = $this->getJson('/cart');
        $syncRes3->assertOk()->assertJson([
            'success' => true,
            'total_qty' => 4,
            'total_price' => '500.000 ₫',
            'is_empty' => false,
        ]);
        $this->assertCount(2, $syncRes3->json('items'));
    }

    public function test_live_search_suggest_handles_short_and_valid_queries(): void
    {
        $category = Category::create(['name' => 'Phụ kiện', 'slug' => 'phu-kien']);
        $category->products()->create([
            'name' => 'Bàn phím cơ RGB',
            'price' => 850000,
            'stock' => 10,
        ]);

        // Query dưới 2 ký tự trả về danh sách rỗng
        $shortRes = $this->getJson('/search/suggest?q=a');
        $shortRes->assertOk()->assertJson([
            'success' => true,
            'data' => [],
            'total' => 0,
        ]);

        // Query hợp lệ trả về kết quả
        $validRes = $this->getJson('/search/suggest?q=B%C3%A0n+ph%C3%ADm');
        $validRes->assertOk()->assertJson([
            'success' => true,
            'total' => 1,
        ]);
        $this->assertSame('Bàn phím cơ RGB', $validRes->json('data.0.name'));
    }

    public function test_product_name_with_html_characters_stored_and_rendered_properly(): void
    {
        $category = Category::create(['name' => 'Thời trang đặc biệt', 'slug' => 'thoi-trang-dac-biet']);
        $xssName = '<img src=x onerror=alert(1)> Áo Phông <b>Đẹp</b>';
        $product = $category->products()->create([
            'name' => $xssName,
            'price' => 120000,
            'stock' => 5,
        ]);

        $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk()->assertJson([
            'success' => true,
            'message' => 'Đã thêm "'.$xssName.'" vào giỏ hàng!',
        ]);

        $syncRes = $this->getJson('/cart');
        $syncRes->assertOk();
        $this->assertSame($xssName, $syncRes->json('items.0.name'));

        // Kiểm tra xem trang blade có escape an toàn không
        $viewRes = $this->get('/cart');
        $viewRes->assertOk();
        $viewRes->assertDontSee($xssName, false); // Không chứa HTML thô chưa escape
        $viewRes->assertSee(e($xssName), false); // Đã được escape an toàn
    }
}
