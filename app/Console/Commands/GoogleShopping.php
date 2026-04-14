<?php

namespace App\Console\Commands;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $unavailableCutoff = Carbon::now()->subDays(30);
        $expirationDate = Carbon::now('America/New_York')->addDays(3)->format('Y-m-d\TH:iO');

        $products = Product::with(['images', 'categories'])
            ->where('p_price3P', '<>', 0)
            ->where('group_id', 0)
            ->whereIn('p_condition', [1, 2, 3])
            ->where('p_newprice', '>', '10')
            ->where('p_reference', '<>', '')
            ->where(function ($query) use ($unavailableCutoff) {
                $query->where('p_qty', '>', 0)
                    // Keep recently unavailable items in the feed long enough for
                    // Merchant Center to process the status change and expiration.
                    ->orWhere(function ($query) use ($unavailableCutoff) {
                        $query->where('p_qty', '<=', 0)
                            ->where('updated_at', '>=', $unavailableCutoff);
                    });
            })
            ->get();

        $fp = fopen('public/uploads/google.txt', 'w');
        $headers = array(
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'expiration_date',
            'price',
            'google_product_category',
            'brand',
            'MPN',
            'identifier_exists',
            'condition',
            'age_group',
            'color',
            'gender',
            'material',
            'shipping',
            'tax',
        );

        fputcsv($fp, $headers);

        foreach ($products as $product) {
            $img = $product->images->first();
            if ($img) {
                $webprice = ceil($product->p_newprice + ($product->p_newprice * CCMargin()));
                $webprice = number_format($webprice, 2, '.', '') . ' USD';
                $path = 'https://swissmadecorp.com/images/' . $img->location;
                $availability = $product->p_qty > 0 ? 'in_stock' : 'out_of_stock';
                $productExpirationDate = $product->p_qty > 0 ? '' : $expirationDate;
                $gender = Gender()->get($product->p_gender);

                if ($gender == "Men's") {
                    $gender = 'male';
                } else {
                    $gender = 'female';
                }

                $title = str_replace("'", '', $product->title);
                $material = Materials()->get($product->p_material);

                $condition = strtolower(Conditions()->get($product->p_condition));
                if ($condition == 'unworn') {
                    $condition = 'new';
                } else {
                    $condition = 'used';
                }

                if ($product->categories) {
                    $categoryName = $product->categories->first()->category_name;
                } else {
                    $categoryName = '';
                }

                $field = array(
                    $product->id,
                    $title,
                    $product->keyword_build,
                    'https://swissmadecorp.com/product-details/' . $product->slug,
                    $path,
                    $availability,
                    $productExpirationDate,
                    $webprice,
                    '201',
                    $categoryName,
                    $product->p_reference,
                    'false',
                    $condition,
                    'adult',
                    $product->p_color,
                    $gender,
                    $material,
                    'US:NY:Overnight:79.00 USD',
                    'US:NY:8.875:n',
                );

                fputcsv($fp, $field);
            }
        }

        fclose($fp);

        $this->info('Product export has run successfully');
    }
}
