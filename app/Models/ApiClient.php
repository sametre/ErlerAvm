<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiClient extends Model
{
    protected $fillable = [
        'public_id',
        'created_by',
        'name',
        'key',
        'secret_hash',
        'abilities',
        'allowed_ips',
        'expires_at',
        'revoked_at',
        'last_used_at',
    ];

    protected $hidden = ['secret_hash'];

    protected static function booted(): void
    {
        static::creating(function (ApiClient $client): void {
            $client->public_id ??= (string) str()->ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'allowed_ips' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where(fn (Builder $query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));
    }

    public function hasAbility(string $ability): bool
    {
        return in_array('*', $this->abilities, true)
            || in_array($ability, $this->abilities, true);
    }
}
