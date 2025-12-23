<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\FirebaseChannel;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        view()->composer('exchange-rate', function($view) {
            $view->with('rates',ExchangeRate::all());
        });

        view()->composer('layouts.sidebar-new', function($view) {
            $categories = Category::whereHas('products',function($query) {
                $query->where('p_qty','>',0);
                $query->whereIn('p_status',array(0,1,2,5));
            })->orderBy('category_name')->get();

            $view->with('brands',$categories);
        });

        view()->composer('layouts.sidebar-new', function($view) {
            $casesizes = Product::select('p_casesize')
                ->where('p_qty','>',0)
                ->orderBy('p_casesize','asc')
                ->groupBy('p_casesize')->get();

            $view->with('casesizes',$casesizes);
        });

        // view()->composer('*', function($view) {
        //     $paths = explode('/',url()->current());
        //     $routes = array();

        //     foreach ($paths as $path) {
        //         if ($path!=$_SERVER['HTTP_HOST'] && $path!='' && $path!='https:' && !is_numeric($path))
        //             $routes[] = $path;
        //     }

        //     $view->with('routes',$routes);
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // This is how Laravel knows what to do when you specify 'firebase' in via()
        Notification::extend('firebase', function ($app) {
            return new FirebaseChannel($app->make(Factory::class));
        });
    }
}
