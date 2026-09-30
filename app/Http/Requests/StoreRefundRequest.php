<?php

namespace App\Http\Requests;

use App\Models\Business;
use App\Models\Sale;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->attributes->get('activeBusinessRole'), [Business::ROLE_OWNER, Business::ROLE_MANAGER], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lines' => ['required', 'array'],
            'lines.*' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', Rule::in([Sale::PAYMENT_CASH, Sale::PAYMENT_CARD])],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }
}
