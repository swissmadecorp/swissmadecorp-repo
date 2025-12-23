<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class CustomerOrderController extends Controller
{
    public function find(Request $request) {
        $input = $request->only('zip','id');

        $message = array('zip.required' => 'Zip code is a required field',
        'id.required' => 'Invoice number is a required field');

        $validator = \Validator::make($input, [
            'zip' => "required",
            'id' => "required",
        ],$message);

        if ($validator->fails()) {
            return back()
                ->withInputs($input)
                ->withErrors($validator);
        }

            //3148
        return redirect("account/order/?id=" . strip_tags($input['id']). '&zip='.strip_tags($input['zip']));
    }

    public function AccountOrder(Request $request) {

        $order = Order::where('id',strip_tags($request['id']))
            ->where('s_zip',strip_tags($request['zip']))
            ->first();

        if ($order) {
            return view('admin.account.order',['order'=>$order]);
        } else return back()->withErrors('No Order found with the criteria you\'ve entered');

        
    }

}
