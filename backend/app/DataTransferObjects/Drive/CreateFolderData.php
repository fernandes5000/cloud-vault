<?php

namespace App\DataTransferObjects\Drive;

readonly class CreateFolderData
{
    public function __construct(
        public string $name,
        public ?string $parentId,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            name: $attributes['name'],
            parentId: $attributes['parent_id'] ?? null,
        );
    }
}
