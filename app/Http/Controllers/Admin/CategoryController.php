<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::withCount('products');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm mới danh mục "'.$category->name.'" thành công.');
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục "'.$category->name.'" thành công.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa danh mục "'.$category->name.'" vì vẫn còn chứa '.$category->products()->count().' sản phẩm.');
        }

        try {
            $name = $category->name;
            $category->delete();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Đã xóa danh mục "'.$name.'" thành công.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Có lỗi xảy ra khi xóa danh mục: '.$e->getMessage());
        }
    }
}
