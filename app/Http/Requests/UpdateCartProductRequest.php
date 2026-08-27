<?php

namespace App\Http\Requests;

use App\Models\CartProduct;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartProduct = $this->route('cart_product');

        return $cartProduct instanceof CartProduct
            && ($this->user()?->can('update', $cartProduct) ?? false);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Вкажіть кількість товару.',
            'quantity.integer' => 'Кількість повинна бути цілим числом.',
            'quantity.min' => 'Мінімальна кількість товару — 1.',
            'quantity.max' => 'Максимальна кількість товару — 99.',
        ];
    }
}
