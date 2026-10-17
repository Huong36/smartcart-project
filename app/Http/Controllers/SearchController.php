<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle live search / auto-suggest query.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        // Client and server validation: at least 2 chars, max 100 chars
        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'query' => $query,
                'data' => [],
                'total' => 0,
            ]);
        }

        $query = mb_substr($query, 0, 100);

        // Escape wildcards to prevent SQL LIKE injection attacks
        $escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        // Limit results to 6-8 items for performance and clean UI
        $products = Product::with('category')
            ->where('name', 'like', "%{$escapedQuery}%")
            ->take(8)
            ->get();

        $results = $products->map(function (Product $product) {
            $isOutOfStock = $product->stock <= 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'price' => (float) $product->price,
                'price_formatted' => number_format((float) $product->price, 0, ',', '.').' ₫',
                'stock' => (int) $product->stock,
                'is_out_of_stock' => $isOutOfStock,
                'stock_label' => $isOutOfStock ? 'Hết hàng' : 'Còn hàng ('.$product->stock.')',
                'image_url' => $product->image_url,
            ];
        });

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $results,
            'total' => $results->count(),
        ]);
    }
}
