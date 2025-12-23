<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Product;
use Auth;
use Session;
use DB;

class CustomersController extends Controller
{

    public function __construct() { 
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'store', 'edit', 'delete']]);
    }
    
    public function lvcustomers() {
        return view('admin.lvcustomers');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('admin.customers', ['pagename' => 'Customers','includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function combineCustomers(Request $request) {
        if ($request->ajax()) {
            $newId = $request['customerid'];
            $currentId = $request['currentId'];
            DB::update("update customer_order set customer_id = $newId where customer_id = $currentId");
            
        }
    }

    public function ajaxgetCustomer(Request $request) {
        if ($request->ajax()) {
            $id = $request['_id'];
            
            $customers = Customer::find($id);

            return response()->json($customers);
        }
    }
    
    public function ajaxCustomers(Request $request) {
        if ($request->ajax()) {
            if (!empty($request['sortBy'])) {
                $customers = Customer::select('id','company')->orderBy($request['sortBy'])->get();
                //return $customers;
            } else 
                $customers = Customer::latest('id','company')->get();

            $data=array();
            foreach ($customers as $customer) {
                $data[] = array('',
                        $customer->id,
                        $customer->company
                );
            }

            return response()->json(array('data'=>$data));
        }
    }

    public function ajaxCustomer(Request $request) {
        if ($request->ajax()) {
            $searchParam = str_replace(["'","&"],'',$request['query']);
            $addParam = '';

            if (isset($request['addParam']))
                $addParam = $request['addParam'];

            // $customers = DB::table('customers')
            //     ->whereRaw("company LIKE '%$key%' or lastname LIKE '%$key%' or firstname LIKE '%$key%'")
            //     ->get();

            $words = explode(' ', $searchParam);
            $searchTerm = "";
            $searchWords = "";
        
            $columns = ['id','company','lastname','firstname'];
        
            if ($searchParam) {
                $searchWords = "(";
                foreach($words as $word) {
                    foreach ($columns as $key => $column) {
                        $searchWords .= $column.' LIKE "%'.$word .'%" OR ';
                    }
                    
                    $searchWords = substr($searchWords,0,-4) . ") AND (";
                    $searchTerm .= $searchWords;
                    $searchWords = "";    
                }   
            }
        
            $searchTerm = substr($searchTerm,0,-6);
            
            $customers = Customer::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                $query->whereRaw($searchTerm);
            })->get();

            $data = array();
            $data['query'] = $searchParam;
            $data['suggestions'] = array();
            
            foreach ($customers as $customer) {
                $lastname='';$firstname='';

                if ($customer->lastname)
                    $lastname = $customer->lastname . " "; 
                if ($customer->firstname)
                    $firstname = $customer->firstname . " ";

                if (!$addParam)
                    $data['suggestions'][] = array('value'=>$lastname . $firstname.$customer->company,'data' => $customer->id);
                else 
                    $data['suggestions'][] = array('value'=>$customer->company,'data' => $customer->id);
            }

            return response()->json($data);
        }
    }
       
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$countries = Country::All();
        return view('admin.customers.create',['pagename' => 'New Customer']);
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
            'company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput($request->all())
                ->withErrors($validator);
        }

        if ($request['b_country']!='231')
            $request['b_state'] = '';
            
        Customer::create([
            'cgroup' => $request['customer-group'],
            'firstname' => allFirstWordsToUpper($request['firstname']),
            'lastname' => allFirstWordsToUpper($request['lastname']),
            'company' => allFirstWordsToUpper($request['company']),
            'address1' => allFirstWordsToUpper($request['address']),
            'address2' => $request['address2'],
            'phone' => localize_us_number($request['phone']),
            'country' => $request['b_country'],
            'state' => $request['b_state'],
            'city' => allFirstWordsToUpper($request['city']),
            'zip' => $request['zipcode'],
            'email' => $request['email'],
            'markup' => $request['markup']
        ]);

        return redirect('admin/customers');
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
        $customer = Customer::find($id);
        if (!$customer)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        $products = Product::where('supplier',$customer->company)->get();
            
        return view('admin.customers.edit',['pagename' => 'Edit Customer', 'customer' => $customer,'products'=>$products]);
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
        $validator = \Validator::make($request->all(), [
            'company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput($request->all())
                ->withErrors($validator);
        }
        
        $this::updateCustomer($request->all(), $id);
        return redirect('admin/customers');
    }

    public static function updateCustomer($request, $id) {
        $params=[
            'cgroup' => $request['customer-group'],
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'company' => $request['company'],
            'address1' => $request['address'],
            'address2' => allFirstWordsToUpper($request['address2']),
            'phone' => localize_us_number($request['phone']),
            'country' => $request['b_country'],
            'state' => $request['b_state'],
            'city' => allFirstWordsToUpper($request['city']),
            'zip' => $request['zipcode'],
            'email' => $request['email'],
            'markup' => $request['markup']
        ];

        if (!empty($request['logo']))
            $params['logo'] = $request['logo'];

        $id = Customer::where('id',$id)->update($params);

        return $id;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::where('id',$id);
        $customer->delete();

        Session::flash('message','Successfully deleted product!');
        return redirect('admin/customers');

    }
}
