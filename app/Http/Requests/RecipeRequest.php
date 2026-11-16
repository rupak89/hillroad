<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
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
        $isUpdate = $this->route('recipe') !== null;

        return [
            'recipe_name' => ($isUpdate ? 'sometimes' : 'required').'|string|max:255',
            'instruction' => 'nullable|string',
            'thumbnail' => 'nullable|string|max:500',
            'servings' => 'nullable|integer|min:1|max:1000',

            // Ingredients validation
            'ingredients' => 'nullable|array',
            'ingredients.*.item_id' => 'required_with:ingredients|integer|exists:items,id',
            'ingredients.*.unit_id' => 'nullable|integer|exists:units,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0.01',

            // Sub-recipes validation
            'sub_recipes' => 'nullable|array',
            'sub_recipes.*.child_recipe_id' => 'required_with:sub_recipes|integer|exists:recipes,id|different:id',
            'sub_recipes.*.quantity' => 'required_with:sub_recipes|numeric|min:0.01',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'recipe_name.required' => 'Recipe name is required.',
            'recipe_name.max' => 'Recipe name must not exceed 255 characters.',
            'servings.min' => 'Servings must be at least 1.',

            'ingredients.*.item_id.required_with' => 'Item is required for each ingredient.',
            'ingredients.*.item_id.exists' => 'Selected item does not exist.',
            'ingredients.*.unit_id.exists' => 'Selected unit does not exist.',
            'ingredients.*.quantity.required_with' => 'Quantity is required for each ingredient.',
            'ingredients.*.quantity.numeric' => 'Quantity must be a number.',
            'ingredients.*.quantity.min' => 'Quantity must be greater than 0.',

            'sub_recipes.*.child_recipe_id.required_with' => 'Recipe is required for each sub-recipe.',
            'sub_recipes.*.child_recipe_id.exists' => 'Selected recipe does not exist.',
            'sub_recipes.*.child_recipe_id.different' => 'A recipe cannot include itself as a sub-recipe.',
            'sub_recipes.*.quantity.required_with' => 'Quantity is required for each sub-recipe.',
            'sub_recipes.*.quantity.numeric' => 'Quantity must be a number.',
            'sub_recipes.*.quantity.min' => 'Quantity must be greater than 0.',
        ];
    }
}
