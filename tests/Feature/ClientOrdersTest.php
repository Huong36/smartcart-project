<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClientOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_paginates_eight_products_and_preserves_filters(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(10)->create(['category_id' => $category->id, 'name' => 'Áo mẫu']);
        Product::factory()->create(['name' => 'Khác']);
        $response = $this->get('/?category_id='.$category->id.'&sort=price_asc&q='.urlencode('Áo'));
        $response->assertOk()->assertViewHas('products', fn ($products) => $products->count() === 8
            && $products->total() === 10
            && str_contains($products->nextPageUrl(), 'sort=price_asc')
            && str_contains($products->nextPageUrl(), 'category_id='.$category->id));
        $this->get('/?category_id='.$category->id.'&page=2')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 2);
    }

    public function test_catalog_sorts_prices_and_handles_empty_results(): void
    {
        $low = Product::factory()->create(['price' => 100]);
        $high = Product::factory()->create(['price' => 500]);
        $this->get('/?sort=price_asc')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->first()->is($low));
        $this->get('/?sort=price_desc')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->first()->is($high));
        $this->get('/?q=no-matching-product')->assertOk()->assertSee('Không có sản phẩm phù hợp');
        $this->get('/?sort=invalid')->assertSessionHasErrors('sort');
    }

    public function test_product_detail_shows_stock_and_add_to_cart_and_missing_product_returns_404(): void
    {
        $product = Product::factory()->create(['stock' => 0, 'description' => '<script>alert(1)</script>']);
        $this->get('/products/'.$product->id)->assertOk()->assertSee($product->name)
            ->assertSee('Sản phẩm tạm hết hàng')->assertSee('disabled')
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/products/99999')->assertNotFound();
    }

    public function test_checkout_and_orders_require_login(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
        $this->post('/checkout', [])->assertRedirect('/login');
        $this->get('/orders')->assertRedirect('/login');
        $this->get('/orders/1')->assertRedirect('/login');
    }

    public function test_checkout_form_uses_current_database_price_and_session_token(): void
    {
        $product = Product::factory()->create(['price' => '125000.25', 'stock' => 3]);
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$product->id => 2]])
            ->get('/checkout')->assertOk()->assertSee('Thông tin giao hàng')
            ->assertViewHas('totalAmount', '250000.50')->assertSessionHas('checkout_token');
    }

    public function test_checkout_saves_order_and_price_snapshot_reduces_stock_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Sản phẩm đã mua', 'price' => '125000.25', 'stock' => 3]);
        $token = (string) Str::uuid();
        $this->actingAs($user)->withSession(['cart' => [$product->id => 2], 'checkout_token' => $token])
            ->post('/checkout', $this->checkoutData($token) + ['total_amount' => 1, 'price' => 1, 'user_id' => 999, 'status' => 'completed'])
            ->assertRedirect('/orders/'.Order::firstOrFail()->id)->assertSessionHas('order_success')
            ->assertSessionMissing('cart')->assertSessionMissing('checkout_token');
        $order = Order::firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('250000.50', $order->total_amount);
        $this->assertSame('pending', $order->status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'quantity' => 2, 'product_name' => 'Sản phẩm đã mua']);
        $product->update(['name' => 'Tên mới', 'price' => 999999]);
        $this->get('/orders/'.$order->id)->assertOk()->assertSee('Sản phẩm đã mua')->assertDontSee('Tên mới');
        $this->assertSame('125000.25', $order->items()->first()->price);
    }

    public function test_repeated_checkout_does_not_duplicate_order_or_clear_a_new_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $token = (string) Str::uuid();
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$product->id => 2], 'checkout_token' => $token])
            ->post('/checkout', $this->checkoutData($token))->assertRedirect();
        $this->withSession(['cart' => [$product->id => 1]])->post('/checkout', $this->checkoutData($token))
            ->assertRedirect('/orders/'.Order::firstOrFail()->id)->assertSessionHas('cart', [$product->id => 1]);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_rejects_wrong_token_invalid_shipping_and_empty_cart(): void
    {
        $this->actingAs(User::factory()->create())->get('/checkout')->assertRedirect('/cart')->assertSessionHasErrors('cart');
        $token = (string) Str::uuid();
        $this->withSession(['checkout_token' => $token])->post('/checkout', $this->checkoutData((string) Str::uuid()))
            ->assertSessionHasErrors('cart');
        $this->post('/checkout', ['shipping_address' => '', 'phone' => 'abc', 'checkout_token' => $token])
            ->assertSessionHasErrors(['shipping_address', 'phone']);
        $this->post('/checkout', $this->checkoutData($token))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_missing_products_and_bad_quantities_without_losing_cart(): void
    {
        $product = Product::factory()->create(['stock' => 2]);
        $this->actingAs(User::factory()->create());
        foreach ([[$product->id => 3], [$product->id => 0], [$product->id => -1], [$product->id => 'abc'], [99999 => 1]] as $cart) {
            $token = (string) Str::uuid();
            $this->withSession(['checkout_token' => $token, 'cart' => $cart])
                ->post('/checkout', $this->checkoutData($token))->assertSessionHasErrors('cart')
                ->assertSessionHas('cart', $cart);
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_checkout_rolls_back_order_items_and_stock_if_writing_an_item_fails(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $second = Product::factory()->create(['stock' => 5]);
        $token = (string) Str::uuid();
        $this->actingAs(User::factory()->create())->withSession([
            'checkout_token' => $token, 'cart' => [$product->id => 1, $second->id => 1],
        ]);
        OrderItem::creating(function (OrderItem $item) use ($second): void {
            if ($item->product_id === $second->id) {
                throw new \RuntimeException('Simulated item write failure');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->post('/checkout', $this->checkoutData($token));
            $this->fail('Expected an item write failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated item write failure', $exception->getMessage());
        } finally {
            OrderItem::flushEventListeners();
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(5, $second->fresh()->stock);
        $this->assertSame([$product->id => 1, $second->id => 1], session('cart'));
    }

    public function test_checkout_rejects_invalid_prices_and_totals_that_exceed_database_capacity(): void
    {
        $product = Product::factory()->create(['stock' => 999]);
        $this->actingAs(User::factory()->create());
        foreach (['0.00', '-0.25', '9999999999.99'] as $price) {
            $product->update(['price' => $price]);
            $token = (string) Str::uuid();
            $this->withSession(['cart' => [$product->id => 2], 'checkout_token' => $token])
                ->post('/checkout', $this->checkoutData($token))->assertSessionHasErrors('cart');
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(999, $product->fresh()->stock);
    }

    public function test_second_customer_cannot_buy_stock_already_purchased(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        $first = User::factory()->create();
        $second = User::factory()->create();
        foreach ([$first, $second] as $index => $user) {
            $token = (string) Str::uuid();
            $response = $this->actingAs($user)->withSession(['checkout_token' => $token, 'cart' => [$product->id => 1]])
                ->post('/checkout', $this->checkoutData($token));
            if ($index === 0) {
                $response->assertSessionHasNoErrors();
            } else {
                $response->assertSessionHasErrors('cart');
            }
        }
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_customers_only_see_their_own_orders_and_cannot_manage_admin_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->makeOrder($owner);
        $this->actingAs($other)->get('/orders')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->isEmpty());
        $this->get('/orders/'.$order->id)->assertNotFound();
        $this->get('/admin/orders')->assertForbidden();
        $this->patch('/admin/orders/'.$order->id, ['status' => 'completed'])->assertForbidden();
        $this->actingAs($owner)->get('/orders')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->first()->is($order));
        $this->get('/orders/'.$order->id)->assertOk();
    }

    public function test_admin_views_all_orders_filters_and_moves_status_forward_only(): void
    {
        $order = $this->makeOrder(User::factory()->create());
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        $this->get('/admin/orders')->assertOk()->assertSee($order->user->email);
        $this->get('/admin/orders/'.$order->id)->assertOk()->assertSee('Thông tin giao hàng');
        $this->patch('/admin/orders/'.$order->id, ['status' => 'completed'])->assertSessionHasErrors('status');
        $this->patch('/admin/orders/'.$order->id, ['status' => 'processing'])->assertRedirect('/admin/orders/'.$order->id);
        $this->assertSame('processing', $order->fresh()->status);
        $this->get('/admin/orders?status=completed')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->isEmpty());
        $this->patch('/admin/orders/'.$order->id, ['status' => 'completed'])->assertRedirect();
        $this->assertSame('completed', $order->fresh()->status);
        $this->patch('/admin/orders/'.$order->id, ['status' => 'processing'])->assertSessionHasErrors('status');
        $this->patch('/admin/orders/'.$order->id, ['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertSame('completed', $order->fresh()->status);
    }

    private function checkoutData(string $token): array
    {
        return ['shipping_address' => '123 Nguyễn Trãi, TP.HCM', 'phone' => '0901234567', 'note' => 'Gọi trước khi giao', 'checkout_token' => $token];
    }

    public function test_zero_keyword_is_filtered_and_preserved_with_category_sort_and_pagination(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(10)->create(['name' => 'Mẫu 0', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Không liên quan', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Mẫu 0 danh mục khác']);
        $this->get('/?q=0&category_id='.$category->id.'&sort=price_asc')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 10
                && $products->count() === 8
                && str_contains($products->nextPageUrl(), 'q=0')
                && str_contains($products->nextPageUrl(), 'sort=price_asc'));
        $this->get('/?q=0&category_id='.$category->id.'&sort=price_asc&page=2')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 2 && $products->total() === 10);
        $this->get('/?q=%200%20')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 11);
        $this->get('/?q=')->assertOk()->assertViewHas('products', fn ($products) => $products->total() === 12);
    }

    public function test_customer_cancels_own_pending_order_and_restores_each_product_quantity(): void
    {
        [$owner, $order, $first, $second] = $this->cancellableOrder();
        $this->actingAs($owner)->get('/orders/'.$order->id)->assertOk()->assertSee('Hủy đơn hàng');
        $this->patch('/orders/'.$order->id.'/cancel')->assertRedirect('/orders/'.$order->id)
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(7, $first->fresh()->stock);
        $this->assertSame(10, $second->fresh()->stock);
        $this->assertDatabaseCount('order_items', 2);
        $this->get('/orders/'.$order->id)->assertOk()->assertSee('Đã hủy')->assertDontSee('Hủy đơn hàng');
    }

    public function test_repeated_cancellation_by_customer_and_admin_does_not_restore_stock_twice(): void
    {
        [$owner, $order, $first, $second] = $this->cancellableOrder();
        $this->actingAs($owner)->patch('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->patch('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->patch('/admin/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(7, $first->fresh()->stock);
        $this->assertSame(10, $second->fresh()->stock);
    }

    public function test_admin_can_cancel_pending_or_processing_orders(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        foreach ([Order::STATUS_PENDING, Order::STATUS_PROCESSING] as $status) {
            [, $order, $first, $second] = $this->cancellableOrder($status);
            $this->actingAs($admin)->get('/admin/orders/'.$order->id)->assertOk()->assertSee('Hủy đơn hàng');
            $this->patch('/admin/orders/'.$order->id.'/cancel')->assertRedirect('/admin/orders/'.$order->id)
                ->assertSessionHasNoErrors();
            $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
            $this->assertSame(7, $first->fresh()->stock);
            $this->assertSame(10, $second->fresh()->stock);
        }
    }

    public function test_customer_cannot_cancel_processing_order_and_nobody_can_cancel_completed_order(): void
    {
        [$owner, $order, $first, $second] = $this->cancellableOrder(Order::STATUS_PROCESSING);
        $this->actingAs($owner)->get('/orders/'.$order->id)->assertOk()->assertDontSee('Hủy đơn hàng');
        $this->patch('/orders/'.$order->id.'/cancel')->assertSessionHasErrors('status');
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);
        $order->update(['status' => Order::STATUS_COMPLETED]);
        $this->patch('/orders/'.$order->id.'/cancel')->assertSessionHasErrors('status');
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->patch('/admin/orders/'.$order->id.'/cancel')->assertSessionHasErrors('status');
        $this->get('/admin/orders/'.$order->id)->assertOk()->assertDontSee('Hủy đơn hàng');
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(5, $first->fresh()->stock);
        $this->assertSame(7, $second->fresh()->stock);
    }

    public function test_cancellation_requires_login_ownership_and_admin_role(): void
    {
        [$owner, $order, $first] = $this->cancellableOrder();
        $this->patch('/orders/'.$order->id.'/cancel')->assertRedirect('/login');
        $this->patch('/admin/orders/'.$order->id.'/cancel')->assertRedirect('/login');
        $other = User::factory()->create();
        $this->actingAs($other)->patch('/orders/'.$order->id.'/cancel')->assertNotFound();
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/cancel')->assertForbidden();
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(5, $first->fresh()->stock);
    }

    public function test_cancelled_order_cannot_be_advanced_to_shipping_or_completion(): void
    {
        [$owner, $order, $first] = $this->cancellableOrder();
        $this->actingAs($owner)->patch('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        foreach ([Order::STATUS_PROCESSING, Order::STATUS_COMPLETED] as $status) {
            $this->patch('/admin/orders/'.$order->id, ['status' => $status])->assertSessionHasErrors('status');
        }
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(7, $first->fresh()->stock);
    }

    public function test_cancellation_rechecks_database_status_instead_of_a_stale_order_instance(): void
    {
        [$owner, $order, $first] = $this->cancellableOrder();
        Order::whereKey($order->id)->update(['status' => Order::STATUS_COMPLETED]);
        try {
            $order->cancel($owner);
            $this->fail('A stale pending order must not cancel a completed order.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame(5, $first->fresh()->stock);
    }

    public function test_cancellation_rolls_back_earlier_stock_changes_when_a_later_product_fails(): void
    {
        [$owner, $order, $first, $second] = $this->cancellableOrder();
        Product::updating(function (Product $product) use ($second): void {
            if ($product->id === $second->id) {
                throw new \RuntimeException('Simulated stock restoration failure');
            }
        });
        $this->actingAs($owner)->withoutExceptionHandling();
        try {
            $this->patch('/orders/'.$order->id.'/cancel');
            $this->fail('Expected a stock restoration failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated stock restoration failure', $exception->getMessage());
        } finally {
            Product::flushEventListeners();
        }
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(5, $first->fresh()->stock);
        $this->assertSame(7, $second->fresh()->stock);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_cancellation_rolls_back_all_stock_when_status_update_fails_and_can_be_retried(): void
    {
        [$owner, $order, $first, $second] = $this->cancellableOrder();
        Order::updating(function (Order $updating): void {
            if ($updating->status === Order::STATUS_CANCELLED) {
                throw new \RuntimeException('Simulated cancellation status failure');
            }
        });
        $this->actingAs($owner)->withoutExceptionHandling();
        try {
            $this->patch('/orders/'.$order->id.'/cancel');
            $this->fail('Expected a cancellation status failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated cancellation status failure', $exception->getMessage());
        } finally {
            Order::flushEventListeners();
        }
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(5, $first->fresh()->stock);
        $this->assertSame(7, $second->fresh()->stock);
        $this->patch('/orders/'.$order->id.'/cancel')->assertRedirect('/orders/'.$order->id);
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(7, $first->fresh()->stock);
        $this->assertSame(10, $second->fresh()->stock);
    }

    /** @return array{User, Order, Product, Product} */
    private function cancellableOrder(string $status = Order::STATUS_PENDING): array
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner);
        $order->update(['status' => $status]);
        $first = Product::factory()->create(['stock' => 5]);
        $second = Product::factory()->create(['stock' => 7]);
        foreach ([[$first, 2], [$second, 3]] as [$product, $quantity]) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ]);
        }

        return [$owner, $order, $first, $second];
    }

    private function makeOrder(User $user): Order
    {
        return $user->orders()->create([
            'shipping_address' => 'TP.HCM', 'phone' => '0901234567',
            'total_amount' => 100000, 'status' => Order::STATUS_PENDING,
        ]);
    }
}
