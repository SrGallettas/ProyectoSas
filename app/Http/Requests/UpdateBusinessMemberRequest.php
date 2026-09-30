<?php

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->attributes->get('activeBusinessRole') === Business::ROLE_OWNER;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([Business::ROLE_MANAGER, Business::ROLE_STAFF])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
