<?php

namespace App\DTOs\Menu;

class UpdateMenuSegmentDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly int $sortOrder,
        /** @var UpdateMenuItemDTO[] */
        public readonly array $items,
    ) {}

    public static function fromArray(array $data, int $defaultSortOrder): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            sortOrder: $data['sort_order'] ?? ($defaultSortOrder + 1),
            items: array_map(
                fn($itemData) => UpdateMenuItemDTO::fromArray($itemData),
                $data['items']
            )
        );
    }
}
