<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:pending,paid,preparing,ready,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'status pesanan',
            'note' => 'catatan',
        ];
    }
}
