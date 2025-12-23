<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use DB;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::all('id','company');
        return view('admin.suppliers',['pagename'=>'Suppliers','suppliers'=>$suppliers]);
    }

    public function ajaxgetSupplier(Request $request) {
        if ($request->ajax()) {
            $id = $request['_id'];
            
            $suppliers = Supplier::find($id);

            return response()->json($suppliers);
        }
    }

    public function ajaxSupplier(Request $request) {
        if ($request->ajax()) {

            $n_criteria = strlen($request['_criteria']);
            $criteria = $request['_criteria'];
            $searchBy = $request['_searchBy'];

            $suppliers = DB::table('suppliers')
                //->whereRaw("left($searchBy,$n_criteria)='$criteria'")
                ->whereRaw("$searchBy LIKE '%$criteria%'")
                ->get();

            
            $data='';
            foreach ($suppliers as $supplier) {
                
                $data .= '<div class="customer-item" data-id="'.$supplier->id. '">'.$supplier->lastname . "  " . $supplier->firstname . " " . $supplier->company.'</div>';
            }

            return response()->json(array('content'=>$data,'rows'=>count($suppliers)));
        }
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.suppliers.create',['pagename'=>'New Supplier']);
    }

    public function _validate($request) {
        
        $validator = \Validator::make($request->all(), [
            // 'firstname' => "required",
            // 'lastname' => "required",
            // 'phone' => "required",
            'company'=>'required',
            //'email' => "required|email|unique:suppliers",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->_validate($request);
        Supplier::create([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'company' => $request['company'],
            'address1' => $request['address'],
            'address2' => $request['address2'],
            'phone' => $request['phone'],
            'country' => $request['b-country'],
            'state' => $request['b-state'],
            'city' => $request['city'],
            'zip' => $request['zipcode'],
            'contact' => $request['contact'],
            'email' => $request['email']
        ]);

        return redirect('admin/suppliers');
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
        $supplier = Supplier::find($id);
        return view('admin.suppliers.edit',['pagename'=>'Edit Supplier','supplier'=>$supplier]);
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
        $this->_validate($request);
        Supplier::find($id)->update([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'company' => $request['company'],
            'address1' => $request['address'],
            'address2' => $request['address2'],
            'phone' => $request['phone'],
            'country' => $request['b-country'],
            'state' => $request['b-state'],
            'city' => $request['city'],
            'zip' => $request['zipcode'],
            'contact' => $request['contact'],
            'email' => $request['email']
        ]);

        return redirect('admin/suppliers');
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
