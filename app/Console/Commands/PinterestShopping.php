<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Product;

class PinterestShopping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinterest:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create product list for pinterest shopping';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function extractTextSections($input) {
        // Define the patterns for each section
        $patterns = [
            'keywords' => '/### SEO Keywords:\s*(.*?)(?=\n\s*### Meta Title:|\n\s*### Meta Description:|\n\s*### Product Description:|$)/s',
            'title' => '/### Meta Title:\s*(.*?)(?=\n\s*### SEO Keywords:|\n\s*### Meta Description:|\n\s*### Product Description:|$)/s',
            'meta_description' => '/### Meta Description:\s*(.*?)(?=\n\s*### SEO Keywords:|\n\s*### Meta Title:|\n\s*### Product Description:|$)/s',
            'product_description' => '/### Product Description:\s*(.*?)(?=\n\s*### SEO Keywords:|\n\s*### Meta Title:|\n\s*### Meta Description:|$)/s',
        ];
    
        // Array to store the extracted sections
        $sections = [];
    
        // Loop through each pattern and extract the corresponding text
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                $sections[$key] = trim($matches[1]);
            }
        }
    
        return $sections;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle1() {
        $fp = fopen('public/uploads/ai.txt', 'r') or die("Unable to open file!");
        
        $file = fread($fp,filesize("public/uploads/ai.txt"));

        fclose($fp);
        dd($this->extractTextSections($file));
        
    }

    public function handle()
    {
        $products=Product::where('p_price3P','<>',0)
            ->where('group_id',0)
            ->where('p_qty', 1)
            ->whereIn('p_condition',[1,2])
            ->where('p_newprice','>', '10')
            ->where('p_reference','<>', '')
            ->get();

        $fp = fopen('public/uploads/pinterest.txt', 'w');
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
                $path = 'https://swissmadecorp.com/public/images/'.$img->location;
                $instock = $product->p_qty > 0 ? 'in stock' : 'out of stock';
                $gender = Gender()->get($product->p_gender);
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
                    $product->title, // 2
                    $product->keyword_build, // 3
                    "https://swissmadecorp.com/watch-products/" . $product->slug, // 3.5
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
                    "US:NY:Overnight:40.00 USD", // 15.5
                    "US:NY:8.875:n" // 16
                );

                fputcsv ($fp,$field);
            } 
        }

        fclose($fp);

        $this->info('Pinterest product export has run successfully');
    }
}
