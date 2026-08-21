<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbayListing extends Model
{
    protected $guarded = [];

    public static function errorIndicatesExistingListing(?string $error): bool
    {
        $error = strtolower((string) $error);

        return str_contains($error, 'sku must be unique')
            || str_contains($error, 'duplicate listing policy');
    }

    public function representsExistingListing(): bool
    {
        return ! empty($this->listitem)
            || self::errorIndicatesExistingListing($this->errors);
    }

    public function products() {
        return $this->belongsTo(Product::class,'product_id','id');
    }

}
