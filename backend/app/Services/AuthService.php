<?php

namespace App\Services;

use App\DataTransferObjects\Auth\LoginData;
use App\DataTransferObjects\Auth\RegisterUserData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function register(RegisterUserData $data): array
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'preferred_locale' => $data->preferredLocale,
            'timezone' => $data->timezone,
            'role' => UserRole::User,
        ]);

        $token = $user->createToken('initial-device')->plainTextToken;
        $user->forceFill(['last_seen_at' => now()])->save();
        $user->sendEmailVerificationNotification();

        $this->auditLogService->record($user, 'auth.registered');

        return [$user, $token];
    }

    public function login(LoginData $data): array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw new AuthenticationException(__('auth.failed'));
        }

        $token = $user->createToken($data->deviceName)->plainTextToken;
        $user->forceFill(['last_seen_at' => now()])->save();

        $this->auditLogService->record($user, 'auth.logged_in', context: [
            'device_name' => $data->deviceName,
        ]);

        return [$user, $token];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        $token?->delete();

        $this->auditLogService->record($user, 'auth.logged_out');
    }
}
