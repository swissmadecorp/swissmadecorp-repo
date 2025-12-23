<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheShow extends Model
{
    protected $guard = [];
    public $timestamps = true;

    public function product() {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
