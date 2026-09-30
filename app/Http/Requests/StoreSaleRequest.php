<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'customer_id' => ['nullable', 'integer'],
            'payment_method' => ['required', Rule::in([Sale::PAYMENT_CASH, Sale::PAYMENT_CARD])],
            'checkout_token' => ['required', 'uuid'],
            'products' => ['required', 'array'],
            'products.*' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Selecciona efectivo o tarjeta para completar el cobro.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'checkout_token.required' => 'No se ha podido identificar este cobro. Recarga la página e inténtalo de nuevo.',
            'checkout_token.uuid' => 'No se ha podido identificar este cobro. Recarga la página e inténtalo de nuevo.',
            'products.required' => 'Añade al menos un producto.',
            'products.*.min' => 'Las cantidades no pueden ser negativas.',
        ];
    }
}
