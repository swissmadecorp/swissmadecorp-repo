<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class RssFeedController extends Controller
{
    public function feed()
    {
        $products=Product::where('p_price3P','<>',0)
            ->where('group_id',0)
            ->where('p_qty', '>', 0)
            ->whereIn('p_condition',[1,2,3])
            ->where('p_newprice','>', '10')
            ->get();

        return response()->view('rss.feed', compact('products'))->header('Content-Type', 'application/xml');

    }
}
