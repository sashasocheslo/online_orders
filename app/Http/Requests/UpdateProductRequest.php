<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            && ($this->user()?->can('update', $product) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size' => ['nullable', 'string', 'max:50'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('2mb'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Вкажіть назву товару.',
            'name.max' => 'Назва не може перевищувати 255 символів.',
            'price.required' => 'Вкажіть ціну товару.',
            'price.numeric' => 'Ціна повинна бути числом.',
            'price.decimal' => 'Ціна може мати не більше двох знаків після коми.',
            'price.min' => 'Ціна повинна бути більшою за нуль.',
            'description.max' => 'Опис не може перевищувати 1000 символів.',
            'size.max' => 'Розмір не може перевищувати 50 символів.',
            'category_id.required' => 'Оберіть категорію.',
            'category_id.exists' => 'Обраної категорії не існує.',
            'image.image' => 'Файл повинен бути зображенням.',
            'image.max' => 'Зображення не може бути більшим за 2 МБ.',
        ];
    }
}
