<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AuditLog;
use App\Models\DriveItem;
use App\Models\UploadSession;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json([
            'data' => [
                'usersCount' => User::query()->count(),
                'filesCount' => DriveItem::query()->where('type', 'file')->count(),
                'foldersCount' => DriveItem::query()->where('type', 'folder')->count(),
                'trashedCount' => DriveItem::query()->onlyTrashed()->count(),
                'activeUploadSessionsCount' => UploadSession::query()->whereIn('status', ['pending', 'uploading'])->count(),
                'storageUsedBytes' => (int) User::query()->sum('used_storage_bytes'),
                'recentAuditEvents' => AuditLog::query()->latest()->limit(10)->get()->map(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'userId' => $log->user_id,
                    'createdAt' => $log->created_at?->toIso8601String(),
                    'context' => $log->context ?? [],
                ]),
            ],
        ]);
    }
}
