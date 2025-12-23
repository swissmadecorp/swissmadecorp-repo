<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Product;

class productOnHold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:onhold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Admin about past due invoices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $products = Product::select('id','reserve_for','reserve_amount','reserve_date')
            ->where("reserve_date",'<>',NULL)
            ->get();
        
            
        foreach ($products as $product) {
            $dt = Carbon::createFromFormat('Y-m-d H:i:s', $product->reserve_date);
            $dt2 = Carbon::now();
            
            //if ($product->id== 11693) 
            //dd($diffInMin);
            
            if ($product->reserve_for!="Shopping Cart") {
                $length = $dt2->diffInDays($dt);
                
                if ($length>3 || $length<0) {
                    $this->updateReserve($product);
                }
            } else  {
                $diffInMin = $dt2->diffInMinutes($dt);
                
                if ($diffInMin <= 0 || $diffInMin > 15) {
                    $this->updateReserve($product);
                }
            }

        }

        $this->info('product lookup has run successfully');
    }
}
