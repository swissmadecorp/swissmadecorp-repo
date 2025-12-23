<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmazonListings extends Model
{
    protected $fillable = ['product_id','listprice','status','submissionId'];
}
