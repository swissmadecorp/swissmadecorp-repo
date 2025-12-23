<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\GMailer; 
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class PriceOfferController extends Controller
{
    public function emailPriceOffer(Request $request) {

        $product = Product::find($request['id']);

        $data = array(
            'template' => 'emails.priceoffer',
            'item' => $product->title.' ('.$product->id.')',
            'fullname' => $request['fullname'],
            'price' => number_format($request['priceOffer'],2),
            'email' => $request['email'],
            'phone' => $request['phone'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'subject' => "Regarding offer for ".$product->title,
        );

        $gmail = new GMailer($data);
        $gmail->send();
    }

}