<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'item_name' => 'required|string|max:255|unique:items',
            'ordering_unit_id' => 'required|numeric|exists:units,id',
            'counting_unit_id' => 'required|numeric|exists:units,id',
            'default_supplier_id' => 'nullable|exists:suppliers,id',
            'default_brand_id' => 'nullable|exists:brands,id',
            'group_id' => 'nullable|exists:groups,id',
            'latest_price' => 'required|numeric|min:0',
        ];
    }
}
