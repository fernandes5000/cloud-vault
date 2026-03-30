<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\Auth\LoginData;
use App\DataTransferObjects\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $authService): JsonResponse
    {
        [$user, $token] = $authService->register(RegisterUserData::fromArray($request->validated()));

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ], 201);
    }

    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        [$user, $token] = $authService->login(LoginData::fromArray($request->validated()));

        return response()->json([
            'token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(UserResource::make($request->user())->resolve());
    }

    public function logout(Request $request, AuthService $authService): Response
    {
        $authService->logout($request->user());

        return response()->noContent();
    }
}
