<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Product;

class FacebookFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:feed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic Facebook Feed Generator';

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
        $products=Product::where('p_price3P','<>',0)
            ->where('p_qty','>',0)
            ->where('group_id',0)
            ->whereIn('p_condition',[1,2,3])
            ->where('p_newprice','>', '10')
            ->where('p_reference','<>', '')
            ->get();

        If (unlink('public/uploads/facebookfeed.csv')) {
           $fp = fopen('public/uploads/facebookfeed.csv', 'w');
        } else {
            $fp = fopen('public/uploads/facebookfeed.csv', 'w');
        }
        
        $headers = array(
            "id", //1
            "title", //2
            "override",
            "description", //3
            "availability",  //5
            "condition",  // 11
            "price", // 6
            "inventory",
            "link", // 3.5
            "image_link", //4
            "brand",  // 8
            "additional_image_link",
            "age_group", // adult 12
            "color", // facecolor 13
            "gender",  // 14
            "google_product_category", // 7
            "MPN",  // 9
            "identifier_​exists", // 10
            "material",  // 15
            "shipping", // 15.5
        );

        fputcsv($fp,$headers);

        foreach ($products as $product) {
            $img = $product->images->first();
            if ($img) {
                $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
                $webprice = number_format($webprice,2,'.','') .' USD';
                $path = 'https://swissmadecorp.com/images/'.$img->location;
                $instock = $product->p_qty > 0 ? 'in stock' : 'out of stock';
                $gender = Gender()->get($product->p_gender);
                if ($gender=="Men's")
                    $gender = "Male";
                elseif ($gender == "Women's")
                    $gender = "Female";

                $material = Materials()->get($product->p_material);

                $condition = $product->p_condition;
                if (in_array($condition,[1,2,4,5]))
                    $condition = 'new';
                else $condition = 'used_like_new';

                //echo $product->id . ', ';
                $field = array(
                    $product->id, // 1
                    $product->title, // 2
                    "US",
                    $product->keyword_build, // 3
                    $instock, // 5
                    $condition, // 11
                    $webprice, // 6
                    $product->p_qty,
                    "https://swissmadecorp.com/" . $product->slug, // 3.5
                    $path, // 4
                    $product->categories->first()->category_name, // 8
                    "",
                    'adult', // 12
                    $product->p_color, // 13
                    $gender, // 14
                    "Apparel & Accessories > Jewelry > Watches", // 7
                    $product->p_reference,  // 9
                    "false", // 10
                    $material, // 15
                    "US:NY:Overnight:40.00 USD", // 15.5
                );

                fputcsv ($fp,$field);
            } 
        }

        fclose($fp);

        $this->info('Product export has run successfully');
    }
}
