<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:ready,unavailable,maintenance'],
            'description' => ['nullable', 'string', 'max:2000'],
            'specifications' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
