<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Product;

class GoogleShopping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create product list for google shopping';

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
     * @return mixed
     */
    public function handle()
    {
        $products=Product::where('p_price3P','<>',0)
            ->where('p_qty', ">", 0)
            ->where('group_id',0)
            ->whereIn('p_condition',[1,2,3])
            ->where('p_newprice','>', '10')
            ->where('p_reference','<>', '')
            ->get();

        $fp = fopen('public/uploads/google.txt', 'w');
        $headers = array(
            "id", //1
            "title", //2
            "description", //3
            "link", // 3.5
            "image_link", //4
            "availability",  //5
            "price", // 6
            "google_product_category", // 7
            "brand",  // 8
            "MPN",  // 9
            "identifier_​exists", // 10
            "condition",  // 11
            "age_group", // adult 12
            "color", // facecolor 13
            "gender",  // 14
            "material",  // 15
            "shipping", // 15.5
            "tax",  // 16 US:NY:8.875:n
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
                if ($gender == "Men's")
                    $gender = "male";
                else $gender = "female";

                $title = str_replace("'","",$product->title);
                    
                $material = Materials()->get($product->p_material);

                $condition = strtolower(Conditions()->get($product->p_condition));
                if ($condition == 'unworn')
                    $condition = 'new';
                else $condition = 'used';
                
                if ($product->categories)
                    $categoryName = $product->categories->first()->category_name;
                else $categoryName = '';

                $field = array(
                    $product->id, // 1
                    $title, // 2
                    $product->keyword_build, // 3
                    "https://swissmadecorp.com/product-details/" . $product->slug, // 3.5
                    $path, // 4
                    $instock, // 5
                    $webprice, // 6
                    "201", // Apparel & Accessories > Jewelry > Watches 7
                    $categoryName, // 8
                    $product->p_reference,  // 9
                    "false", // 10
                    $condition, // 11
                    'adult', // 12
                    $product->p_color, // 13
                    $gender, // 14
                    $material, // 15
                    "US:NY:Overnight:79.00 USD", // 15.5
                    "US:NY:8.875:n" // 16
                );

                fputcsv ($fp,$field);
            } 
        }

        fclose($fp);

        $this->info('Product export has run successfully');
    }
}
