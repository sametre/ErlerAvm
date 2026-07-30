<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApiClients\CreateApiClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApiClientRequest;
use App\Models\ApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiClientController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ApiClients', [
            'clients' => ApiClient::query()
                ->with('createdBy:id,name')
                ->latest()
                ->get()
                ->map(fn (ApiClient $client) => $this->payload($client)),
            'abilities' => config('api.abilities'),
            'docsUrl' => route('developer.docs'),
        ]);
    }

    public function store(StoreApiClientRequest $request, CreateApiClient $createApiClient): JsonResponse
    {
        $credentials = $createApiClient->handle($request->user(), $request->validated());

        return response()->json([
            'client' => $this->payload($credentials['client']->load('createdBy:id,name')),
            'credentials' => [
                'key' => $credentials['client']->key,
                'secret' => $credentials['secret'],
            ],
            'message' => 'API kimlik bilgileri oluşturuldu. Secret yalnızca bir kez gösterilir.',
        ], 201)->header('Cache-Control', 'no-store, private');
    }

    public function destroy(Request $request, ApiClient $apiClient): JsonResponse
    {
        abort_unless(
            $request->user()?->can('api-clients.manage') === true
                || $request->user()?->hasRole('Super Admin') === true,
            403,
        );

        $apiClient->forceFill(['revoked_at' => now()])->save();

        return response()->json([
            'client' => $this->payload($apiClient->fresh()->load('createdBy:id,name')),
            'message' => 'API istemcisi iptal edildi.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ApiClient $client): array
    {
        return [
            'public_id' => $client->public_id,
            'name' => $client->name,
            'key' => $client->key,
            'abilities' => $client->abilities,
            'allowed_ips' => $client->allowed_ips,
            'created_by' => $client->createdBy?->name,
            'last_used_at' => $client->last_used_at?->toIso8601String(),
            'expires_at' => $client->expires_at?->toIso8601String(),
            'revoked_at' => $client->revoked_at?->toIso8601String(),
            'created_at' => $client->created_at?->toIso8601String(),
        ];
    }
}
