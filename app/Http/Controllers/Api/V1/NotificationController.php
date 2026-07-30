<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 30), 1), 100);
        $unreadOnly = $request->boolean('unread');

        $query = $request->user()
            ->notifications()
            ->latest();

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query
            ->limit($limit)
            ->get()
            ->map(fn (UserNotification $notification) => $this->payload($notification));

        return $this->success([
            'notifications' => $notifications,
            'unread_count' => $request->user()->notifications()->unread()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success([
            'unread_count' => $request->user()->notifications()->unread()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $publicId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('public_id', $publicId)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $this->success($this->payload($notification->refresh()), 'Bildirim okundu.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return $this->success([
            'unread_count' => 0,
        ], 'Tüm bildirimler okundu.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(UserNotification $notification): array
    {
        return [
            'public_id' => $notification->public_id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'action_url' => $notification->action_url,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
