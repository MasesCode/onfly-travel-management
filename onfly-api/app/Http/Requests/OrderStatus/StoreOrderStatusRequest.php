<?php

namespace App\Http\Requests\OrderStatus;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:order_statuses,name|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do status é obrigatório.',
            'name.string' => 'O nome do status deve ser um texto.',
            'name.unique' => 'Este nome de status já existe.',
            'name.max' => 'O nome do status não pode ter mais de 255 caracteres.',
        ];
    }
}
