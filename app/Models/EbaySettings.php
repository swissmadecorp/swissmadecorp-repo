<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbaySettings extends Model
{
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'oauth_access_token' => 'encrypted',
            'oauth_refresh_token' => 'encrypted',
            'oauth_access_token_expires_at' => 'datetime',
            'oauth_refresh_token_expires_at' => 'datetime',
        ];
    }
}
