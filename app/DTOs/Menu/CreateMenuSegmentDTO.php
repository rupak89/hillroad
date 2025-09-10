<?php

namespace App\DTOs\Menu;

class CreateMenuSegmentDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $sortOrder,
        /** @var CreateMenuItemDTO[] */
        public readonly array $items,
    ) {}

    public static function fromArray(array $data, int $defaultSortOrder): self
    {
        return new self(
            name: $data['name'],
            sortOrder: $data['sort_order'] ?? ($defaultSortOrder + 1),
            items: array_map(
                fn($itemData) => CreateMenuItemDTO::fromArray($itemData),
                $data['items']
            )
        );
    }
}
