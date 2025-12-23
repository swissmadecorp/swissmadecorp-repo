<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ProductModelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Product::observe(\App\Observer\ProductObserver::class);
        //\App\Cart::observe(\App\Observer\CartObserver::class);

        //\App\Order::observe(\App\Observer\OrderObserver::class);
    }
}
