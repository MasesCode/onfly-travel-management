<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user && $user->is_admin;
    }

    public function rules(): array
    {
        return [];
    }
}
