<?php

namespace App\Http\Controllers;

use App\Models\Testimony;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Order;

class TestimonyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
     
        if (!$request['code'])
            return view('testimonies',['thankyou'=>0, 'error' => 2]);

        $testimony=Testimony::where('code',$request['code'])->first();
        
        if (!$testimony) {
            $order=Order::with('products')->where('code',$request['code'])->first();
            return view('testimonies',['order'=>$order,'thankyou'=>0,'error' => 0]);
        } else
            return view('testimonies',['thankyou'=>0,'error' => 1]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $order=Order::with('products')->where('code',$request['code'])->first();
        if ($order) {
            Testimony::create([
                'fullname' => $request['fullname'],
                'order_id' => $order->id,
                'title' => $request['title'],
                'feedback' => $request['feedback'],
                'code' => $request['code']
            ]);
            
            $order->update(['code'=>null]);
            return view('testimonies',['thankyou'=>1,'error'=>0]);
        }
        return view('testimonies',['thankyou'=>0,'error'=>1]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Testimony  $testimony
     * @return \Illuminate\Http\Response
     */
    public function show(Testimony $testimony)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Testimony  $testimony
     * @return \Illuminate\Http\Response
     */
    public function edit(Testimony $testimony)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Testimony  $testimony
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Testimony $testimony)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Testimony  $testimony
     * @return \Illuminate\Http\Response
     */
    public function destroy(Testimony $testimony)
    {
        //
    }
}
