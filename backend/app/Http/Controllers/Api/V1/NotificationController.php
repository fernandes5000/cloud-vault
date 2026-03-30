<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserNotificationResource;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(50);

        return response()->json([
            'data' => UserNotificationResource::collection($notifications->getCollection()),
            'meta' => [
                'total' => $notifications->total(),
                'currentPage' => $notifications->currentPage(),
                'lastPage' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markRead(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->forceFill(['read_at' => now()])->save();

        return response()->json([
            'data' => UserNotificationResource::make($notification->refresh()),
        ]);
    }
}
