<?php

namespace App\DataTransferObjects\Drive;

readonly class InitiateUploadData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public ?string $folderId,
        public int $totalChunks,
        public ?int $totalSizeBytes,
        public ?string $mimeType,
        public ?string $checksumSha256,
        public array $metadata,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            name: $attributes['name'],
            folderId: $attributes['folder_id'] ?? null,
            totalChunks: (int) $attributes['total_chunks'],
            totalSizeBytes: isset($attributes['total_size_bytes']) ? (int) $attributes['total_size_bytes'] : null,
            mimeType: $attributes['mime_type'] ?? null,
            checksumSha256: $attributes['checksum_sha256'] ?? null,
            metadata: $attributes['metadata'] ?? [],
        );
    }
}
