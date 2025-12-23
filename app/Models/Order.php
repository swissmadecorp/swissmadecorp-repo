<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Order extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id','product_name','qty','price','serial','payment','created_at','newcost','op_id','product_id','printAfterSave'];

    public function customers() {
        return $this->belongsToMany(Customer::class)
            ->withPivot('customer_id')->orderBy('pivot_customer_id','asc');
    }

    public function products() {
        return $this->belongsToMany(Product::class)->withPivot('qty','cost','price','serial','product_name','op_id');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function returns() {
        return $this->belongsToMany(Returns::class)->withPivot('product_id','qty','created_at');
    }

    public function scopeTotalQty($query) {
        return $query->with(['returns' => function($q) 
            {
                return $q->select(DB::raw('product_id,sum(qty)'))
                    ->groupBy('product_id');
            }]);
    }
    
    public function scopeSortit($query) {
        return $query->orderBy('created_at','desc');
    }

    private function localize_us_number($phone) {
        $numbers_only = preg_replace("/[^\d]/", "", $phone);
        return preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only);
    }

    public function setBFirstnameAttribute($value) {
        $this->attributes['b_firstname'] = ucwords($value);
    }

    public function setBLastnameAttribute($value) {
        $this->attributes['b_lastname'] = ucwords($value);
    }

    public function setBAddress1Attribute($value) {
        $this->attributes['b_address1'] = ucwords($value);
    }

    public function setBAddress2Attribute($value) {
        $this->attributes['b_address2'] = ucwords($value);
    }

    public function setSFirstnameAttribute($value) {
        $this->attributes['s_firstname'] = ucwords($value);
    }

    public function setSLastnameAttribute($value) {
        $this->attributes['s_lastname'] = ucwords($value);
    }

    public function setSAddress1Attribute($value) {
        $this->attributes['s_address1'] = ucwords($value);
    }

    public function setSAddress2Attribute($value) {
        $this->attributes['s_address2'] = ucwords($value);
    }

    public function setBPhoneAttribute($value) {
        $this->attributes['b_phone'] = $this->localize_us_number($value);
    }

    public function setSPhoneAttribute($value) {
        $this->attributes['s_phone'] = $this->localize_us_number($value);
    }

    public function setSCityAttribute($value) {
        $this->attributes['s_city'] = ucwords($value);
    }

    public function setBCityAttribute($value) {
        $this->attributes['b_city'] = ucwords($value);
    }

    public function setPoAttribute($value) {
        $this->attributes['po'] = strtoupper($value);
    }

    // public function setBCompanyAttribute($value) {
    //     $this->attributes['b_company'] = ucwords($value);
    // }

    // public function setSCompanyAttribute($value) {
    //     $this->attributes['s_company'] = ucwords($value);
    // }

    public function setCreatedAtAttribute($value) {
        $this->attributes['created_at'] = ucwords($value);
    }
    
// select product_id, sum(qty) total from `order_returns` 
//     group by product_id
}
