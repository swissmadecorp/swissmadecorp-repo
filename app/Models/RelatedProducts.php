<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatedProducts extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    
    public function product() {
        return $this->belongsTo(Product::class,'product_id','id'); 
    }

}
