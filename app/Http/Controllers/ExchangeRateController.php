<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    //
    public function index() {
        $rates = ExchangeRate::all();
        return view('admin.rates',['pagename'=>'Exchange Rates','rates'=>$rates]);
    }

    public function create() {
        return view('admin.rates.create',['pagename'=>'New Rate']);
    }
    
    public function store(Request $request)
    {

        $validator = \Validator::make($request->all(),[
            'currency_name' => 'required',
            'rate' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

       ExchangeRate::create($request->all());
       return redirect("admin/rates");
    }

    public function switchRates(Request $request) {
        if ($request->ajax()) {
            if ($request['code']=='' || $request['code'] == 'USD') {
                session()->forget('exchange_rate');
                $currencyName="USD";
            } else {
                $rateDB = ExchangeRate::where('currency_name',$request['code'])->first();

                $currencyName = $rateDB->currency_name;
                $rate = [
                    'currency_name'=>$currencyName,
                    'rate'=>$rateDB->rate, 
                    'symbol' => $rateDB->symbol, 
                    'description' => $rateDB->description,
                    'image_name' => $rateDB->image_name
                ];
            
                session()->put('exchange_rate', $rate);
            }

            $rateDB = ExchangeRate::where('currency_name',"<>",$request['code'])->get();
            $combine = "";
            foreach ($rateDB as $rate) {
                if ($rate->currency_name != $currencyName) {
                    $combine .="<a href='#'><img src='/assets/$rate->image_name.png' data-id='$rate->currency_name' alt='$rate->description'></a>";
                }
            }

            if ($currencyName != 'USD') {
                $combine .="<a href='#'><img src='/assets/us.png' data-id='' alt='USA currency'></a>";
            }
            
            return $combine;
        }
    }
}
