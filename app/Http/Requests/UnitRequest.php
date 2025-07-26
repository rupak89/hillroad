<?php

namespace App\Http\Requests;

use App\Models\unit;
use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:units',
            'ratio' => 'required|numeric|min:0',
            'unit_type_id' => 'required|exists:unit_types,id',
        ];
    }
}
