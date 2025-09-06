<?php

namespace App\DTOs;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

abstract class BaseDTO
{
    /**
     * Create DTO from array with validation
     *
     * @param array $data
     * @return static
     * @throws ValidationException
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        $validator = Validator::make($data, $instance->rules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $instance->fill($validator->validated());

        return $instance;
    }

    /**
     * Get validation rules
     *
     * @return array
     */
    abstract protected function rules(): array;

    /**
     * Fill the DTO with validated data
     *
     * @param array $data
     * @return void
     */
    abstract protected function fill(array $data): void;

    /**
     * Convert DTO to array
     *
     * @return array
     */
    abstract public function toArray(): array;
}
