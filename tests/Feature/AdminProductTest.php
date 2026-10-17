<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function customerUser(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_guest_and_customer_cannot_access_products(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));

        $this->actingAs($this->customerUser())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_product_list_and_create_page(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create(['name' => 'Công nghệ']);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Bàn phím cơ RGB',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Bàn phím cơ RGB')
            ->assertSee('Công nghệ');

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Thêm Sản phẩm mới');
    }

    public function test_product_validation_enforces_rules(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        // Name missing, price <= 0, stock < 0
        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => '',
            'category_id' => $category->id,
            'price' => 0,
            'stock' => -1,
        ]);

        $response->assertSessionHasErrors(['name', 'price', 'stock']);

        // Stock non-integer is rejected
        $responseDecimal = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Sản phẩm số lượng lẻ',
            'category_id' => $category->id,
            'price' => 100000,
            'stock' => 2.5,
        ]);
        $responseDecimal->assertSessionHasErrors(['stock']);

        // Invalid image file format (txt pretending to be image)
        $fakeFile = UploadedFile::fake()->create('document.pdf', 100);
        $responseImage = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Sản phẩm lỗi ảnh',
            'category_id' => $category->id,
            'price' => 100000,
            'stock' => 5,
            'image' => $fakeFile,
        ]);

        $responseImage->assertSessionHasErrors(['image']);
    }

    public function test_admin_can_create_product_with_image_upload(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('product.webp', 400, 400);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Tai nghe Sony',
            'category_id' => $category->id,
            'price' => 590000,
            'stock' => 15,
            'image' => $image,
            'description' => 'Mô tả tai nghe',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Tai nghe Sony',
            'category_id' => $category->id,
            'price' => 590000,
            'stock' => 15,
        ]);

        $product = Product::where('name', 'Tai nghe Sony')->firstOrFail();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_update_product_and_replace_image(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $oldImage = UploadedFile::fake()->image('old.jpg', 300, 300);
        $oldPath = $oldImage->store('products', 'public');

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Chuột cũ',
            'image' => $oldPath,
        ]);

        $newImage = UploadedFile::fake()->image('new.png', 300, 300);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Chuột mới',
            'category_id' => $category->id,
            'price' => 350000,
            'stock' => 10,
            'image' => $newImage,
            'description' => 'Mô tả chuột mới',
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('Chuột mới', $product->name);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_cannot_delete_product_with_orders(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 100000,
            'shipping_address' => 'HN',
            'phone' => '0987654321',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 100000,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_can_delete_product_without_orders(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('delete_me.webp');
        $imagePath = $image->store('products', 'public');

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image' => $imagePath,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_admin_can_create_product_with_zero_stock(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Sản phẩm hết hàng ban đầu',
            'category_id' => $category->id,
            'price' => 120000,
            'stock' => 0,
            'description' => 'Mô tả sản phẩm stock 0',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Sản phẩm hết hàng ban đầu',
            'category_id' => $category->id,
            'stock' => 0,
        ]);
    }

    public function test_admin_can_update_product_with_zero_stock(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm có sẵn',
            'stock' => 20,
            'price' => 250000,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Sản phẩm cập nhật hết hàng',
            'category_id' => $category->id,
            'price' => 250000,
            'stock' => 0,
            'description' => 'Đã bán hết kho',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame('Sản phẩm cập nhật hết hàng', $product->fresh()->name);
    }

    public function test_product_with_quotes_in_name_renders_safe_delete_form(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => "Sách O'Reilly \"Special Edition\"",
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertDontSee('onsubmit="return confirm', false);
        $response->assertSee('class="delete-confirm-form"', false);
    }
}
