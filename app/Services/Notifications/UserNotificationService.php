<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\UserNotification;

class UserNotificationService
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function createForUser(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        ?array $data = null,
    ): UserNotification {
        return UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }
}
