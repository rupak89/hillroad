<?php

namespace App\DTOs\Menu;

class UpdateMenuDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $targetHeadCount,
        public readonly float $markupPercentage,
        /** @var UpdateMenuSegmentDTO[] */
        public readonly array $segments,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            targetHeadCount: $data['target_head_count'],
            markupPercentage: $data['markup_percentage'] ?? 0,
            segments: array_map(
                fn($segmentData, $index) => UpdateMenuSegmentDTO::fromArray($segmentData, $index),
                $data['segments'],
                array_keys($data['segments'])
            )
        );
    }
}
