<?php

namespace App\DataTransferObjects\Auth;

readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $preferredLocale,
        public string $timezone,
    ) {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            name: $attributes['name'],
            email: $attributes['email'],
            password: $attributes['password'],
            preferredLocale: $attributes['preferred_locale'] ?? 'en',
            timezone: $attributes['timezone'] ?? 'UTC',
        );
    }
}
