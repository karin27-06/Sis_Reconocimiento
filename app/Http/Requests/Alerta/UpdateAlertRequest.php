<?php

namespace App\Http\Requests\Alerta;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idMovimiento' => 'required|exists:movimientos,id',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
            'tipo' => 'required|in:1,2', // Solo acepta 1 o 2
        ];
    }

    public function messages(): array
    {
        return [
            'idMovimiento.required' => 'El movimiento es obligatorio.',
            'idMovimiento.exists' => 'El movimiento seleccionado no es válido.',

            'descripcion.string' => 'La descripción debe ser texto.',

            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',

            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser 1 (Huella) o 2 (Cara).',
        ];
    }
}
