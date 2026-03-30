<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\Share\CreateShareLinkData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Share\CreateShareLinkRequest;
use App\Http\Resources\DriveItemResource;
use App\Http\Resources\ShareLinkResource;
use App\Models\DriveItem;
use App\Models\ShareLink;
use App\Services\DriveItemService;
use App\Services\ShareLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShareController extends Controller
{
    public function index(Request $request, DriveItem $driveItem, ShareLinkService $shareLinkService): JsonResponse
    {
        $this->authorize('share', $driveItem);

        return response()->json([
            'data' => ShareLinkResource::collection($shareLinkService->listForItem($request->user(), $driveItem)),
        ]);
    }

    public function store(CreateShareLinkRequest $request, ShareLinkService $shareLinkService): JsonResponse
    {
        $share = $shareLinkService->create(
            $request->user(),
            CreateShareLinkData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => ShareLinkResource::make($share),
        ], 201);
    }

    public function destroy(Request $request, ShareLink $shareLink): Response
    {
        $this->authorize('delete', $shareLink);
        $shareLink->delete();

        return response()->noContent();
    }

    public function sharedWithMe(Request $request): JsonResponse
    {
        $shares = ShareLink::query()
            ->with('driveItem')
            ->where('visibility', 'private')
            ->where('is_active', true)
            ->where(function ($query) use ($request): void {
                $query->where('recipient_user_id', $request->user()->id)
                    ->orWhere('recipient_email', $request->user()->email);
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $shares->map(fn (ShareLink $share) => [
                'share' => ShareLinkResource::make($share),
                'item' => $share->driveItem ? DriveItemResource::make($share->driveItem) : null,
            ]),
        ]);
    }
}
