<?php

namespace App\DTOs\Menu;

class UpdateMenuItemDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $recipeId,
        public readonly float $quantity,
        public readonly ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            recipeId: $data['recipe_id'],
            quantity: $data['quantity'],
            notes: $data['notes'] ?? null
        );
    }
}
