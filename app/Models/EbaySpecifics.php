<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbaySpecifics extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function specifics() {
        return $this->belongsToMany(Specifics::class,'id','ebay_specifics_id');
    }

}
