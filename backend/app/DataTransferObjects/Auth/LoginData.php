<?php

namespace App\DataTransferObjects\Auth;

readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            email: $attributes['email'],
            password: $attributes['password'],
            deviceName: $attributes['device_name'] ?? 'unknown-device',
        );
    }
}
