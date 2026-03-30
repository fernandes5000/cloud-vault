<?php

namespace App\DataTransferObjects\Drive;

readonly class CompleteUploadData
{
    public function __construct(
        public string $uploadSessionId,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            uploadSessionId: $attributes['upload_session_id'],
        );
    }
}
