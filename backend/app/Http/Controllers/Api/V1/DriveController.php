<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\Drive\CreateFolderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\CreateFolderRequest;
use App\Http\Requests\Drive\FavoriteDriveItemRequest;
use App\Http\Requests\Drive\MoveDriveItemRequest;
use App\Http\Requests\Drive\RenameDriveItemRequest;
use App\Http\Resources\DriveItemResource;
use App\Models\DriveItem;
use App\Services\DriveItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DriveController extends Controller
{
    public function index(Request $request, DriveItemService $driveItemService): JsonResponse
    {
        $scope = $request->string('scope')->toString() ?: 'root';
        $items = $driveItemService->listItems(
            user: $request->user(),
            parentId: $request->input('parent_id'),
            scope: $scope,
        );

        return response()->json([
            'data' => DriveItemResource::collection($items->getCollection()),
            'meta' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
                'scope' => $scope,
            ],
        ]);
    }

    public function storeFolder(CreateFolderRequest $request, DriveItemService $driveItemService): JsonResponse
    {
        $item = $driveItemService->createFolder(
            $request->user(),
            CreateFolderData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => DriveItemResource::make($item),
        ], 201);
    }

    public function rename(
        RenameDriveItemRequest $request,
        DriveItem $driveItem,
        DriveItemService $driveItemService,
    ): JsonResponse {
        $this->authorize('update', $driveItem);

        $item = $driveItemService->rename($request->user(), $driveItem, $request->validated('name'));

        return response()->json(['data' => DriveItemResource::make($item)]);
    }

    public function move(
        MoveDriveItemRequest $request,
        DriveItem $driveItem,
        DriveItemService $driveItemService,
    ): JsonResponse {
        $this->authorize('update', $driveItem);

        $item = $driveItemService->move($request->user(), $driveItem, $request->validated('parent_id'));

        return response()->json(['data' => DriveItemResource::make($item)]);
    }

    public function favorite(
        FavoriteDriveItemRequest $request,
        DriveItem $driveItem,
        DriveItemService $driveItemService,
    ): JsonResponse {
        $this->authorize('update', $driveItem);

        $item = $driveItemService->toggleFavorite(
            $request->user(),
            $driveItem,
            (bool) $request->validated('is_favorite'),
        );

        return response()->json(['data' => DriveItemResource::make($item)]);
    }

    public function destroy(Request $request, DriveItem $driveItem, DriveItemService $driveItemService): Response
    {
        $this->authorize('delete', $driveItem);
        $driveItemService->trash($request->user(), $driveItem);

        return response()->noContent();
    }

    public function restore(Request $request, string $driveItemId, DriveItemService $driveItemService): JsonResponse
    {
        $item = $driveItemService->findOwned($request->user(), $driveItemId, true);
        $this->authorize('restore', $item);

        return response()->json([
            'data' => DriveItemResource::make($driveItemService->restore($request->user(), $item)),
        ]);
    }

    public function preview(Request $request, DriveItem $driveItem, DriveItemService $driveItemService)
    {
        $this->authorize('view', $driveItem);
        $driveItemService->touchOpened($request->user(), $driveItem);

        return $driveItemService->responseForItem($driveItem, inline: true);
    }

    public function download(Request $request, DriveItem $driveItem, DriveItemService $driveItemService)
    {
        $this->authorize('view', $driveItem);
        $driveItemService->touchOpened($request->user(), $driveItem);

        return $driveItemService->responseForItem($driveItem);
    }
}
