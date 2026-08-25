<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Вкажіть електронну пошту.',
            'email.email' => 'Вкажіть коректну електронну пошту.',
            'password.required' => 'Вкажіть пароль.',
            'device_name.required' => 'Вкажіть назву пристрою.',
        ];
    }
}
