<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $menus = Menu::query()
            ->with('category')
            ->search($request->query('search'))
            ->ofCategory($request->integer('category_id') ?: null)
            ->available($request->has('is_available') ? $request->boolean('is_available') : null)
            ->priceBetween(
                $request->filled('min_price') ? (int) $request->query('min_price') : null,
                $request->filled('max_price') ? (int) $request->query('max_price') : null,
            )
            ->orderBy('name')
            ->paginate((int) min($request->integer('per_page', 10), 50));

        return ApiResponse::paginated($menus);
    }

    public function show(Menu $menu): JsonResponse
    {
        return ApiResponse::success(new MenuResource($menu->load('category')));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = Menu::create([
            'category_id' => $request->integer('category_id'),
            'name' => $request->string('name')->toString(),
            'slug' => Menu::makeSlug($request->string('name')->toString()),
            'description' => $request->input('description'),
            'price' => $request->integer('price'),
            'stock' => $request->integer('stock'),
            'image_url' => $request->input('image_url'),
            'is_available' => $request->boolean('is_available', true),
        ]);

        return ApiResponse::created(new MenuResource($menu->load('category')), 'Menu berhasil dibuat.');
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->only(['category_id', 'description', 'price', 'stock', 'image_url', 'is_available']);

        if ($request->filled('name')) {
            $data['name'] = $request->string('name')->toString();
            $data['slug'] = Menu::makeSlug($data['name']);
        }

        $menu->update($data);

        return ApiResponse::success(new MenuResource($menu->fresh()->load('category')), 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return ApiResponse::success(null, 'Menu berhasil dihapus.');
    }
}
