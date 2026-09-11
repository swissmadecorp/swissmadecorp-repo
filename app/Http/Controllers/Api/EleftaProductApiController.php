<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class EleftaProductApiController extends ProductApiController
{
    public function index(Request $request)
    {
        $products = Product::select(
            'products.id', 'title', 'movement', 'p_casesize', 'p_model',
            'p_reference', 'p_box', 'p_papers', 'p_material', 'p_condition',
            'p_retail', 'dealer_price', 'web_price', 'p_status', 'p_gender',
            'p_strap', 'slug', 'category_name', 'products.created_at'
        )
            ->with('images')
            ->join('categories', 'category_id', '=', 'categories.id')
            ->where('p_qty', '>', 0)
            ->where('dealer_price', '>', 0)
            ->whereIn('p_status', [0, 1, 5])
            ->orderBy('products.created_at', 'desc')
            ->get();

        $items = [];

        foreach ($products as $product) {
            $images = [];

            foreach ($product->images as $image) {
                $images[] = ['https://swissmadecorp.com/images/thumbs/'.$image->location];
            }

            if (count($images) === 0) {
                $images[] = ['https://swissmadecorp.com/images/no-image.jpg'];
            }

            $items[] = [
                'id' => (string) $product->id,
                'title' => $product->title,
                'model' => $product->p_model,
                'category' => $product->category_name,
                'reference' => $product->p_reference,
                'box' => $product->p_box == 0 ? 'No' : 'Yes',
                'papers' => $product->p_papers == 0 ? 'No' : 'Yes',
                'material' => Materials()->get($product->p_material),
                'condition' => Conditions()->get($product->p_condition),
                'retail' => $product->p_retail,
                'price' => $product->dealer_price,
                'webprice' => $product->web_price,
                'movement' => Movement()->get($product->movement),
                'case_size' => $product->p_casesize,
                'status' => Status()->get($product->p_status),
                'year' => $product->p_year,
                'gender' => $product->p_gender,
                'strap' => Strap()->get($product->p_strap),
                'slug' => $product->slug,
                'images' => $images,
            ];
        }

        return $this->sendResponse($items, $this->dealerCategories(), 'Retrieved successfully.', $request);
    }

    private function dealerCategories(): array
    {
        $categories = Category::whereHas('products', function ($query) {
            $query->where('p_qty', '>', 0)
                ->where('dealer_price', '>', 0)
                ->whereIn('p_status', [0, 1, 2, 5]);
        })->orderBy('category_name')->get();

        $brands = [['All categories']];

        foreach ($categories as $category) {
            $brands[] = [$category->category_name];
        }

        return $brands;
    }
}
