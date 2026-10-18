<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Http::preventStrayRequests();
        config(['visual-search.token' => 'test-token', 'visual-search.minimum_similarity' => 0.45]);
    }

    public function test_guests_can_open_camera_and_image_upload_page(): void
    {
        $this->get('/search/image')->assertOk()->assertSee('Chụp ảnh')->assertSee('Chọn ảnh');
    }

    public function test_missing_non_image_and_oversized_uploads_are_rejected_before_calling_engine(): void
    {
        foreach ([null, UploadedFile::fake()->create('bad.txt', 1, 'text/plain'), UploadedFile::fake()->image('large.jpg')->size(2049)] as $image) {
            $this->postJson('/search/image', $image ? ['image' => $image] : [])->assertUnprocessable()->assertJsonValidationErrors('image');
        }
        Http::assertNothingSent();
    }

    public function test_image_dimensions_are_limited(): void
    {
        $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('wide.jpg', 6001, 1)])
            ->assertUnprocessable()->assertJsonValidationErrors('image');
        Http::assertNothingSent();
    }

    public function test_matches_use_real_catalog_data_are_sorted_and_do_not_expose_confidence_percentages(): void
    {
        $first = $this->productWithImage('first.jpg', ['price' => 125000, 'stock' => 0]);
        $second = $this->productWithImage('second.jpg');
        $irrelevant = $this->productWithImage('other.jpg');
        Http::fake(['127.0.0.1:8020/*' => Http::response(['matches' => [
            ['id' => $first->id, 'similarity' => 0.7],
            ['id' => $second->id, 'similarity' => 0.9],
            ['id' => $second->id, 'similarity' => 0.9],
            ['id' => $irrelevant->id, 'similarity' => 0.2],
            ['id' => 999999, 'similarity' => 0.99],
        ]])]);

        $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('query.jpg')])
            ->assertOk()->assertJsonCount(2, 'products')
            ->assertJsonPath('products.0.id', $second->id)
            ->assertJsonPath('products.1.price', '125000.00')
            ->assertJsonPath('products.1.stock', 0)
            ->assertJsonMissingPath('products.0.similarity');
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer test-token')
            && count($request['catalog']) === 3 && is_string($request['image']));
        $this->assertSame(['products/first.jpg', 'products/other.jpg', 'products/second.jpg'], Storage::disk('public')->allFiles('products'));
    }

    public function test_missing_catalog_images_are_skipped_without_engine_calls(): void
    {
        Product::factory()->create(['image' => 'products/missing.jpg']);
        $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('query.jpg')])
            ->assertOk()->assertJsonCount(0, 'products');
        Http::assertNothingSent();
    }

    public function test_no_match_is_reported_without_falling_back_to_random_products(): void
    {
        $this->productWithImage('first.jpg');
        Http::fake(['127.0.0.1:8020/*' => Http::response(['matches' => []])]);
        $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('query.jpg')])
            ->assertOk()->assertJsonCount(0, 'products')->assertJsonPath('message', 'Chưa tìm thấy sản phẩm đủ giống. Hãy thử ảnh rõ hơn, chỉ có một món đồ.');
    }

    public function test_unavailable_engine_returns_friendly_error_without_internal_details(): void
    {
        $this->productWithImage('first.jpg');
        Http::fake(['127.0.0.1:8020/*' => Http::response(['error' => 'private/internal/path'], 503)]);
        $response = $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('query.jpg')]);
        $response->assertStatus(503)->assertJsonMissing(['error' => 'private/internal/path']);
        $this->assertStringNotContainsString('private/internal/path', $response->getContent());
    }

    public function test_malformed_engine_response_does_not_appear_as_no_match(): void
    {
        $this->productWithImage('first.jpg');
        Http::fake(['127.0.0.1:8020/*' => Http::response(['unexpected' => true])]);
        $this->postJson('/search/image', ['image' => UploadedFile::fake()->image('query.jpg')])->assertStatus(503);
    }

    /** @param array<string, mixed> $attributes */
    private function productWithImage(string $name, array $attributes = []): Product
    {
        UploadedFile::fake()->image($name)->storeAs('products', $name, 'public');

        return Product::factory()->create(['image' => 'products/'.$name, ...$attributes]);
    }
}
