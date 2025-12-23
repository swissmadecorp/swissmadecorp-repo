<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Product;
use App\Models\Margin;
use Carbon\Carbon;

class MarginsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = DB::table('products')->selectRaw('products.id,category_name,p_reference,p_model,title')
            ->join('categories','category_id','=','categories.id')
            ->whereNotExists( function ($query) {
                $query->select(DB::raw(1))
                    ->from('margins')
                    ->whereRaw('margins.product_id = products.id');
            })
            ->where('p_qty','>',0)
            ->orderBy('category_name','asc')
            ->get();
            
        $margins = DB::table('margins')->selectRaw('product_id,category_name,p_reference,p_model,amount,margin,title')
            ->join('products','product_id','=','products.id')
            ->join('categories','category_id','=','categories.id')
            ->orderBy('product_id','asc')
            ->get();
            
        return view('admin.margins',['pagename'=>'Margins','products'=>$products,'margins'=>$margins]);
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
    public function ajaxStore(Request $request)
    {
        if ($request->ajax()) {
            $data = array();

            parse_str($request['_form'],$output);
            
            $amount=$output['margin-amount'];
            foreach ($request['_options'] as $option) {
                $data[] = array(
                    'product_id'=>$option, 
                    'amount'=>$amount,
                    'margin'=>$output['marginamount'],
                    'created_at'=>Carbon::now('America/New_York'),
                    'updated_at'=>Carbon::now('America/New_York'),
                );
            }

            Margin::insert($data);
            $product = Product::find($option);

            if ($output['marginamount']=='Percent') {
                $product->update(['p_newprice' => $product->p_retail-($product->p_retail*($amount/100))]);
            } else
                $product->update(['p_newprice' => $amount]);
                
            return response()->json('success');
        }
    }

    public function ajaxDelete(Request $request){
        if ($request->ajax()) {
            $data = array();

            parse_str($request['_form'],$output);
            
            foreach ($request['_options'] as $option) {
                $data[] = $option;
                $product = Product::find($option)->update(['p_newprice'=>NULL]);
            }
            
            DB::table('margins')->whereIn('product_id',$data)->delete();
            return response()->json('success');
        }
    }

    public function ajaxUpdate(Request $request)
    {
        if ($request->ajax()) {
            $data = array();

            parse_str($request['_form'],$output);
            
            foreach ($request['_options'] as $option) {
                $amount=$output['margin-amount'];
                $data = array(
                    'amount'=>$amount,
                    'margin'=>$output['marginamount'],
                    'updated_at'=>Carbon::now('America/New_York'),
                );

                Margin::where('product_id','=',$option)->update($data);
                
                $product = Product::find($option);

                if ($output['marginamount']=='Percent') {
                    $product->update(['p_newprice' => $product->p_retail-($product->p_retail*($amount/100))]);
                } else
                    $product->update(['p_newprice' => $amount]);
             }
            
            return response()->json('success');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
