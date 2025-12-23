<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $guarded = [];
    use FullTextSearch;
    
    protected $searchable = [
        'criteria'
    ];

    public function setProductConditionAttribute($value) {
        $this->attributes['product_condition'] = serialize($value);
    }

    public function setBoxpapersAttribute($value) {
        $this->attributes['boxpapers'] = serialize($value);
    }
}
