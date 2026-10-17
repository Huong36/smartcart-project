<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = Product::with('category');

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if ($keyword = trim($filters['q'] ?? '')) {
            $query->where('name', 'like', '%'.$keyword.'%');
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->latest('id'),
        };

        return view('home', [
            'products' => $query->paginate(8)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
