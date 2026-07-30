<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiClient
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');
        $secret = $request->header('X-API-Secret');

        if (! is_string($key) || ! is_string($secret)) {
            return $this->unauthorized();
        }

        $client = ApiClient::query()->active()->where('key', $key)->first();

        if (! $client || ! Hash::check($secret, $client->secret_hash)) {
            return $this->unauthorized();
        }

        if ($client->allowed_ips !== null && ! in_array($request->ip(), $client->allowed_ips, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu istemci IP adresinden erişime izin verilmiyor.',
            ], 403);
        }

        if (! $client->hasAbility('catalog:read')) {
            return response()->json([
                'success' => false,
                'message' => 'API istemcisinin bu işlem için yetkisi bulunmuyor.',
            ], 403);
        }

        $request->attributes->set('api_client', $client);
        $client->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Geçersiz veya süresi dolmuş API kimlik bilgileri.',
        ], 401);
    }
}
