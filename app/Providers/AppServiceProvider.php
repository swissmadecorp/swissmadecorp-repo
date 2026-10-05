<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Observers\ProductObserver;
use App\Models\Product;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Notifications\FirebaseChannel;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;
use App\Events\InventoryChanged;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

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
        Product::observe(\App\Observers\WatchReminderObserver::class);
        // Product::observe(ProductObserver::class);
        $this->commands([
            \App\Jobs\ExportProductsCsv::class,
        ]);

        // This is how Laravel knows what to do when you specify 'firebase' in via()
        Notification::extend('firebase', function ($app) {
            return new FirebaseChannel($app->make(Factory::class));
        });

        DB::listen(function (QueryExecuted $query) {

        $sql = strtolower(
            preg_replace(
                '/\s+/',
                ' ',
                trim($query->sql)
            )
        );

        /*
        Detect:

        qty = ...
        quantity = ...

        This works whether your Laravel code uses:

        $product->save()

        Product::where(...)->update(...)

        DB::table(...)->update(...)
        */

        $quantityChanged =
            str_starts_with($sql, 'update ')
            &&
            preg_match(
                '/[`"]?(qty|quantity)[`"]?\s*=/i',
                $sql
            );


        /*
        Also detect new/deleted products.

        That gives the iPhone the same realtime
        behavior for newly added inventory.
        */

        $productInserted =
            preg_match(
                '/^insert into\s+[`"]?products[`"]?/i',
                $sql
            );


        $productDeleted =
            preg_match(
                '/^delete from\s+[`"]?products[`"]?/i',
                $sql
            );


        if (
            !$quantityChanged &&
            !$productInserted &&
            !$productDeleted
        ) {
            return;
        }


        \Log::info(
            'Inventory changed - broadcasting inventory.changed'
        );


        broadcast(
            new InventoryChanged()
        );
    });

    }
}
