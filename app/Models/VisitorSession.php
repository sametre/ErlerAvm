<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    protected $fillable = [
        'session_hash',
        'visitor_uid_hash',
        'user_id',
        'ip_hash',
        'user_agent_hash',
        'last_path',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }
}
