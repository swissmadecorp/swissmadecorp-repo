<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    protected $guarded = [];
    public $timestamps = true;
    protected $dates = ['start_date','end_date'];

    public function setProductAttribute($value) {
        if ($value)
            $this->attributes['product'] = serialize($value);
    }
}
