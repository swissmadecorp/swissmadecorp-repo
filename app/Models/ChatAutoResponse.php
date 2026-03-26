<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAutoResponse extends Model
{
    protected $fillable = [
        'key',
        'label',
        'message',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];
}
