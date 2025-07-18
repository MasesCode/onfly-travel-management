<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user && $user->is_admin;
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => 'sometimes|string|min:8',
            'is_admin' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.string' => 'O email deve ser um texto.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este email já está em uso.',
            'password.string' => 'A senha deve ser um texto.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'is_admin.boolean' => 'O campo administrador deve ser verdadeiro ou falso.',
        ];
    }
}
