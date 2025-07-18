<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.string' => 'A senha atual deve ser um texto.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.string' => 'A nova senha deve ser um texto.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
        ];
    }
}
