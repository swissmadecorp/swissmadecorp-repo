<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{ 
    protected $guarded = [];

    public function jobs() {
        return $this->hasMany(RepairProduct::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class);
    }
}

// countries
//     id - integer
//     name - string

// users
//     id - integer
//     country_id - integer
//     name - string

// posts
//     id - integer
//     user_id - integer
//     title - string