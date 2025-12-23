<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Estimate extends Model
{
    
    protected $guarded = ['id','product_name','qty','price','serial','payment','created_at','newcost','op_id','product_id','printAfterSave','purchased_from'];

    public function customers() {
        return $this->belongsToMany(Customer::class)
            ->withPivot('customer_id')->orderBy('pivot_customer_id','asc');
    }

    public function products() {
        return $this->belongsToMany(Product::class)->withPivot('id','qty','price','product_name');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function scopeTotalQty($query) {
        return $query->with(['returns' => function($q) 
            {
                return $q->select(DB::raw('product_id,sum(qty)'))
                    ->groupBy('product_id');
            }]);
    }
// select product_id, sum(qty) total from `order_returns` 
//     group by product_id
}
