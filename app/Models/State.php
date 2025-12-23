<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function country() {
        $this->belongsTo(Country::class);
    }
}
