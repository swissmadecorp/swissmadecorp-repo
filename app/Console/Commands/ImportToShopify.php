<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Image;

class ImportToShopify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:export {param}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create product list for shopify';

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
        $param = $this->argument('param');  // get parameter from schedule Kernel
        if ($param == "new") {
            $products=Product::where('p_price3P','<>',0)
            ->where('group_id',0)
            ->where('p_qty',">",0)
            ->whereIn('p_condition',[1,2,3])
            ->where('p_newprice','>', '10')
            ->where('p_reference','<>', '')
            ->get();
        } else {
            $to = date("Y-m-d 23:59:59",strtotime(now()));
            $from = date("Y-m-d 00:00:00",strtotime("-1 day"));

            $products = Product::whereHas('orders', function($query) use($from,$to) {
                $query->whereBetween('created_at',[$from,$to]);
            })
            ->where('group_id',0)
            //->where('p_qty',">",0)
            ->whereIn('p_condition',[1,2,3])
            ->where('p_newprice','>', '10')
            ->where('p_reference','<>', '')
            ->get();
        }
        
        $fp = fopen("public/uploads/shopify-$param.csv", 'w');
        $headers = array(
            "Handle", //1
            "Title", //2
            "Body (HTML)", //3
            "Vendor", //4
            "Standardized Product Type",  //5
            "Custom Product Type", // 6
            "Tags", // 7
            "Published", // 8
            "Option1 Name",  // 9
            "Option1 Value",  // 10
            "Option2 Name",  // 11
            "Option2 Value", // 12
            "Option3 Name",  // 13
            "Option3 Value", // 14
            "Variant SKU", // 15
            "Variant Grams", // 16
            "Variant Inventory Tracker", // 17
            "Variant Inventory Qty", // 18
            "Variant Inventory Policy", // 19
            "Variant Fulfillment Service", // 20
            "Variant Price", // 21
            "Variant Compare At Price", // 22
            "Variant Requires Shipping", // 23
            "Variant Taxable", // 24
            "Variant Barcode", // 25
            "Image Src", // 26
            "Image Position", // 27
            "Image Alt Text", // 28
            "Gift Card", // 29
            "SEO Title", // 30
            "SEO Description", // 31
            "Google Shopping / Google Product Category", // 32
            "Google Shopping / Gender", // 33
            "Google Shopping / Age Group", // 34 
            "Google Shopping / MPN", // 35
            "Google Shopping / AdWords Grouping", // 36
            "Google Shopping / AdWords Labels", // 37
            "Google Shopping / Condition", // 38
            "Google Shopping / Custom Product", // 39
            "Google Shopping / Custom Label 0", // 40
            "Google Shopping / Custom Label 1", // 41
            "Google Shopping / Custom Label 2", // 42
            "Google Shopping / Custom Label 3", // 43
            "Google Shopping / Custom Label 4", // 44
            "Variant Image", // 45
            "Variant Weight Unit", // 46
            "Variant Tax Code", // 47
            "Cost per item", // 48
            "Price / International", // 49
            "Compare At Price / International", // 50
            "Status" // 51
        );

        fputcsv($fp,$headers);

        foreach ($products as $product) {
            $img = $product->images->first();
            if ($img) {
                
                $webprice = ceil($product->p_newprice+($product->p_newprice*.029));
                
                $instock = $product->p_qty > 0 ? 'in stock' : 'out of stock';
                $gender = Gender()->get($product->p_gender);
                $material = Materials()->get($product->p_material);

                $titleCondition = Conditions()->get($product->p_condition);
                if ($titleCondition == 'Unworn' || $titleCondition == "New") {
                    //$titleCondition = "Unworn";
                    $condition = 'new';
                } else {
                    //$titleCondition = "Pre-Owned";
                    $condition = 'used';
                }

                $html = "<table class='product-details'><tr>";
                $html .= "<td>Stock No:</td>";
                $html .= "<td>$product->id</td></tr>";
                $html .= "<tr><td>Brand:</td>";
                if (isset($product->categories->category_name)) {
                    $html .= "<td>".$product->categories->category_name . "</td>";
                } else {
                    $html .= "<td>N/A</td>";
                }
                $html .= "</tr>";
                if ($product->p_model) {
                    $html .= "<tr>";
                    $html .= "<td>Model:</td>";
                    $html .= "<td>$product->p_model</td>";
                    $html .= "</tr>";
                }

                if ($product->p_casesize) {
                    $html .= "<tr>";
                    $html .= "<td>Case Size:</td>";
                    $html .= "<td>$product->p_casesize</td>";
                    $html .= "</tr>";
                }
                if ($product->p_reference) {
                    $html .= "<tr>";
                    $html .= "<td>Reference:</td>";
                    $html .= "<td>$product->p_reference</td>";
                    $html .= "</tr>";
                }
                if ($product->serial_code) {
                    $html .= "<tr>";
                    $html .= "<td>Serial</td>";
                    $html .= "<td>$product->serial_code</td>";
                    $html .= "</tr>";
                }
                if ($product->p_color) {
                    $html .= "<tr>";
                    $html .= "<td>Face Color:</td>";
                    $html .= "<td>$product->p_color</td>";
                    $html .= "</tr>";
                }
                if ($product->p_year) { 
                    $html .= "<tr>";
                    $html .= "<td>Production Year:</td>";
                    $html .= "<td>$product->p_year </td>";
                    $html .= "</tr>";
                } 
                if (($product->p_box==0 || $product->p_box==1) && $product->group_id == 0) { 
                    $html .= "<tr>";
                    $html .= "<td>Box:</td>";
                    $html .= "<td>" . ($product->p_box==1 ? "Yes" : "No") . "</td>";
                    $html .= "</tr>";
                } 
                if (($product->p_papers==0 || $product->p_papers==1) && $product->group_id == 0) { 
                    $html .= "<tr>";
                    $html .= "<td>Papers:</td>";
                    $html .= "<td>" . ($product->p_papers==1 ? "Yes" : "No") . "</td>";
                    $html .= "</tr>";
                } 
                if ($product->p_strap>0) { 
                    $html .= "<tr>";
                    $html .= "<td>Strap/Band:</td>";
                    $html .= "<td>" . Strap()->get($product->p_strap) . "</td>";
                    $html .= "</tr>";
                } 
                if ($product->p_clasp>0) { 
                    $html .= "<tr>";
                    $html .= "<td>Clasp Type:</td>";
                    $html .= "<td>" . Clasps()->get($product->p_clasp) . "</td>";
                    $html .= "</tr>";
                }  
                if ($product->p_material>0) { 
                    $html .= "<tr>";
                    if ($product->group_id == 0) { 
                        $html .= "<td>Case Material:</td>";
                        $html .= "<td>" . Materials()->get($product->p_material) . "</td>";
                    } elseif ($product->group_id == 1) { 
                        $html .= "<td> Material:</td>";
                        $html .= "<td>" . MetalMaterial()->get($product->p_material) . "</td>";
                    } 
                    $html .= "</tr>";
                } 
                if ($product->p_bezelmaterial>0) {
                    $html .= "<tr>";
                    $html .= "<td>Bezel Material:</td>";
                    $html .= "<td>";
                    if ($product->group_id == 0) { 
                        $html .= BezelMaterials()->get($product->p_bezelmaterial);
                    } elseif ($product->group_id == 1) { 
                        $html .= BezelMetalMaterial()->get($product->p_bezelmaterial);
                    } 
                    $html .= "</td>";
                    $html .= "</tr>";
                } 
                if ($product->water_resistance) { 
                    $html .= "<tr>";
                    $html .= "<td>Water Resistance:</td>";
                    $html .= "<td>" . $product->water_resistance . "</td>";
                    $html .= "</tr>";
                } 
                if ($product->movement>-1) { 
                    $html .= "<tr>";
                    $html .= "<td>Movement:</td>";
                    $html .= "<td>" . Movement()->get($product->movement) . "</td>";
                    $html .= "</tr>";
                } 
                if(!empty($custom_columns)) { 
                    foreach ($custom_columns as $column) {
                        if ($product->$column) {
                            $html .= "<tr>";
                            $html .= "<td>" .ucwords(str_replace(['-','c_'], ' ', $column)) . "</td>";
                            $html .= "<td>$product->$column</td>";
                            $html .= "</tr>";
                        } 
                    }
                } 
                
                $html .= "</table>";

                $published = false;
                if ($product->p_qty== 0) {
                    $status = "archived";
                } else {
                    $status = "active";
                    $published = true;
                }

                $title = $titleCondition . " " . $product->title;
                $path = 'https://swissmadecorp.com/public/images/thumbs/'.$img->location;

                $fields = array(
                    $title, //A
                    $title, // B
                    $html, // C
                    $product->categories->category_name, // D
                    "Apparel & Accessories > Jewelry > Watches", // E
                    "Watches", // F
                    $product->keyword_build, // G
                    $published, // H
                    $title, // I
                    $title, // 10
                    "", // 11
                    "", // 12
                    "", // 13
                    "", // 14
                    $product->id, // 15
                    "",  // 16
                    "shopify", // 17
                    $product->p_qty, // 18
                    "deny", // 19
                    "manual", // 20
                    $webprice, // 21
                    "", // 22
                    TRUE, // 23
                    TRUE, // 24
                    "", // 25
                    $path, // 26
                    1, // 27
                    $title, // 28
                    FALSE, // 29
                    $title, // 30
                    $title, // 31
                    "Apparel & Accessories > Jewelry > Watches", // 32
                    $gender, // 33
                    "Adult", // 34
                    "", // 35
                    "", // 36
                    "", // 37
                    $condition, // 38
                    "", // 39
                    "", // 40
                    "", // 41
                    "", // 42
                    "", // 43
                    "", // 44
                    "", // 45
                    "", // 46
                    "", // 47
                    "", // 48
                    "", // 49
                    "", // 50
                    $status // 51
                );
                
                fputcsv ($fp, $fields);

                if (count($product->images)>1) {
                    foreach ($product->images as $image)  {
                        $path = 'https://swissmadecorp.com/public/images/thumbs/'.$image->location;
                        if ($path != 'https://swissmadecorp.com/public/images/thumbs/'.$img->location) {
                            $fields = array(
                                $title, //A
                                "",
                                "",
                                "",
                                "",
                                "",
                                "",
                                "",
                                "", // I
                                "", // 10
                                "", // 11
                                "", // 12
                                "", // 13
                                "", // 14
                                "", // 15
                                "",  // 16
                                "", // 17
                                "", // 18
                                "", // 19
                                "", // 20
                                "", // 21
                                "", // 22
                                "", // 23
                                "", // 24
                                "", // 25
                                $path // 26
                            );

                            fputcsv ($fp, $fields);
                        }
                    }
                } 

            }
        }

        fclose($fp);
        $this->info('Product export has run successfully');
    }
}
