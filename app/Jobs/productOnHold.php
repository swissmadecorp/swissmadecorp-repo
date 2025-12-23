<?php

namespace App\Jobs;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Cart;

class productOnHold
{
    
    private function updateReserve($product) {

        $product->update([
            "reserve_for" => '',
            "reserve_amount" => 0,
            "reserve_date" => NULL,
            "p_status" => 0
        ]);

        //\Log::info($product->reserve_for);
    }

    /**
     * Execute the job.
     */
    public static function handleMethod()
    {
        $products = Product::select('id','reserve_for','reserve_amount','reserve_date')
        ->where("reserve_date",'<>',NULL)
        ->get();
    
        foreach ($products as $product) {
            $expiryTime = Carbon::parse($product->reserve_date)->isPast();
            
            if ($expiryTime) {
                $product->update([
                    "reserve_for" => '',
                    "reserve_amount" => 0,
                    "reserve_date" => NULL,
                    "p_status" => 0
                ]);
            }


        }

        // $this->info('product lookup has run successfully');
    }
}
