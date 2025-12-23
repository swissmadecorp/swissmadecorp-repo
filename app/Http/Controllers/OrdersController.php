<?php

namespace App\Http\Controllers;

use PDF;
use Input;
use Session;
use App\Models\Order;
use App\Models\Repair;
use App\Models\Payment;
use App\Models\TheShow;
use App\Models\Taxable;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
use App\Models\Newsletter;
use App\Models\AmazonListings;
use App\Mail\GMailer;
use Illuminate\Http\Request;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Jobs\AmazonSubmitProductQueue;
use App\Jobs\eBayEndItem;
use Illuminate\Support\Facades\Cache;
use App\Jobs\RevokeWalmartProduct;
use App\Libs\FedexCommon;

class OrdersController extends Controller
{
    protected $displayPDF = true;

    public function __construct() {
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'store', 'edit', 'delete']]);
        // Alternativly
        //$this->middleware('role:viewer', ['except' => ['index', 'show']]);
    }

    private function getPinterestToken($code) {
        $clientId     = '1480606';
        $clientSecret = 'a68a2b9ff96f97d7723b3ff5a374ac5bbc88127d';
        $authCode     = $code;

        $authKey = base64_encode($clientId . ':' . $clientSecret);

        $headers = array(
            'Authorization: Basic ' . $authKey,
            'Content-Type: application/x-www-form-urlencoded'
        );

        $post = [
            "code" => $authCode,
            "redirect_uri"=> "https://swissmadecorp.com/admin/orders/pinterest",
            "grant_type" => "authorization_code"
        ];

        $uri = "https://api.pinterest.com/v3/oauth/access_token/"; //'https://api.pinterest.com/v3/oauth/token?grant_type=authorization_code&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&code=' . $authCode;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uri);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLINFO_HEADER_OUT,true);

        $response = curl_exec( $ch );
        if( curl_errno($ch) ){
            echo 'Curl error: ' . curl_error($ch);
            
        }

        $js = json_decode($response,true);
        
        curl_close($ch);

        if ( $js['status'] == "success" ) {
            return $js['access_token'];
        }

        else return $js['status'];
    }

    private function getFromPinterest($token) {
        $headers = array(
            'Authorization: Bearer ' . $token,
        );

        $uri = "https://api.pinterest.com/v5/boards"; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT,90);

        $response = curl_exec( $ch );
        if( curl_errno($ch) ){
            echo 'Curl error: ' . curl_error($ch);
            
        }

        curl_close($ch);
        $js = json_decode($response,true);
        dd($js);
    }

    private function createPin($token) {
        $headers = array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        );

        $img = file_get_contents(
            'https://swissmadecorp.com/public/images/chopard-happy-sport-26mm-278250-23-stainless-steel-womens-watch-96443.jpg');
              
            // Encode the image string data into base64
        $data = base64_encode($img);
              
        $post = [
            "link" => "https://swissmadecorp.com/chopard-happy-sport-27-8250-23-white-kzsz-92625",
            "title"=> "Chopard Happy Sport 26mm 27/8250-23 Stainless Steel Women's Watch",
            "description" => "Chopard Happy Sport 26mm 27/8250-23 Stainless Steel Women's Watch",
            "board_id" => "584905139044185995",
            "media_source" => array(
                "source_type" => "image_base64",
                "content_type"  => "image/jpeg",
                "data" => $data
            )
        ];

        $uri = "https://api.pinterest.com/v5/pins"; //'https://api.pinterest.com/v3/oauth/token?grant_type=authorization_code&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&code=' . $authCode;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uri);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLINFO_HEADER_OUT,true);

        $response = curl_exec( $ch );
        if( curl_errno($ch) ){
            echo 'Curl error: ' . curl_error($ch);
            
        }
