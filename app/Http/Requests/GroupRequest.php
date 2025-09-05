<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
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
        $groupId = $this->route('group'); // Get the group ID from the route

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:groups,name' . ($groupId ? ',' . $groupId : ''),
            ],
        ];
    }
}
