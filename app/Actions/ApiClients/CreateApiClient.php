<?php

namespace App\Actions\ApiClients;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateApiClient
{
    /**
     * @param  array{name: string, abilities: array<int, string>, allowed_ips?: array<int, string>|null, expires_at?: string|null}  $attributes
     * @return array{client: ApiClient, secret: string}
     */
    public function handle(User $creator, array $attributes): array
    {
        $secret = Str::random(64);

        $client = ApiClient::query()->create([
            'created_by' => $creator->id,
            'name' => $attributes['name'],
            'key' => 'erl_live_'.Str::lower(Str::random(32)),
            'secret_hash' => Hash::make($secret),
            'abilities' => $attributes['abilities'],
            'allowed_ips' => $attributes['allowed_ips'] ?? null,
            'expires_at' => $attributes['expires_at'] ?? null,
        ]);

        return ['client' => $client, 'secret' => $secret];
    }
}
