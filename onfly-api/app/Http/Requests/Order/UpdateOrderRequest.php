<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date|after_or_equal:today',
            'return_date' => 'sometimes|date|after_or_equal:departure_date',
        ];
    }

    public function messages(): array
    {
        return [
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino não pode ter mais de 255 caracteres.',
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de partida deve ser hoje ou no futuro.',
            'return_date.date' => 'A data de retorno deve ser uma data válida.',
            'return_date.after_or_equal' => 'A data de retorno deve ser igual ou posterior à data de partida.',
        ];
    }
}
