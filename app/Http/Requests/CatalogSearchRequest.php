<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'query' => trim((string) $this->query('query', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:120'],
            'menu_id' => ['nullable', 'integer', Rule::exists('menus', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::when($this->filled('min_price'), ['gte:min_price']),
            ],
            'sort' => ['nullable', Rule::in(['price_asc', 'price_desc', 'name'])],
        ];
    }

    public function messages(): array
    {
        return [
            'query.max' => 'Пошуковий запит не може перевищувати 120 символів.',
            'menu_id.exists' => 'Обраного ресторану не існує.',
            'category_id.exists' => 'Обраної категорії не існує.',
            'min_price.numeric' => 'Мінімальна ціна повинна бути числом.',
            'min_price.min' => 'Мінімальна ціна не може бути від’ємною.',
            'max_price.numeric' => 'Максимальна ціна повинна бути числом.',
            'max_price.min' => 'Максимальна ціна не може бути від’ємною.',
            'max_price.gte' => 'Максимальна ціна повинна бути не меншою за мінімальну.',
            'sort.in' => 'Обрано невідомий спосіб сортування.',
        ];
    }
}
