<?php

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Create a DTO from already-validated data.
     *
     * Validation is intentionally not performed here: HTTP requests are
     * validated by their FormRequest, which is the single validation layer.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $instance = new static;
        $instance->fill($data);

        return $instance;
    }

    /**
     * Fill the DTO from validated data.
     *
     * @param  array<string, mixed>  $data
     */
    abstract protected function fill(array $data): void;

    /**
     * Convert the DTO back to an array.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
