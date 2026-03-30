<?php

namespace App\Http\Controllers\Api\V1;

use App\DataTransferObjects\Drive\InitiateUploadData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Drive\InitiateUploadRequest;
use App\Http\Requests\Drive\UploadChunkRequest;
use App\Http\Resources\DriveItemResource;
use App\Http\Resources\UploadSessionResource;
use App\Models\UploadSession;
use App\Services\ChunkUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UploadController extends Controller
{
    public function store(
        InitiateUploadRequest $request,
        ChunkUploadService $chunkUploadService,
    ): JsonResponse {
        $session = $chunkUploadService->initiate(
            $request->user(),
            InitiateUploadData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => UploadSessionResource::make($session),
        ], 201);
    }

    public function chunk(
        UploadChunkRequest $request,
        UploadSession $uploadSession,
        ChunkUploadService $chunkUploadService,
    ): JsonResponse {
        $session = $chunkUploadService->appendChunk(
            $request->user(),
            $uploadSession,
            $request->file('chunk'),
            (int) $request->validated('chunk_index'),
        );

        return response()->json([
            'data' => UploadSessionResource::make($session),
        ]);
    }

    public function complete(
        Request $request,
        UploadSession $uploadSession,
        ChunkUploadService $chunkUploadService,
    ): JsonResponse {
        $item = $chunkUploadService->complete($request->user(), $uploadSession);

        return response()->json([
            'data' => DriveItemResource::make($item),
        ]);
    }

    public function destroy(
        Request $request,
        UploadSession $uploadSession,
        ChunkUploadService $chunkUploadService,
    ): Response {
        $chunkUploadService->cancel($request->user(), $uploadSession);

        return response()->noContent();
    }
}
