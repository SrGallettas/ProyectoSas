<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('business_id', $this->attributes->get('activeBusiness')->id)],
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'El nombre de la categoría es obligatorio.', 'name.max' => 'El nombre de la categoría no puede superar los 255 caracteres.', 'name.unique' => 'Ya existe una categoría con ese nombre.'];
    }
}
