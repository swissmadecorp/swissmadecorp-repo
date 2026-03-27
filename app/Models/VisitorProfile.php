<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorProfile extends Model
{
    protected $fillable = [
        'visitor_key',
        'display_name',
        'email',
        'last_known_ip',
        'country',
        'city',
        'visit_count',
        'first_seen_at',
        'last_seen_at',
        'last_identified_at',
        'metadata',
    ];

    protected $casts = [
        'visit_count' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_identified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class)->orderByDesc('started_at');
    }
}
