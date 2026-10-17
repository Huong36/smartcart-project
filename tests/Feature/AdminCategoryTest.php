<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
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

    public function test_guest_and_customer_cannot_access_categories(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));

        $this->actingAs($this->customerUser())
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_category_list_and_create_page(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create(['name' => 'Gia dụng thông minh']);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Gia dụng thông minh');

        $this->actingAs($admin)
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSee('Thêm Danh mục mới');
    }

    public function test_admin_can_create_category_with_valid_data(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Thiết bị thông minh',
            'slug' => 'thiet-bi-thong-minh',
            'description' => 'Mô tả danh mục thiết bị.',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Thiết bị thông minh',
            'slug' => 'thiet-bi-thong-minh',
        ]);
    }

    public function test_category_validation_requires_name_and_unique_slug(): void
    {
        $admin = $this->adminUser();
        Category::factory()->create(['slug' => 'dien-tu']);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => '',
            'slug' => 'dien-tu',
        ]);

        $response->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create(['name' => 'Tên cũ', 'slug' => 'ten-cu']);

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Tên mới',
            'slug' => 'ten-moi',
            'description' => 'Mô tả mới',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Tên mới',
            'slug' => 'ten-moi',
        ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_empty_category(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_quotes_in_name_renders_safe_delete_form(): void
    {
        $admin = $this->adminUser();
        $category = Category::factory()->create([
            'name' => "Sách & Tạp chí O'Reilly \"Tech\"",
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertDontSee('onsubmit="return confirm', false);
        $response->assertSee('class="delete-confirm-form"', false);
    }
}
