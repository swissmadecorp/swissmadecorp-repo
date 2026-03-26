<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerChatMessage extends Model
{
    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_STAFF = 'staff';
    public const SENDER_SYSTEM = 'system';

    protected $fillable = [
        'customer_chat_id',
        'sender_type',
        'user_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_mime_type',
        'attachment_size',
        'is_auto_response',
    ];

    protected $casts = [
        'is_auto_response' => 'boolean',
        'attachment_size' => 'integer',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(CustomerChat::class, 'customer_chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
