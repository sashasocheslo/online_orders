<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Вкажіть ім’я.',
            'email.required' => 'Вкажіть електронну пошту.',
            'email.email' => 'Вкажіть коректну електронну пошту.',
            'email.unique' => 'Користувач із такою електронною поштою вже існує.',
            'password.required' => 'Вкажіть пароль.',
            'password.confirmed' => 'Підтвердження пароля не збігається.',
            'password.min' => 'Пароль повинен містити щонайменше 8 символів.',
            'device_name.required' => 'Вкажіть назву пристрою.',
        ];
    }
}
