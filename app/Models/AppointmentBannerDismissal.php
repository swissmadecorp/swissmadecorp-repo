<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentBannerDismissal extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'booking_updated_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_updated_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }
}
