<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePushDeviceTokenRequest;
use App\Http\Traits\ApiResponse;
use App\Models\PushDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushDeviceTokenController extends Controller
{
    use ApiResponse;

    public function store(StorePushDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $token = PushDeviceToken::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'device_id' => $validated['device_id'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'locale' => $validated['locale'] ?? null,
                'timezone' => $validated['timezone'] ?? null,
                'enabled' => true,
                'last_seen_at' => now(),
            ],
        );

        Log::info('Push device token registered.', [
            'user_id' => $request->user()->id,
            'platform' => $token->platform,
            'device_id' => $token->device_id,
        ]);

        return $this->success([
            'registered' => true,
            'platform' => $token->platform,
            'last_seen_at' => $token->last_seen_at?->toIso8601String(),
        ], 'Bildirim cihazı kaydedildi.', $token->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        PushDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $validated['token'])
            ->update(['enabled' => false]);

        return $this->success(null, 'Bildirim cihazı pasifleştirildi.');
    }
}
