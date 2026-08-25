<?php

namespace App\Http\Requests;

use App\Enums\AiProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::enum(AiProvider::class)],
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => 'Оберіть AI-провайдера.',
            'provider.enum' => 'Обраний AI-провайдер не підтримується.',
            'question.required' => 'Напишіть, яку страву потрібно порадити.',
            'question.min' => 'Запит повинен містити щонайменше 3 символи.',
            'question.max' => 'Запит не може перевищувати 500 символів.',
        ];
    }
}
