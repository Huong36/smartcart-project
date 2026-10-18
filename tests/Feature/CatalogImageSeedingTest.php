<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CatalogImageSeedingTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('catalogSeeders')]
    public function test_existing_sample_images_are_backfilled_without_resetting_product_data(string $seeder): void
    {
        Storage::fake('public');
        $category = Category::factory()->create(['name' => 'Điện tử', 'slug' => 'dien-tu']);
        $missingImage = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tai nghe không dây',
            'image' => null,
            'price' => 123456,
            'stock' => 0,
            'description' => 'Mô tả đã chỉnh sửa',
        ]);
        $emptyImage = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Loa Bluetooth mini',
            'image' => '',
        ]);
        $customImage = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Chuột không dây',
            'image' => 'products/custom-upload.png',
        ]);
        $unrelated = Product::factory()->create(['image' => null]);
        Storage::disk('public')->put('products/custom-upload.png', 'user-upload');
        Storage::disk('public')->put('products/tai-nghe-khong-day.jpg', 'existing-file');

        $this->seed($seeder);
        $this->seed($seeder);

        $this->assertSame('products/tai-nghe-khong-day.jpg', $missingImage->fresh()->image);
        $this->assertSame('products/loa-bluetooth-mini.jpg', $emptyImage->fresh()->image);
        $this->assertSame('123456.00', $missingImage->fresh()->price);
        $this->assertSame(0, $missingImage->fresh()->stock);
        $this->assertSame('Mô tả đã chỉnh sửa', $missingImage->fresh()->description);
        $this->assertSame('products/custom-upload.png', $customImage->fresh()->image);
        $this->assertSame('user-upload', Storage::disk('public')->get($customImage->image));
        $this->assertSame('existing-file', Storage::disk('public')->get($missingImage->fresh()->image));
        $this->assertNull($unrelated->fresh()->image);
        Storage::disk('public')->assertExists($emptyImage->fresh()->image);
        $this->assertDatabaseCount('products', 61);
    }

    public function test_legacy_png_paths_are_upgraded_without_deleting_existing_files(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create(['name' => 'Điện tử', 'slug' => 'dien-tu']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tai nghe không dây',
            'image' => 'products/tai-nghe-khong-day.png',
            'price' => 333000,
            'stock' => 7,
        ]);
        Storage::disk('public')->put($product->image, 'original-png-content');

        $this->seed(ProductSeeder::class);

        $this->assertSame('products/tai-nghe-khong-day.jpg', $product->fresh()->image);
        $this->assertSame('original-png-content', Storage::disk('public')->get('products/tai-nghe-khong-day.png'));
        Storage::disk('public')->assertExists('products/tai-nghe-khong-day.jpg');
        $this->assertSame('333000.00', $product->fresh()->price);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_fresh_install_has_files_for_every_sample_product_image(): void
    {
        Storage::fake('public');

        $this->seed();

        $this->assertDatabaseCount('products', 60);
        $this->assertDatabaseCount('categories', 8);
        foreach (Product::all() as $product) {
            $this->assertNotEmpty($product->image, $product->name);
            Storage::disk('public')->assertExists($product->image);
        }
    }

    /**
     * @return array<string, array{class-string<Seeder>}>
     */
    public static function catalogSeeders(): array
    {
        return [
            'full database seed' => [DatabaseSeeder::class],
            'product seed only' => [ProductSeeder::class],
            'catalog seed only' => [CatalogSeeder::class],
        ];
    }
}
