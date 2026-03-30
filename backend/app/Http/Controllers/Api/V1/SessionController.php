<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->tokens()->latest()->get()->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'lastUsedAt' => $token->last_used_at?->toIso8601String(),
                'expiresAt' => $token->expires_at?->toIso8601String(),
                'createdAt' => $token->created_at?->toIso8601String(),
                'isCurrent' => $request->user()->currentAccessToken()?->id === $token->id,
            ]),
        ]);
    }

    public function destroy(Request $request, int $tokenId): Response
    {
        $request->user()->tokens()->whereKey($tokenId)->delete();

        return response()->noContent();
    }
}
