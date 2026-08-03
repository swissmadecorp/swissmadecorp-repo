<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCredit extends Model
{
    protected $fillable = ['amount','customer_id'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
