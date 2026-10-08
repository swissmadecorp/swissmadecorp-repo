<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Mail\GMailer; 
use App\Http\Controllers\Controller;
use App\Models\Product;

class PriceOfferController extends Controller
{
    public function emailPriceOffer(Request $request) {
        $input = $request->validate([
            'id' => 'required|integer|exists:products,id',
            'fullname' => 'required|string',
            'priceOffer' => 'required|numeric|gt:0',
            'email' => 'required|email',
            'phone' => 'required|string',
            'comments' => 'nullable|string',
        ]);
        $product = Product::findOrFail($input['id']);
        if ($product->p_qty <= 0 || in_array((int) $product->p_status, [7, 8], true)) {
            return response()->json(['success' => false, 'message' => 'This item is no longer available.'], 422);
        }
        $email = strip_tags($input['email']);
        $image = $product->images()->first();

        $data = array(
            'to' => 'info@swissmadecorp.com',
            'replyTo' => $email,
            'template' => 'emails.priceoffer',
            'item' => $product->title.' ('.$product->id.')',
            'fullname' => allFirstWordsToUpper(strip_tags($input['fullname'])),
            'image' => $image?->location ?? 0,
            'price' => number_format($input['priceOffer'],2),
            'email' => $email,
            'phone' => strip_tags($input['phone']),
            'comments' => $input['comments'] ?? '',
            'ip' => $request->ip(),
            'subject' => 'Regarding a price offer',
        );

        $gmail = app()->makeWith(GMailer::class, ['event' => $data]);
        $gmail->send();
        return response()->json(['success' => true, 'message' => 'Your offer has been submitted to Swiss Made Corp.']);
    }

}
