<?php

namespace App\Libs;
use App\Models\Cart;

class _Cart {
    public function get() {
        $cart = Cart::count();
        $incart = $cart>0 ? "<span class='incart'>$cart</span>" : '';

        return $incart;
    }
}