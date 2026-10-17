<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower($this->input('email'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => [
                'bail', 'required', 'string', 'min:8', 'confirmed',

                function (string $attribute, string $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('Пароль слишком длинный. Сократите его до 72 байт.');
                    }

                    if (str_contains($value, "\0")) {
                        $fail('Пароль содержит недопустимый символ.');
                    }
                },
            ],
            'password_confirmation' => ['required', 'string'],
            'role' => ['prohibited'],
            'user_id' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Имя'];
    }
}
