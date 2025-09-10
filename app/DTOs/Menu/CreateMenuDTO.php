<?php

namespace App\DTOs\Menu;

class CreateMenuDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $targetHeadCount,
        public readonly float $markupPercentage,
        public readonly int $userId,
        /** @var CreateMenuSegmentDTO[] */
        public readonly array $segments,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            targetHeadCount: $data['target_head_count'],
            markupPercentage: $data['markup_percentage'] ?? 0,
            userId: $userId,
            segments: array_map(
                fn($segmentData, $index) => CreateMenuSegmentDTO::fromArray($segmentData, $index),
                $data['segments'],
                array_keys($data['segments'])
            )
        );
    }
}
