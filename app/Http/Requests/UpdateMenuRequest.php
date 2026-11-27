<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name' => [
                'sometimes', 'required', 'string', 'min:3', 'max:120',
                Rule::unique('menus', 'name')->ignore($menuId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'required', 'integer', 'min:1000', 'max:1000000'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0', 'max:9999'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama menu',
            'description' => 'deskripsi',
            'price' => 'harga',
            'stock' => 'stok',
            'image_url' => 'URL gambar',
            'is_available' => 'status tersedia',
        ];
    }
}
