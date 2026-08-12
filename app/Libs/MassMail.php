<?php

namespace App\Libs;

use App\Models\Product;

class MassMail
{
    public static function process($request)
    {
        $request = array_merge([
            'category' => [],
            'loadWithTemplate' => false,
            'template' => '',
        ], is_array($request) ? $request : $request->all());

        $template = (string) ($request['template'] ?? '');

        $d = strtotime('today');
        $start_week = strtotime('-3 weeks midnight', $d);
        $start = date('Y-m-d', $start_week);
        $end = date('Y-m-d', $d);

        if ($request['loadWithTemplate'] && $template === '') {
            $filename = base_path().'/public/template/mass-mail-tinymce.html';
            $template = is_file($filename) ? (string) file_get_contents($filename) : '';
        }

        $categoryIds = collect($request['category'] ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($categoryIds) {
            $products = Product::with(['categories', 'images'])
                ->whereIn('category_id', $categoryIds)
                ->where('created_at', '>=', $start)
                ->where('p_qty', '>', 0)
                ->get();
        } else {
            $products = Product::with(['categories', 'images'])->where('created_at', '>=', $start)
                ->where('p_qty', '>', 0)
                ->get();
        }

        $totalRecords = $products->count();

        if ($totalRecords > 5) {
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
        for ($i = 0; $i < $count; $i++) {
            ?>
                <tr>
            <?php for ($j = 0; $j < $records; $j++) {
                $exp++;
                ?>

                <?php
                    if ($exp == $totalRecords) {
                        break;
                    }
                $product = $products[$exp];
                $path = 'product-details/'.$product->slug ?>
        
                <td style="border: 1px solid #d4d4d4;width: 180px" valign="top">
                <?php if (count($product->images)) { ?>
                    <?php $image = $product->images->first() ?>
                    <?php if (! file_exists(base_path().'/public/images/thumbs/'.$image->location)) { ?>
                        <a href="https://swissmadecorp.com/<?= $path ?>"><img style="height: 180px" src="https://swissmadecorp.com/images/no-image.jpg" alt=""></a>
                    <?php } else { ?>
                        <a href="https://swissmadecorp.com/<?= $path ?>"><img style="height: 180px" title="<?= $product->title ?>" alt="<?= $product->title ?>" src="<?= 'https://swissmadecorp.com/images/thumbs/'.$image->location ?>" alt=""></a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="https://swissmadecorp.com/<?= $path?>"><img style="height: 180px" src="https://swissmadecorp.com/images/no-image.jpg" alt=""></a>
                <?php } ?>
                
                <span style="display:block">Status: <span class="sticker new" style="color:green">Available</span></span>
                <hr>
                <div style="font-weight: 600;padding: 5px 1px;white-space: initial;text-overflow: ellipsis;overflow: hidden;line-height: 15px;">
                    <?php if (isset($product->categories->category_name)) { ?>
                        <a href="https://swissmadecorp.com/<?= $path?>"><?= $product->categories->category_name.' '.$product->p_model.' '.$product->p_reference?></a>
                    <?php } else { ?>
                        <a href="https://swissmadecorp.com/<?= $path?>"><?= $product->p_model.' '.$product->p_reference?></a>
                    <?php } ?>
                </div>
                <div class="container item-info">
                    <ul style="list-style: none; padding: 0; margin: 0">
                        <li style="list-style: none; padding: 0; margin: 0">
                            <div class="attribs">
                                <label for="" class="second_font m_right_17 m_top_2 d_inline_b">Our Price:</label>
                                
                                <?php if ($product->p_newprice > 0) {?>
                                    <?php $webprice = ceil($product->p_newprice + ($product->p_newprice * CCMargin())) ?>
                                    <span class="price">$<?= number_format($webprice, 2) ?></span>
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

        return $template ? self::replaceProducts($template, $var) : $var;
    }

    private static function replaceProducts(string $template, string $products): string
    {
        if (! preg_match('/<div\b[^>]*\bid=["\']products["\'][^>]*>/i', $template, $opening, PREG_OFFSET_CAPTURE)) {
            return $template.'<div id="products">'.$products.'</div>';
        }

        $contentStart = $opening[0][1] + strlen($opening[0][0]);
        $depth = 1;
        $offset = $contentStart;

        while (preg_match('/<\/?div\b[^>]*>/i', $template, $tag, PREG_OFFSET_CAPTURE, $offset)) {
            $value = $tag[0][0];
            $position = $tag[0][1];

            if (str_starts_with(strtolower($value), '</div')) {
                $depth--;

                if ($depth === 0) {
                    return substr($template, 0, $contentStart)
                        .$products
                        .substr($template, $position);
                }
            } else {
                $depth++;
            }

            $offset = $position + strlen($value);
        }

        return $template;
    }
}
