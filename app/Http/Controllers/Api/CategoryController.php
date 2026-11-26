<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MenuResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) min($request->integer('per_page', 10), 50);

        $categories = Category::query()
            ->search($request->query('search'))
            ->active($request->has('is_active') ? $request->boolean('is_active') : null)
            ->withCount('menus')
            ->orderBy('name')
            ->paginate($perPage);

        return ApiResponse::paginated($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('menus');

        return ApiResponse::success(new CategoryResource($category));
    }

    public function menus(Request $request, Category $category): JsonResponse
    {
        $menus = $category->menus()
            ->search($request->query('search'))
            ->available($request->has('is_available') ? $request->boolean('is_available') : null)
            ->orderBy('name')
            ->paginate((int) min($request->integer('per_page', 10), 50));

        return ApiResponse::paginated($menus);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'name' => $request->string('name')->toString(),
            'slug' => Category::makeSlug($request->string('name')->toString()),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return ApiResponse::created(new CategoryResource($category), 'Kategori berhasil dibuat.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->only(['description', 'is_active']);

        if ($request->filled('name')) {
            $data['name'] = $request->string('name')->toString();
            $data['slug'] = Category::makeSlug($data['name']);
        }

        $category->update($data);

        return ApiResponse::success(new CategoryResource($category->fresh()), 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->menus()->exists()) {
            return ApiResponse::error('Kategori masih memiliki menu, tidak bisa dihapus.', 422, [
                'category' => ['Hapus atau pindahkan menu terlebih dahulu.'],
            ]);
        }

        $category->delete();

        return ApiResponse::success(null, 'Kategori berhasil dihapus.');
    }
}
