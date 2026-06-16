<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Models\DiscountRule;
use App\Models\Product;
use App\Models\Category;

class DiscountRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.discountrules');
    }


    public function getAllDiscountRules(Request $request) {
        $rules = DiscountRule::all();
        $data = array();

        foreach ($rules as $rule) {
            $data[] = array(
                '',
                $rule->id,
                $rule->rule_name,
                $action = DiscountRules()->get($rule->action),
                $rule->is_active == 0 ? 'No' : "Yes",
                $rule->free_shipping == 0 ? 'No' : "Yes",
                $rule->discount_code,
                $rule->created_at->format('m-d-y'),
            );
        }


        return response()->json(array('data'=>$data));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('discountrules.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'rule_name' => 'required',
        ]);

        if (!$request['start_date'])
            $request['start_date'] = now();

        if (!$request['end_date'])
            $request['end_date'] = now();

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        if (!$request['amount']) $request['amount'] = 0;
        DiscountRule::create($request->all());
        if ($request['action'] == 5) {
            $products = Category::query()->update([
                'is_on_sale' => 1
            ]);
        } else {
            $products = Product::whereIn('id',$request['product'])->update([
                'is_on_sale' => 1
            ]);
        }
        

        //Product::whereIn('id',$request['product'])->searchable();

        Session::flash('message', "Successfully created a new discount rule.");
        return redirect("admin/discountrules");
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
        return redirect()->route('discountrules.index');
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

        // dd($request->all());
        if (!$request['start_date'])
            $request['start_date'] = now();

        if (!$request['end_date'])
            $request['end_date'] = now();

        if (!$request['product']) $request['product'] = null;
        
        $discountRule = DiscountRule::findOrFail($id);
        $discountRule->update($request->all());

        // $products = Product::whereIn('id',$request['product'])->update([
        //     'is_on_sale' => 1
        // ]);


        //Product::whereIn('id',$request['product'])->searchable();

        return redirect("admin/discountrules");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $discountRule = DiscountRule::findOrFail($id);
        $discountRule->delete();

        Session::flash('message', "Successfully deleted invoice!");
        //return back();
    }
}
