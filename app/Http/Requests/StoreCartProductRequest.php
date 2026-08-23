<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Оберіть товар.',
            'product_id.integer' => 'Ідентифікатор товару повинен бути цілим числом.',
            'product_id.exists' => 'Обраного товару не існує.',
        ];
    }
}
