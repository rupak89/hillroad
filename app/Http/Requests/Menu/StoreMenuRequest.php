<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_head_count' => 'required|integer|min:1',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'segments' => 'required|array|min:1',
            'segments.*.name' => 'required|string|max:255',
            'segments.*.sort_order' => 'nullable|integer',
            'segments.*.items' => 'required|array|min:1',
            'segments.*.items.*.recipe_id' => 'required|exists:recipes,id',
            'segments.*.items.*.quantity' => 'required|numeric|min:0.01',
            'segments.*.items.*.notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Menu name is required',
            'target_head_count.required' => 'Target head count is required',
            'target_head_count.min' => 'Target head count must be at least 1',
            'segments.required' => 'At least one segment is required',
            'segments.*.name.required' => 'Segment name is required',
            'segments.*.items.required' => 'Each segment must have at least one item',
            'segments.*.items.*.recipe_id.required' => 'Recipe is required for each item',
            'segments.*.items.*.recipe_id.exists' => 'Selected recipe does not exist',
            'segments.*.items.*.quantity.required' => 'Quantity is required for each item',
            'segments.*.items.*.quantity.min' => 'Quantity must be greater than 0',
        ];
    }
}