dd($response);
        $js = json_decode($response,true);
        
        curl_close($ch);

        if ( $js['status'] == "success" ) {
            return $js['access_token'];
        }

        dd($s);
    }

    public function pinterest(Request $request) {
       
        if(isset($request['code'])){
            //$pinterest = new Pinterest('1480606', 'a68a2b9ff96f97d7723b3ff5a374ac5bbc88127d');
            //$token = $pinterest->auth->getOAuthToken($request['code']);
            //dd($token);
            //$pinterest->auth->setOAuthToken("pina_AEAZ5FYWAB45OAQAGAAB4CFMLW6YTAYBACGSPL2H7MMEJMV74NRGYDMD3CKE3422G4KDNGQEKCJ6CS2NFDVY3X6Z7V3FHKQA"); //$token->access_token);
            // $token = $this->getPinterestToken($request['code']);
            // dd($token);
            $this->createPin("pina_AEAZ5FYWAB45OAQAGAAB4CBIBX4I5AYBACGSPYXU42GYU2KU3EUUKJD52NE5NKD5UW5BHJ6TIANWWFOAYIEP2TWVVWQNNIQA");

            // $pinterest->pins->create(array(
            //     "description"    => "Hublot Big Bang 41mm 341.PB.131.RX 18K Rose Gold Unisex Watch",
            //     "title"          => "Hublot Big Bang 41mm 341.PB.131.RX 18K Rose Gold Unisex Watch",
            //     "image_url"      => "https://swissmadecorp.com/public/images/hublot-big-bang-341pb131rx-99922.jpg",
            //     "board_id"       => "Watches"
            // ));

            //dd($pinterest->users->me());
        }
        
        //$pinterest = new Pinterest('1480606', 'a68a2b9ff96f97d7723b3ff5a374ac5bbc88127d');
        //$loginurl = $pinterest->auth->getLoginUrl('https://swissmadecorp.com/admin/orders/pinterest', array('pins:read','pins:write'));
        
        https://www.pinterest.com/oauth/?client_id={app_id}&redirect_uri={redirect_uri}&response_type=code&state={optional}

        $loginurl = 'https://www.pinterest.com/oauth/?client_id=1480606&redirect_uri=https://swissmadecorp.com/admin/orders/pinterest&response_type=code&scope=boards:read,boards:write,pins:read,pins:write,user_accounts:read';
        $html = '<a href="'.$loginurl.'">url</a>';
        echo $html;
                
       // https://api.pinterest.com/oauth/?response_type=code&redirect_uri=https%3A%2F%2Fswissmadecorp.com%2Fadmin%2Forders%2Fpinterest&client_id=1480606&scope=pins%3Aread%2Cpins%3Awrite&state=8d3c2ae
        

        
        // die($loginurl);
    }

    // Get all orders and load them upon loading a product page through ajax
    public function ajaxOrderStatus(Request $request) {
        if ($request->ajax()) {
        $data=array();
        $action = $request["action"];
        
        if ($action == 'unpaid' || !$action)
            $orders = Order::with('payments')->where('status','=',0)->get();
        elseif ($action == 'paid') {
            $orders = Order::with('customers','payments')->where('status','=',1)->get();
        } elseif ($action == 'returned')
            $orders = Order::with('payments')->where('status','=',2)->get();
        elseif ($action == 'canceled') {
            //return response()->json(array('action'=>$action));
            $orders = Order::with('payments')->where('status','=',4)->get();
        }
        else $orders = Order::with('customers','payments')->get();

        foreach ($orders as $order) {
            $custId = $order->customers->first()->id;
            $link = "<a href='orders/$order->id' data-custid='$custId'>".$order->id."</a>";
            if ($order->code) {
                $status = $order->cc_status;
            } else  {
                $status = orderStatus()->get($order['status']);
            }

            $method = $order->method;$shipped='';
            if ($order['emailed']) 
                $method .=' <i class="far fa-envelope" title="Invoice was emailed"></i>';
            if ($order->tracking)
                $shipped = " <a class='shipping' href='https://www.fedex.com/apps/fedextrack/?tracknumbers=$order->tracking' target='_blank'><i class='fab fa-fedex fa-lg'></i></a>";
            $incomplete = '';
            $companyInfo = (!$order['b_firstname'] && !$order['b_lastname'] && $order['s_firstname'] && $order['s_lastname']) ? '<b>'.$order['b_company'] . '</b>-'.$order['s_firstname'] . ' ' .$order['s_lastname'] .'*': $order['b_company'];
            $po = $order->po;
            if ($po)
                $companyInfo .= ' ('. $po .') ';
            if ($order['payment_options'] == 'Incomplete')
                $incomplete = ' <b>(Incomplete)</b>';

            if ($action == 'paid') {
                $total = $order->payments->sum('amount');
            } else {
                $total = $order->total - $order->payments->sum('amount');
            }

            // if ($action=='unpaid' || !$action) {
                // foreach ($order['payments'] as $payment)
                //     $total -= $payment['amount'];
            // }
            
            $data[] = array('',
                    $link.$incomplete,
                    $method.$shipped,
                    $companyInfo,
                    $status,
                    $order->created_at->format('m-d-y'),
                    '$'. number_format($total,2),
                    $custId
            );
        }


        $displaystatus = "Displaying $action orders";
        return response()->json(array('data'=>$data,'action'=>$action,'displaystatus'=>$displaystatus));
        }
    }

    // Load USPS module to get the city and state based on the zip code
    public function addressFromZip(Request $request) {
        return addressFromZip($request['zip']);
    }

    // Create a new customer and if already exists, update it
    public function ajaxSaveCustomer(Request $request) {
        if ($request->ajax()) {
            parse_str($request['_form'],$output);
            //return response()->json($output);

            $data = array(
                'cgroup' => 0,
                'firstname' => allFirstWordsToUpper($output['b_firstname']),
                'lastname' => allFirstWordsToUpper($output['b_lastname']),
                'company' => $output['b_company'],
                'address1' => allFirstWordsToUpper($output['b_address1']),
                'address2' => $output['b_address2'],
                'phone' => localize_us_number($output['b_phone']),
                'country' => $output['b_country'],
                'state' => $output['b_state'],
                'city' => strtoupper(($output['b_city'])),
                'zip' => $output['b_zip']
            );

            $customer = Customer::updateOrCreate(['company'=>$output['b_company']],$data);
            //$customer->save();
            return response()->json($customer->id);
        }
    }

    public function lvinvoices() {
        return view('admin.lvinvoices'); //->compact('users');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // if (\Auth::user()->name == 'Edward B') {   
            
        // }

        // if (isset($request["action"])) {
        //     $action = $request["action"];
        //     if ($action == 'unpaid')
        //         $orders = Order::where('status','=',0)->get();
        //     elseif ($action == 'paid')
        //         $orders = Order::where('status','=',1)->get();
        //     elseif ($action == 'returns')
        //         $orders = Order::where('status','=',2)->get();
        //     elseif ($action == 'canceled')
        //         $orders = Order::where('status','=',4)->get();
        //     else $orders = Order::all();
            
        // } else $orders = Order::where('status','=',0)->get();
        
        return view('admin.orders',['pagename' => 'Invoices', 'includeDataTableCss'=>'1','includeDataTableJs'=>'1']);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (isset($_GET['id'])) {
            $products=Product::findMany($_GET['id']);
            return view('admin.orders.create',['pagename' => 'New Invoice','products'=>$products]);
        }
        
        return view('admin.orders.create',['pagename' => 'New Invoice']);
    }

    // Remove from Inventory Adjuster
    private function removeFromInventoryAdjuster($id) {
        $inventory = \DB::table('table_temp_a')->where('id',$id);

        if(count($inventory->get())){
            $product=Product::join('table_temp_a','table_temp_a.id','=','products.id')
            ->where('table_temp_a.id',$id)->first();

            $inventory->delete();
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
        //return redirect("admin/orders/?print=1928");
        $validator = \Validator::make($request->all(), [
            'b_company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInputs($request->all())
                ->withErrors($validator);
        }

        $orderArray = array_slice($request->all(),4);
        $created_at = $request['created_at'];
        $freight = $request['freight'];

        // $excludeArray = array('id','product_name','qty','price','serial','payment','created_at','newcost');
        // foreach ($orderArray as $key => $index) {
        //     if ( in_array($key,$excludeArray)) {
        //             unset($orderArray[$key]);
        //     }
        // }
        
        $orderArray['method'] = $request['payment'];
        $orderArray['status'] = 0;
        
        if ($created_at) {
            $orderArray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
            $orderArray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
        }
        
        $customer = Customer::find($request['customer_id']);
        
        if (!$customer) {
            
            $data = array(
                'cgroup' => $request['cgroup'],
                'firstname' => allFirstWordsToUpper($request['b_firstname']),
                'lastname' => allFirstWordsToUpper($request['b_lastname']),
                'company' => $request['b_company'],
                'address1' => allFirstWordsToUpper($request['b_address1']),
                'address2' => $request['b_address2'],
                'phone' => localize_us_number($request['b_phone']),
                'country' => $request['b_country'],
                'state' => $request['b_state'],
                'city' => strtoupper($request['b_city']),
                'zip' => $request['b_zip']
            );

            $customer = Customer::updateOrCreate(['company'=>$request['b_company']],$data);
        } 

        $keys = $request['id'];
        
        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);

        $subtotal = 0;
        $total = 0;
        $tax = 0;

        $product_ids=array();

        //\Log::debug('keys: '.print_r($keys,true));
        foreach ($keys as $index => $key) {
            if ($key) {
                $product_id = $key;
                $product_ids[]=$product_id;

                $qty = $request['qty'][$index];
                if (!$qty) $qty = 1;
                $price = $request['price'][$index];
                if (!$price) $price = 0;
                
                $serial = $request['serial'][$index];
                if (!$serial) $serial = 0;

                $product_name = $request['product_name'][$index];
                if (!$product_name)
                    $product_name = "Miscellaneous";
                
                $product = Product::where('id',$product_id)->first();
                
                if (isset($request['newcost'][$index]))
                    $cost = $request['newcost'][$index];
                else $cost = $product->p_price;
            
                $order->products()->attach($product->id, [
                    'qty' => $qty,
                    'price' => $price,
                    'serial' => $serial,
                    'product_name' => $product_name,
                    'cost' => $cost
                ]);

                if ($product_id != 1) {
                    $this->removeFromInventoryAdjuster($product_id);
                    $theshow=TheShow::where('product_id',$product_id);
                    if($theshow->get()){
                        $theshow->delete(); 
                    }
                }

                if ($product->category_id!=74) {
                    if ($request['payment'] == 'Invoice') {
                        if ($product->p_qty > 0) {
                            $product->p_qty = 0;
                            $product->p_status=8; // mark as sold
                        } else $product->decrement('p_qty');

                        $product->update();
                    } elseif ($request['payment'] == 'On Memo') {
                        $product->p_status=1;
                        $product->update();
                    }
                    //$this->findIdenticalItem($product->p_reference,$product->p_color,$product->p_condition,$product->p_strap);
                    
                } else {
                    $product->p_status=4;
                    $product->update();
                }
                $subtotal = $subtotal + ($price*$qty);

            }
        }
        
        if ($request['cgroup'] == 1) {
            $tax = Taxable::where('state_id',$order->s_state)->value('tax');
            $total = number_format($subtotal + ($subtotal * ($tax/100))+$freight,2, '.', '');
        } else {
            $tax = 0;
            $total = $subtotal+$freight;
        }
        
        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'taxable' => $tax,
            'freight' => $freight
        ]);
        
        eBayEndItem::dispatch($product_ids);
        
        //$walmart = new WalmartClass();
        // $walmart->retireItem($product_ids);
        //RevokeWalmartProduct::dispatch($product_ids)->onConnection('sqs');
        
        // Put all sold item skus on Amazon out of stock.
        //$this->setAmazonItemOutOfStock($product_ids);
        
        //$this->saveEmailForNewsLetter($request['email'],$request['b_company']);
        if ($product)
            $product->save();
        
        if ($request['printAfterSave'] == 1)
            return redirect("admin/orders/?print=$order->id");
        else
            return redirect("admin/lvinvoices");
    }

    protected function findIdenticalItem($ref,$color,$cond,$strap) {
        
        $product=Product::where('p_reference',$ref)
                       ->where('p_color',$color)
                       ->where('p_condition',$cond)
                       ->where('p_strap',$strap)
                       ->where('p_status',3)
                       ->where('p_qty',1);

        if ($product->count()>0) {
            $product->first()->update(['p_status'=>0]);
        }
    }

    public function printmulti($ids) {
        $ids=explode(',',$ids);
        $filename=array();

        $orders=Order::wherein('id',$ids)->get();
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        foreach ($orders as $order) {
            $ret = $printOrder->_print($order,null,'emailmultiple'); // Print newly created proforma/order.
            //$arr=$this->print($id,'emailmultiple');

            $order=$ret[1];
            $filename[] = $ret[0];
            
            if ($order->email=='') {
                Session::flash('message', "Email was not specified. Please enter email and  try again!");
                return redirect("admin/lvinvoices");    
            }

            $order->emailed=1;
            $order->update();    
        }
        
        $purchasedFrom = $order->purchased_from;
        if ($purchasedFrom==2) {
            $email = 'signtimeny@gmail.com';
            $subject = 'Signature Time';
        } else {
            $email = 'info@swissmadecorp.com';
            $subject = 'Swiss Made Corp.';
        }

        if ($order->b_company != "Website") {
            $company = $order->b_company;
        } else {
            $company = $order->s_company;
        }

        $data = array(
            'template' => 'emails.invoice',
            'to' =>$order->email,
            'company' => $company,
            'order_id' => $order->id,
            'filename'=>$filename,
            'purchasedFrom' => $ret[2],
            'subject' => $subject,
            'from' => $email,
        );

        $gmailer = new GMailer($data);
        $gmailer->send();

        //Mail::to($order->email)->queue(new EmailCustomer($data));
        Session::flash('message', "Successfully emailed invoice!");
        
        return redirect("admin/lvinvoices");
    }

    // Print order
    public function print($id,$output='') {

        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
        if ($output == 'appraisal') {
            $order=Order::find($id);
            $ret = $printOrder->printAppraisal($order,$output); // Print newly created proforma/order.
        } else {
            $order=Order::wherein('id',explode(",",$id))->get();
            $ret = $printOrder->print($order,$output); // Print newly created proforma/order.
        }

        if ($ret && !is_array($ret))
            return redirect("admin/lvinvoices");
        elseif (is_array($ret))
            return $ret;
    }

    public function printStatementsDue() {
        $this->displayPDF=false;
        $orders = Order::where('status',0)->get();
        
        foreach ($orders as $order) {
            $payments = $order->payments()->orderBy('created_at','desc')->limit(1)->get();
            
            if(!$payments->isEmpty()) {
                foreach ($payments as $payment) {
                    //$nowDate=Carbon::now();
                    $nowDate = Carbon::createFromDate(2017, 10, 15, 'America/New_York');
                    $paymentDate=Carbon::createFromFormat('m-d-Y',date('m-d-Y',strtotime($payment->created_at)))->addDays(20);
                    
                    //echo $order->id.' '.$nowDate.' '. $paymentDate.'<br>';
                    $timeDiff = $nowDate->diffInDays($paymentDate);
                    if ($timeDiff >= 20) {
                        $this->printStatement($order->id,0,$order);
                    }
                    //echo '<pre>';print_r($timeDiff);    
                }
            } else {
                $nowDate=Carbon::now();
                $orderDate=Carbon::createFromFormat('m-d-Y',date('m-d-Y',strtotime($order->created_at)));
                $timeDiff = $orderDate->diffInDays($nowDate);
                if ($timeDiff >= 20) {
                    $this->printStatement($order->id,0,$order);
                }
                //echo $order->id.' '.$nowDate.' '.$orderDate.'<br>';
                //echo '<pre>';print_r($timeDiff);
                
            }
        }

        PDF::Output(str_replace(' ','-',$order->b_company).'- Statement -'.$order->id.'.pdf', 'I');
    }


    public function printStatement($id, $status=null, $order=null, $statementDate='') {
        // if ($this->displayPDF==true)
        //     $order=Order::find($id);
        
        if ($status=="paid")
            $bstatus = 1;
        else $bstatus = 0;

        $orders = Order::whereHas('customers',function($query) use($id) {
            $query->where('id', $id);
        })->where('status',$bstatus)
            ->where('method','<>','On Memo')
            ->get();
            
            
        if (!$orders->count()) {
            ob_start(); ?>
                No order is found with the following Invoice / Memo # <?= $id ?>
            <?php
            echo ob_get_clean();
            return null;
        }

        // set document information
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $order = $orders->first();
        
        $purchasedFrom = !isset($order->purchased_from) ? 0 : $order->purchased_from;

        $customer_id = $id;
        
        $email = 'info@swissmadecorp.com';
        if ($purchasedFrom==1)
            $email = 'signtimeny@gmail.com';

        PDF::setHeaderCallback(function($pdf) use ($purchasedFrom) {
            // Logo
            if ($purchasedFrom==0) {
                $image_file = 'assets/logo-swissmade.jpg';
                $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            } else {
                $pdf->SetY(17);
                $pdf->SetFont('helvetica', 'T', 13);
                $pdf->WriteHTML("<b><i>SIGNATURE TIME</i></b>", false, false, false, false, 'L');
            }

            // Set font
            //$pdf->SetFont('helvetica', 'T', 10);
            // Title            
        });

        if ($this->displayPDF==true) {
            PDF::setFooterCallback(function($pdf){
                // Position at 15 mm from bottom
                $pdf->SetY(-15);
                // Set font
                $pdf->SetFont('helvetica', 'I', 8);
                    // Page number
                $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            });
        }

        // set header and footer fonts
        $pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        // ---------------------------------------------------------
        // add a page
        $pdf::AddPage(); 
        $pdf::setXY($pdf::getPageWidth()-55,25);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold">Statement</div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= date('F d, Y',time()) ?></td>
            </tr>
        </table>
        <?php
        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
        $pdf::SetFont('helvetica', '', 10);
        $pdf::setY(23);
        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>212-840-8463<br>$email", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();$pdf::Ln();$pdf::Ln();
        $countries = new \App\Libs\Countries;
        $country = $countries->getCountry($order->b_country);
        $state_b = $countries->getStateCodeFromCountry($order->b_state);
        $state_s = $countries->getStateCodeFromCountry($order->s_state);
        
        ob_start();
        ?>
            <table>
                <tr>
                <td style="width: 43%;">
                    <?php $customer=$order->customers()->first(); ?>
                    <?= $customer->firstname . ' ' . $customer->lastname ?><br>
                    <?= !empty($customer->company) ? $customer->company . '<br>' : '' ?>
                    <?= !empty($customer->address1) ? $customer->address1 .'<br>' : ''?>
                    <?= !empty($customer->address2) ? $customer->address2 .'<br>' : '' ?>
                    <?= !empty($customer->city) ? $customer->city .', '. $state_b . ' ' . $customer->zip.'<br>': '' ?>
                    <?= !empty($customer) ? $country.'<br>' : '' ?>
                    <?= !empty($customer->phone) ? $customer->phone . '<br>' : '' ?>
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;">
                    <?= $customer->firstname . ' ' . $customer->lastname ?><br>
                    <?= !empty($customer->company) ? $customer->company . '<br>' : '' ?>
                    <?= !empty($customer->address1) ? $customer->address1 .'<br>' : ''?>
                    <?= !empty($customer->address2) ? $customer->address2 .'<br>' : '' ?>
                    <?= !empty($customer->city) ? $customer->city .', '. $state_s . ' ' . $customer->zip.'<br>': '' ?>
                    <?= !empty($country) ? $country.'<br>' : '' ?>
                    <?= !empty($customer->phone) ? $customer->phone . '<br>' : '' ?>
                </td>
                </tr>
            </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #3b4e87;color:#fff">
                        <th width="80" style="border: 1px solid #999;color:#fff">Date</th>
                        <th width="160" style="border: 1px solid #999;color:#fff">Reference</th>
                        <th width="290" style="border: 1px solid #999;color:#fff">Description</th>
                        <th width="110" style="border: 1px solid #999;color:#fff">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // $customer_id=$order->customers->first()->id;
                        // $orders = Order::whereHas('customers',function($query) use($customer_id) {
                        //     $query->where('id', $customer_id);
                        // })
                        // ->where('status',0)
                        // ->where('method','<>','On Memo')
                        // ->get();

                        $totals = 0;
                    foreach ($orders as $order) { 
                        $totals += $order->total;
                    ?>
                    
                    <tr>
                        <td width="80" style="border-bottom: 1px solid #ddd"><?= $order->created_at->format('m/d/Y') ?></td>
                        <td width="160" style="border-bottom: 1px solid #ddd"><?= "Inv. $order->id"?></td>
                        <td width="290" style="border-bottom: 1px solid #ddd"></td>
                        <td width="110" style="text-align:right;border-bottom: 1px solid #ddd"><?= number_format($order->total,2) ?></td>
                    </tr>
                        <?php foreach($order->payments->all() as $payment) { ?>
                        <tr>
                            <td style="border-bottom: 1px solid #ddd"><?= $payment->created_at->format('m/d/Y') ?></td>
                            <td style="border-bottom: 1px solid #ddd"></td>
                            <td style="border-bottom: 1px solid #ddd">Payment Received [<?= $payment->ref ?>]</td>
                            <td style="text-align:right;border-bottom: 1px solid #ddd">-<?= number_format($payment->amount,2) ?></td>
                        </tr>
                        <?php $totals = $totals - $payment->amount; ?>
                        <?php } ?>
                    <?php } ?>

                    <tr>
                        <td colspan="3" style="text-align:right;border-top: 2px solid #eee;background-color: #3b4e87;color:#fff">BALANCE DUE</td>
                        <td style="text-align:right;border-top: 2px solid #eee;border-left: 2px solid #eee;background-color:#d2d9ec">$ <?= number_format($totals,2) ?></td>
                    </tr>
                </tbody>
            </table>
            
            

            <?php
                // $pdf::Ln();
                // $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); ?>
             
            <?php
                // $pdf::Ln();
                // $pdf::Cell(0,0,'Please detach the remittance slip below and return it with your payment');
                // echo str_repeat('.',183);
                // $pdf::Ln();
            ?>

            <table style="padding-top: 25px">
                <tr>
                    <td valign="bottom">Please detach the remittance slip below and return it with your payment</td>
                </tr>
            </table>
            <?=  str_repeat('.',183); ?>
            <div style="text-align: center">REMITTANCE</div>
            
            <br><table style="width: 100%" cellpadding="3">
                <tr>
                    <td style="width: 56%">Please make checks payable to Swiss Made Corp. and mail to:</td>
                    <td style="width: 30%;text-align:right">STATEMENT DATE</td>
                    <td style="width: 15%;text-align:right"><?=date('m/d/Y',time())?></td>
                </tr>
                <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right">CUSTOMER ID</td>
                    <td style="width: 15%;text-align:right"><?=$customer_id?></td>
                </tr>
            </table>
            
            <br>
            <br>Swiss Made Corp.<br>15 W 47th Street, Ste # 503<br>New York, NY 10036<br>212-840-8463<br>info@swissmadecorp.com
            <br><table style="width: 100%" cellpadding="5" cellspacing="5">
                <!-- <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right"><b>DUE DATE</b></td>
                    <td style="width: 15%;text-align:right;border:1px solid #eee;width: 100px"><?= date('m/1/Y', strtotime("+30 days")) ?></td>
                </tr> -->
                <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right"><b>BALANCE DUE</b></td>
                    <td style="width: 15%;text-align:right;border:1px solid #eee;width: 100px">$  <?= number_format($totals,2) ?></td>
                </tr> 
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>                 
                <tr>
                    <td style="width: 56%;text-align:left">Please write Customer ID on your check.</td>
                    <td style="width: 30%;text-align:right"><b>AMOUNT ENCLOSED</b></td>
                    <td style="width: 15%;text-align:right;border:1px solid #eee;width: 100px"></td>
                </tr>                
            </table>
            
            <?php
            $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        
        if ($this->displayPDF==true) {
            $pdf::Output(str_replace(' ','-',$order->b_company).'- Statement -'.$order->id.'.pdf', 'I');
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
        $order = Order::find($id);
        if (!$order)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        if ($order->status == 0)
            $status = "UnPaid";
        elseif ($order->status==1)
            $status = "Paid";
        elseif ($order->status==2) 
            $status = "Return";
        elseif ($order->status==3)
            $status = "Transferred";
        elseif ($order->status==4) 
            $status = "Canceled";
        
            return view("admin.orders.show",['pagename' => 'Invoice #'.$id . ' - ' .  $status, 'order' => $order]);
    }

    public function memotransfer(Request $request,$id)
    {
        $order = Order::find($id);
        return view('admin.orders.memotransfer',['pagename' => 'Memo to Invoice','order' => $order]);
    }

    // transfer memo to invoice and save it
    public function memoStore(Request $request)
    {

        $oldOrder = Order::find($request['order_id']);
        $product_ids = array();

        if ($oldOrder) {
            if ($oldOrder->method=='On Memo') {
                $created_at = $request['created_at'];
                
                if ($request['b_country']!='231') 
                    $request['b_state'] = '';

                if ($request['s_country']!='231') 
                    $request['s_state'] = '';

                $status = 0;
        
                //$freight = $request['freight'];
                $orderArray = array_slice($request->all(),4);
                $excludeArray = array('id','product_name','qty','price','serial','payment','created_at','model','discount');
                foreach ($orderArray as $key => $index) {
                    if ( in_array($key,$excludeArray)) {
                            unset($orderArray[$key]);
                    }
                }
                  
                $orderArray['freight'] = $request['freight'];
                $orderArray['method'] = $request['payment'];
                $orderArray['status'] = 0;
                $oldOrder->freight = 0;
                
                //dd($request->all());
                
                if ($created_at) {
                    $orderArray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
                    $orderArray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
                }
                
                //dd($oldOrder->method);
                $order = Order::create($orderArray);
                
                $customer = Customer::find($request['customer_id']);
                $order->customers()->attach($customer->id);
                $subtotal = 0;
                $tax = 0;
        
                if ($customer->cgroup == 0) 
                    $tax = Taxable::where('state_id',$request['s_state'])->value('tax');
                
                $keys = $request['id'];
                
                $prev1='p';$prev2='p';
                foreach ($keys as $index => $key) {
                    if ($key) {
                        $product_id = $key;
                        $qty = $request['qty'][$index];
                        $price = $request['price'][$index];
                        $serial = $request['serial'][$index];
                        $product_name = $request['product_name'][$index];

                        $product = Product::find($product_id);
                        if ($qty>0) {
                            $order->products()->attach($product_id, [
                                'qty' => $qty,
                                'price' => $price,
                                'serial' => $serial,
                                'product_name' => $product_name
                            ]);
                            
                            $product_ids[] = $product_id;
                            if ($product->category_id!=74)  {
                                $product->p_qty -= 1;
                                $product->update();
                            }
                            $subtotal = $subtotal + ($price*$qty);
                        }

                        $productExists = $oldOrder->products()->where('product_id',$product_id);

                        if ($productExists->exists()) {
                            $oldProduct = $productExists->get();
                            $oldOrder->products()->detach($oldProduct);
                            
                            // if ($oldOrder->comments && $prev1=='p')
                            //     $prev1=$oldOrder->comments."\n";
                            // else $prev1='';

                            // $oldOrder->update([
                            //     'comments'=>$prev1.$product_name ." (SN: $product->p_serial) was transferred to invoice#: ".$order->id,
                            // ]);
                            // $prev1='';

                            // if ($order->comments && $prev2=='p')
                            //     $prev2=$order->comments."\n";
                            // else $prev2='';

                            $order->update([
                                //'comments'=>$prev2.$product_name ." (SN: $product->p_serial) was transferred from memo#: ".$oldOrder->id,
                                'po' => 'MEMO '.$oldOrder->id
                            ]);
                            // $prev2='';
                        }
                    }
                }
                
                eBayEndItem::dispatch($product_ids);

                if ($oldOrder->products->count() == 0) {
                    $oldOrder->update([
                        'status' => 3,
                        'total' => 0
                    ]);
                } 
                
                $this->refreshOrderTotals($order);
                $this->refreshOrderTotals($oldOrder,1);
            }
        }

        return redirect("admin/orders/".$order->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::find($id);
        
        if (!$order)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        if ($order->status == 0)
            $status = "UnPaid";
        elseif ($order->status==1)
            $status = "Paid";
        elseif ($order->status==2) 
            $status = "Return";
        elseif ($order->status==3)
            $status = "Transferred";
        elseif ($order->status==4)
            $status = "Canceled";

        $method = $order->method;
        
        return view("admin.orders.edit",['pagename' => $method.' #'.$id . ' - ' .  $status, 'order' => $order]);
    }

    public function UpdateOrderStatus(Request $request) {
        $orderId = $request['orderid'];
        $status = $request['status'];

        $order = Order::findOrFail($orderId);

        $order->status = $status;
        $order->update();
    }
    
    // Deletes individual product from the order when in edit mode. 
    public function destroyproduct(Request $request) {
        //return response()->json('success');
        if ($request->ajax()) {
            
            $id = $request['orderid'];
            $product_id = $request['productid'];
            $order = Order::find($id);
            $product = $order->products->find($product_id);
            \DB::table('order_product')
                    ->where('op_id', $request['opid'])
                    ->delete();

            //$order->products()->detach($product);
            $cgroup = $order->customers->first()->cgroup;
            $subtotal = 0;
            $total = 0;
            $tax = 0;

            $order->load('products'); // Refreshes products after removal from the table

            if ($product->category_id!=74)  {
                if ($order->method != 'On Memo')
                    $product->update([
                        'p_qty' => $product->p_qty + $product->pivot->qty,
                        'p_status' => 0
                    ]);
                else 
                    $product->update([
                        'p_status' => 0
                    ]);
            }
            
            $this->refreshOrderTotals($order);
                       
            return response()->json('success');
        }
    }

    public function refreshOrderTotals($order,$is_transferred = 0) {
        $cgroup = $order->customers->first()->cgroup;
        $subtotal = 0;
        $total = 0;
        $tax = 0;

        foreach ($order->products as $product) {
            $qty = $product->pivot->qty;
            $price = $product->pivot->price;

            $subtotal = $subtotal + ($price*$qty);
        }

        $freight = $order->freight;
        
        if ($order->method=='Canceled') {
            $freight = 0;
            $status = 4;
        }

        if ($cgroup == 1 && !$order->taxexempt && $order->taxable<1) {
            $tax = Taxable::where('state_id',$order->s_state)->value('tax');
            $total = number_format($subtotal + ($subtotal * ($tax/100))+$freight,2, '.', '');
            $tax = ($subtotal * ($tax/100));
        } elseif ($order->taxable > 0) {
            if (strpos($order->taxable,',') !== false)
                $tax = str_replace(',','',$order->taxable);
            else
                $tax = $order->taxable;

            if ($tax < 10) {
                $total = number_format($subtotal + ($subtotal * ($tax/100))+$freight,2, '.', '');
                $tax = ($subtotal * ($tax/100));
            } else {
                $tax = (float)number_format($tax,2, '.', '');
                $total = number_format($subtotal + $tax+$freight,2, '.', '');
            }

        } else {
            $tax = 0;
            $total = $subtotal+$freight;
        }
        
        // This was added 2/16/22
        $total -= number_format($order->discount,2,'.','');
        $status=$order->status;

        $orderTotal = 0;
        $freight = $order->freight;
        $discount = $order->discount;

        foreach($order->payments->all() as $payment) {
            $orderTotal = $orderTotal+$payment->amount;
        }

        if ($orderTotal+$discount == $total && $order->method != "On Memo")
            $status = 1;

        // This was added 2/16/22

        // This was removed 2/16/22

        // if ($order->method != 'On Memo') {
        //     if ((float)$order->payments->sum('amount') == (float) $total)
        //         $status = 1;
        //     elseif ($order->total != 0 && $order->subtotal==0 && $freight==0)
        //         $status = 2;
        //     elseif ($order->method=='Canceled')
        //         $status = 4;
        //     else $status = 0;
        // } elseif ($is_transferred) 
        //     $status = 3;
        //   else $status = 0;
        
        // $status = $order->status;
        // if ($total == 0 && $order->method=='On Memo') 
        //     $status=1;
        
        // This was removed 2/16/22

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'taxable' => $tax,
            'freight' => $freight,
            'status' => $status
        ]);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = \Validator::make($request->all(), [
            'b_company' => "required",
        ]);
       
        //eBayEndItem::dispatch($product_ids);
        //die;
        if ($validator->fails()) {
            return back()
                ->withInputs($request->all())
                ->withErrors($validator);
        }

        $order = Order::find($id);

        $orderArray = array_slice($request->all(),3);
        $freight = $orderArray['freight'];
        $method = $order->method;
        $orderArray['method'] = $request['method'];
        $orderArray['taxexempt'] = isset($orderArray['taxexempt']) && $orderArray['taxexempt'] == 'on' ? 1 : 0;
        $orderArray['status'] = $order->status; // Unpaid memo

        $customer = Customer::findOrFail($request['customer_id']);

        if (!$customer->address1) {
            $data = array(
                'firstname' => allFirstWordsToUpper($request['b_firstname']),
                'lastname' => allFirstWordsToUpper($request['b_lastname']),
                'address1' => allFirstWordsToUpper($request['b_address1']),
                'address2' => $request['b_address2'],
                'phone' => localize_us_number($request['b_phone']),
                'country' => $request['b_country'],
                'state' => $request['b_state'],
                'city' => strtoupper($request['b_city']),
                'zip' => $request['b_zip']
            );
            $customer->update($data);
        }

//        if (\Auth::user()->name != 'Edward B') { 
            $order->customers()->detach();
            $order->customers()->attach($request['customer_id']);

            $order->update($orderArray);   
//        }

        $status = $order->status;
        // if ($request['method'] == 'On Memo'){
        //     $status = 0;
        // } 
        
        $counter=0;
        $product_ids=array();$product=null;
        if (isset($request['product_id'])) {
            foreach ($request['product_id'] as $index => $key) {
                $product_id = $key;
                $price = $request['price'][$index];
                $product_name = $request['product_name'][$index];
                $product_ids[]=$product_id;
                
                if ($request['op_id'][$index]==0) {
                    $qty = $request['qty'][$index];
                    $serial = $request['serial'][$index];
                    if ($product_id){
                        $product = Product::find($product_id);
                        
                        $order->products()->attach($product->id, [
                            'qty' => $qty,
                            'price' => $price,
                            'serial' => $serial,
                            'product_name' => $product_name,
                        ]);
                        
                        $theshow=TheShow::where('product_id',$product_id);
                        if($theshow->get()){
                            $theshow->delete(); 
                        }
                        
                        if ($product->category_id!=74 && $request['method'] == 'Invoice') {
                            if ($product->p_qty > 0) {
                                $product->p_qty = 0;
                                $product->p_status=8; // mark as sold
                            } else $product->decrement('p_qty');
        
                            $product->update();
                            //$this->findIdenticalItem($product->p_reference,$product->p_color,$product->p_condition,$product->p_strap);
                        } elseif ($product->category_id!=74 && $request['method'] == 'On Memo') {
                            $product->p_status=1;
                            $product->update();
                        }

                    }
                    $counter++;
                } else {
                    
                    $product = Product::find($product_id);
                    $qty = $request['qty'][$index];
                    
                    if ($product->category_id != 74) {
                        if ($order->method == "On Memo" && $method=='Invoice') { // invoice that changes to memo
                            $product->p_qty = $product->p_qty + $qty;
                            $product->p_status = 1;
                            $product->save();
                        
                        } elseif ($order->method == "Invoice" && $method=='On Memo') { // On memo that changes to Invoice
                            $product->p_qty = $product->p_qty - $qty;
                            $product->p_status = 8;
                            $product->save();
                        } elseif ($request['method'] == 'Canceled') {
                            $product->update([
                                'p_qty' => $product->p_qty + $qty,
                                'p_status' => 0
                            ]);

                            $qty = 0;
                        } elseif ($method == "Invoice") {
                            if ($qty == 0) {
                                $product->p_qty = 1;
                                $product->p_status = 0;
                                $product->save();
                            } else  {
                                $product->p_qty = 0;
                                $product->p_status = 8;
                                $product->save();
                            }
                        } elseif ($order->method=="On Memo" & $method=="On Memo" && $product->p_status==0) {
                            $product->p_status = 1;
                            $product->save();
                        }

                        $op_id = $request['op_id'][$index];
                        if ($op_id > 0) {
                            \DB::table('order_product')
                                ->where('op_id', $op_id)
                                ->update([
                                    'qty' => $qty,
                                    'price' => $price,
                                    'product_name' => $product_name
                                ]);
                        }
                    } else {
                        $op_id = $request['op_id'][$index];
                        if ($op_id > 0) {
                            \DB::table('order_product')
                                ->where('op_id', $op_id)
                                ->update([
                                    'qty' => $qty,
                                    'price' => $price,
                                    'product_name' => $product_name
                                ]);
                        }
                    }
                }
                //if ($product) $product->save();
            }

            //$this->setAmazonItemOutOfStock($product_ids);
        }
        
        eBayEndItem::dispatch($product_ids);
        // $listings = EbayListing::whereIn('product_id',$product_ids)->where('status','active');
        // $ebayListings = $listings->get();
        // if ($ebayListings){
        //     foreach ($ebayListings as $listing) {
        //         $item=EbayController::EndItem(['datainfo' => "reason=NotAvailable&itemID=".$listing->listitem]);
        //     }
        //     $listings->delete();
        // }
        
        $this->saveEmailForNewsLetter($request['email'],$request['b_company']);
        $this->refreshOrderTotals($order);

        if ($order->method != 'On Memo') {
            if ($order->payments->sum('amount') == $order->total) {
                if ($method == "Canceled")
                    $status = 4;
                else $status = 1;
        } elseif ($order->total != 0 && $order->subtotal==0 && $freight==0)
                $status = 2;
            elseif ($order->method=='Canceled')
                $status = 4;
            else $status = 0;
        }

        if ($order->total < 1)
            $status = 1;

        $order->update(['status' => $status]); 

        return redirect("admin/lvinvoices");
      
    }

    private function saveEmailForNewsLetter($email,$company) {
        $validCompanies = array('Website','Chrono24','eBay');
        if ($email && in_array($company,$validCompanies)) {
            $count = Newsletter::select('email')->where('email',$email)->count();
            if (!$count)
                Newsletter::create([
                    'email' => $email,
                    'subscribed' => 1
                ]);
        }
    }

    private function setAmazonItemOutOfStock($product_ids) {
        $uri = "https://wfda.watchfacts.com/listings/S19WM2KU4D2SRP";
        
        $listings = AmazonListings::whereIn('product_id',$product_ids);
        $amazon_listings = $listings->get();

        if ($amazon_listings) {
            foreach ($product_ids as $product) {
                $items[] = array('sku'=>$product);
            }

            $arr = array(
                "category"=> "watches",
                "requestId"=> "S19WM2KU4D2SRP",
                "requestType"=> "inventory",
                "sellerId"=> "1162",
                "items"=> $items
            );

            $d = json_encode($arr);
        
            $response = \Httpful\Request::post($uri)
                ->sendsJson()
                ->body($d)
                ->sendsType(\Httpful\Mime::FORM)    
                ->send();
            
            $listings->delete();
        }

        // Put all sold item skus on Amazon out of stock.
        //$listings = AmazonListings::whereIn('product_id',$product_ids);
        //$amazon_listings = $listings->get();
        //if ($amazon_listings) {
        //    foreach ($amazon_listings as $product) {
        //        dispatch(new AmazonSubmitProductQueue($product->product_id,0,'USA'));
        //    }
        //    $listings->delete();
        //}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Removes entire order including all the products
    public function destroy($id)
    {
        $order = Order::find($id);
        $product_ids = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo") {
                    $product->p_qty = $product->p_qty + $product->pivot->qty;
                    $product->p_status = 1;
                    $product->update();
                } 

            }
        }

        $order->products()->detach();
        $order->customers()->detach();

        $payment = Payment::where('order_id',$id);
        $payment->delete();

        $order->delete();

        Session::flash('message', "Successfully deleted invoice!");
        return back();
    }
}
