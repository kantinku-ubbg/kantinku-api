<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:3', 'max:120', 'unique:menus,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:1000', 'max:1000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:9999'],
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
