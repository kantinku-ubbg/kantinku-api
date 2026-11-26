<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'sometimes', 'required', 'string', 'min:3', 'max:80',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kategori',
            'description' => 'deskripsi',
            'is_active' => 'status aktif',
        ];
    }
}
