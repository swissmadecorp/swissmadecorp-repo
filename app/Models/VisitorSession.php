<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorSession extends Model
{
    protected $fillable = [
        'visitor_profile_id',
        'session_token',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'landing_url',
        'landing_path',
        'landing_title',
        'current_url',
        'current_path',
        'current_title',
        'referrer_url',
        'referrer_host',
        'page_views',
        'started_at',
        'last_seen_at',
        'ended_at',
        'metadata',
    ];

    protected $casts = [
        'page_views' => 'integer',
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(VisitorProfile::class, 'visitor_profile_id');
    }
}
