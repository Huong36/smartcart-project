<?php

namespace App\Http\Controllers;

use App\Services\ImageSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ImageSearchController extends Controller
{
    public function index(): View
    {
        return view('image-search');
    }

    public function search(Request $request, ImageSearchService $search): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ], [
            'image.required' => 'Bạn hãy chụp hoặc chọn một ảnh trước.',
            'image.image' => 'Tệp đã chọn không phải ảnh hợp lệ.',
            'image.mimes' => 'Hỗ trợ ảnh JPG, PNG và WebP.',
            'image.max' => 'Vui lòng chọn ảnh không quá 2 MB.',
            'image.dimensions' => 'Ảnh không được lớn hơn 6000 × 6000 pixel.',
        ]);

        try {
            $results = $search->search($request->file('image'));
        } catch (Throwable $exception) {
            Log::warning('Visual search unavailable', ['exception' => $exception::class]);

            return response()->json(['message' => 'Tìm bằng ảnh đang tạm thời chưa sẵn sàng. Bạn thử lại sau nhé.'], 503);
        }

        return response()->json([
            'products' => array_map(fn (array $result): array => [
                'id' => $result['product']->id,
                'name' => $result['product']->name,
                'category' => $result['product']->category?->name,
                'price' => $result['product']->price,
                'stock' => $result['product']->stock,
                'image_url' => $result['product']->image_url,
                'url' => route('products.show', $result['product']),
            ], $results),
            'message' => $results === [] ? 'Chưa tìm thấy sản phẩm đủ giống. Hãy thử ảnh rõ hơn, chỉ có một món đồ.' : null,
        ])->header('Cache-Control', 'no-store');
    }
}
