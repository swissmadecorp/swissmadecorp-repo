<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Prunable;

class CustomerChat extends Model
{
    use Prunable;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_OFFLINE = 'offline';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'public_token',
        'assigned_user_id',
        'status',
        'visitor_name',
        'visitor_email',
        'last_message_at',
        'last_customer_message_at',
        'last_staff_message_at',
        'customer_last_seen_at',
        'staff_last_seen_at',
        'assigned_at',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_customer_message_at' => 'datetime',
        'last_staff_message_at' => 'datetime',
        'customer_last_seen_at' => 'datetime',
        'staff_last_seen_at' => 'datetime',
        'assigned_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Define which records should be pruned.
    */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(1));
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CustomerChatMessage::class)->orderBy('id');
    }


}
