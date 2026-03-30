<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriveItemResource;
use App\Http\Resources\ShareLinkResource;
use App\Services\DriveItemService;
use App\Services\ShareLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicShareController extends Controller
{
    public function show(Request $request, string $token, ShareLinkService $shareLinkService): JsonResponse
    {
        $shareLink = $shareLinkService->resolve(
            token: $token,
            viewer: $request->user(),
            password: $request->string('password')->toString() ?: null,
        );

        $shareLinkService->registerAccess($shareLink);

        return response()->json([
            'data' => [
                'share' => ShareLinkResource::make($shareLink),
                'item' => DriveItemResource::make($shareLink->driveItem),
            ],
        ]);
    }

    public function download(
        Request $request,
        string $token,
        ShareLinkService $shareLinkService,
        DriveItemService $driveItemService,
    ) {
        $shareLink = $shareLinkService->resolve(
            token: $token,
            viewer: $request->user(),
            password: $request->string('password')->toString() ?: null,
        );
        $shareLinkService->assertDownloadAllowed($shareLink);
        $shareLinkService->registerAccess($shareLink, downloaded: true);

        return $driveItemService->responseForItem($shareLink->driveItem);
    }
}
