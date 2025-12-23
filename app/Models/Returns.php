<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    protected $fillable = ['order_id','product_id','qty','comment','reason'];
    public $timestamps = false;
    
    public function orders() {
        return $this->hasMany(Order::class);
    }
 
    public function products() {
        return $this->belongsToMany(Product::class)->withPivot('qty','price','serial');
    }
}
