<?php 

namespace App\Libs;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class MassMail
{
    public static function process($request) {
        $template = '';

        $d = strtotime("today");
        $start_week = strtotime("-3 weeks midnight",$d);
        $start = date("Y-m-d", $start_week);
        $end = date("Y-m-d", $d);
        
        if ($request['loadWithTemplate']) {
            $filename = base_path().'/public/template/mass-mail-tinymce.html';
            $file = fopen($filename,'r') or die("Unable to open file!");
            $template = fread($file,filesize($filename));
            
            fclose($file);
        }

        if ($request['category']) {
            $products = Product::whereHas('categories', function($query) use($request) {
                $query->whereIn('id',$request['category']);
            })->where('created_at','>=',$start)
                ->where('p_qty','>',0)
                ->get();
        } else {
            $products = Product::where('created_at','>=',$start)
                ->where('p_qty','>',0)
                ->get();
        }

        $totalRecords = $products->count();
            
        if ($totalRecords>5) {
            $count = ceil($totalRecords / 5);
            $records = 5;
        } else {
            $records = $totalRecords;
            $count = 1;
        }
        
        $exp = -1;
        ob_start(); ?>
            <table style="border-spacing: 10px">
                
        <?php
        for ($i=0; $i < $count; $i++) {
            ?>
                <tr>
            <?php for ($j=0; $j < $records; $j++) { 
                $exp ++;
                ?>

                <?php
                    if ($exp == $totalRecords) break;
                    $product = $products[$exp];
                    $path = 'product-details/'.$product->slug ?>
        
                <td style="border: 1px solid #d4d4d4;width: 180px" valign="top">
                <?php if (count($product->images)) { ?>
                    <?php $image = $product->images->first() ?>
                    <?php if (!file_exists(base_path(). '/public/images/thumbs/' . $image->location)) { ?>
                        <a href="https://swissmadecorp.com/<?= $path ?>"><img style="height: 180px" src="https://swissmadecorp.com/images/no-image.jpg" alt=""></a>
                    <?php } else { ?>
                        <a href="https://swissmadecorp.com/<?= $path ?>"><img style="height: 180px" title="<?= $product->title ?>" alt="<?= $product->title ?>" src="<?= 'https://swissmadecorp.com/images/thumbs/' . $image->location ?>" alt=""></a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="https://swissmadecorp.com/<?=$path?>"><img style="height: 180px" src="https://swissmadecorp.com/images/no-image.jpg" alt=""></a>
                <?php } ?>
                
                <span style="display:block">Status: <span class="sticker new" style="color:green">Available</span></span>
                <hr>
                <div style="font-weight: 600;padding: 5px 1px;white-space: initial;text-overflow: ellipsis;overflow: hidden;line-height: 15px;">
                    <?php if (isset($product->categories->category_name)) { ?>
                        <a href="https://swissmadecorp.com/<?=$path?>"><?=$product->categories->category_name . ' ' . $product->p_model . ' ' . $product->p_reference?></a>
                    <?php } else { ?>
                        <a href="https://swissmadecorp.com/<?=$path?>"><?=$product->p_model . ' ' . $product->p_reference?></a>
                    <?php } ?>
                </div>
                <div class="container item-info">
                    <ul style="list-style: none; padding: 0; margin: 0">
                        <li style="list-style: none; padding: 0; margin: 0">
                            <div class="attribs">
                                <label for="" class="second_font m_right_17 m_top_2 d_inline_b">Our Price:</label>
                                
                                <?php if ($product->p_newprice>0) {?>
                                    <?php $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin())) ?>
                                    <span class="price">$<?= number_format($webprice,2) ?></span>
                                <?php } else { ?>
                                    <span class="price" style="color:red">Call Us</span>
                                <?php } ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </td>
        <?php
            }
            ?>
            </tr> <?php
        }
        ?>
        
        </table>

        <?php

        $var = ob_get_clean();

        if ($template) {
            $f= strpos($template, 'products');
            $var = substr($template,0,$f-9) . '<div id="products" style="-webkit-box-pack: center!important;-ms-flex-pack: center!important;justify-content: center!important;">'.$var.'</div></div>';
        }
        
        return $var;
    }
    
}