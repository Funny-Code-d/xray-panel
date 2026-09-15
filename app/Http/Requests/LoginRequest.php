<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Разрешить всем (аутентификация ещё не пройдена).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Сообщения об ошибках на русском.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email обязателен',
            'email.email' => 'Некорректный email',
            'password.required' => 'Пароль обязателен',
        ];
    }
}