<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:20'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Вкажіть номер телефону.',
            'phone_number.max' => 'Номер телефону не може перевищувати 20 символів.',
            'delivery_address.required' => 'Вкажіть адресу доставки.',
            'delivery_address.max' => 'Адреса доставки не може перевищувати 255 символів.',
            'country.required' => 'Вкажіть країну.',
            'country.max' => 'Назва країни не може перевищувати 100 символів.',
        ];
    }
}
