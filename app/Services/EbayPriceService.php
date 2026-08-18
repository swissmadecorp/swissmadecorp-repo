<?php

namespace App\Services;

use App\Models\Product;

class EbayPriceService
{
    public function listingPrice(Product $product): string
    {
        $productPrice = (float) $product->p_newprice;

        if ($productPrice < 1000) {
            $price = round($productPrice + ($productPrice * 0.15) + 500);
        } elseif ($productPrice > 1000 && $productPrice < 7500) {
            $price = round($productPrice + ($productPrice * 0.065) + 400);
        } else {
            $price = round($productPrice + ($productPrice * 0.03) + 300);
        }

        return number_format($price, 2, '.', '');
    }

    public function minimumBestOfferPrice(Product $product, string $listingPrice): string
    {
        $reduction = optional($product->categories)->category_name === 'Rolex' ? 200 : 500;

        return number_format((float) $listingPrice - $reduction, 2, '.', '');
    }
}
