<?php

namespace App\DTOs\Menu;

class CreateMenuItemDTO
{
    public function __construct(
        public readonly int $recipeId,
        public readonly float $quantity,
        public readonly ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            recipeId: $data['recipe_id'],
            quantity: $data['quantity'],
            notes: $data['notes'] ?? null
        );
    }
}
