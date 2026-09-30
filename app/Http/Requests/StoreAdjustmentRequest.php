<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->attributes->has('activeBusinessRole');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['void', 'refund'])],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
            'payment_method' => ['nullable', Rule::in(['cash', 'card'])],
            'lines' => ['nullable', 'array'],
            'lines.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
