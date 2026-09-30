<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashClosureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->attributes->has('activeBusiness');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'counted_cash' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_date.required' => 'Selecciona la fecha del cierre.',
            'business_date.before_or_equal' => 'No puedes cerrar una fecha futura.',
            'counted_cash.required' => 'Introduce el efectivo contado.',
            'counted_cash.numeric' => 'El efectivo contado debe ser un importe válido.',
            'counted_cash.min' => 'El efectivo contado no puede ser negativo.',
            'counted_cash.decimal' => 'El efectivo contado puede tener como máximo 2 decimales.',
        ];
    }
}
