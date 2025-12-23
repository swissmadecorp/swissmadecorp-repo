<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ClearDiscountPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs through discount rules and clears all products that were discounted that are expired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discountProducts = $this->discountProducts();
        
        //Product::whereIn('id',$discountProducts)->searchable();
        return 0;
    }

    private function discountRule2() {
        $now = (date('Y-m-d',strtotime(now())));
                
        $discountRule = \App\Models\DiscountRule::whereIn('action',[4,5])
            ->where('end_date','<=',$now)
            ->get();

        return $discountRule;
    }

    private function discountProducts() {
        $rules = $this->discountRule2();
        $products = array();

        foreach ($rules as $rule) {
            if (is_array(unserialize($rule->product))) {
                foreach (unserialize($rule->product) as $product) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }
}
