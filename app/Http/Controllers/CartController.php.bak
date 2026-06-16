<?php

namespace App\Http\Controllers;

use Session;
//use App\Mail\GmailCustomer; 
use App\Mail\GMailer;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use App\Models\Taxable;
use App\Models\Product;
use App\Models\Customer;
use App\Models\DiscountRule;
use App\Models\Newsletter;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Mail\AbandonedEmail; 
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
//use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Omnipay\Common\Helper;

class CartController extends Controller
{
    private $domestic = 79;
    private $international = 125;

    private static $cartProducts;
    private $rate = '1';
    private $currencyName = 'USD';
    private $cartTax = 0;
    private $cartTotal = 0;
    private $cartFreight = 0;
    private $cartItems = [];
    private $discountPromo;
    
    private function discount($promocode,$orginal_amount,$action) {
            $webprice=0;
            
            foreach (Cart::products() as $products) {
                $webprice+=$products['webprice'];
            }

            if ($action==0)
                $amount = $webprice-$orginal_amount;
            elseif ($action==1)
                $amount = $webprice*($orginal_amount/100);
            elseif ($action==5) {
                // Todo enumerate all items in the cart and apply discount
                $amount = $webprice*($orginal_amount/100);
            } else {
                // Todo enumerate all items in the cart and apply discount
                $amount = 0;
            }

            $discount = [
                'original_amount' => $orginal_amount,
                'action' => $action,
                'amount'=>$amount,
                'promocode'=>$promocode,
                'newprice' => $webprice,
            ];
            
            session()->put('discount', $discount);
            
            $discountAmt = number_format($amount,2);

            return $discountAmt;
    }

           /**
     * Gets, validates and returns the connecting client's IP
     */
    public function promo(Request $request) {
        if ($request->ajax()) {
            $now = (date('Y-m-d',strtotime(now())));
            $discountRule = DiscountRule::where('discount_code',$request['promocode'])
                ->where('start_date','<=',$now)
                ->where('end_date','>=',$now)
                ->first();
            
            if ($discountRule) {
                if ($discountRule->is_active) {
                    $discountAmt=$this->discount($request['promocode'],$discountRule->amount,$discountRule->action);
                    return array('error'=>0,'amount'=>$discountRule->amount,'content'=>"A discount has been applied.");
                } else return array('error'=>1,'content'=>"This promo code has expired.");
            } else {
                return array('error'=>1,'content'=>"That wasn't a correct promo code.");
            }
        }
    }

    private function USAEPay($request,$latestOrderId,$customer=null) {
        
        $fullname=explode(' ',strip_tags($request['name']));
        if (count($fullname)==1) {
            session()->flash('message','You must enter First name and Last name on the card');
            return redirect()->action('App\Http\Controllers\CartController@cart');
        }
        $exp = str_replace(['/',' '],'',strip_tags($request['expiry']));

        $trans = new \App\Libs\usaepay(); 
        //$trans=new umTransaction;
        
        $trans->usesandbox=false;     // Sandbox true/false
        $trans->ip=$_SERVER['REMOTE_ADDR'];   // This allows fraud blocking on the customers ip address
        $trans->testmode= 0;    // Change this to 0 for the transaction to process

        $trans->command="cc:sale";    // Command to run; Possible values are: cc:sale, cc:authonly, cc:capture, cc:credit, cc:postauth, check:sale, check:credit, void, void:release, refund, creditvoid and cc:save. Default is cc:sale.
        
        $this->getCartTotal();

        if ($trans->usesandbox) {
            $trans->key="_TRj9Izy00iVk8PW3b56J2j8a0W891BX";      // Your Source Key
            $trans->pin="1234";      // Source Key Pin
            $trans->card="4000100011112224";
            $trans->exp="0924";
            $trans->amount="1.00";
            $trans->invoice=$latestOrderId;
            $trans->cardholder="Test T Jones";
            $trans->street="1234 Main Street";
            $trans->zip="90036";
            $trans->description="Online Order";
            $trans->cvv2="435";

        } else {
            $trans->key=config('usaepay.key');      // Your Source Key old key IfpXhgk5veqBPxQPzZ6dWt9H3NpYwnUY
            $trans->pin=config('usaepay.pin');      // Source Key Pin
            $trans->card = strip_tags($request['number']);     // card number, no dashes, no spaces
            $trans->exp = $exp;          // expiration date 4 digits no /
            $trans->amount = $this->cartTotal;           // charge amount in dollars
            $trans->invoice = $latestOrderId;          // invoice number.  must be unique.
            $trans->cardholder = strip_tags($request['name']);   // name of card holder
            $trans->street = strip_tags($customer['b_address1']);   // street address
            $trans->zip = strip_tags($customer['b_zip']);         // zip code
            $trans->description = "Online Order";  // description of charge
            $trans->cvv2=strip_tags($request['cvc']);          // cvv2 code
        }

        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($customer['b_country']);
        $state_b = $countries->getStateCodeFromCountry($customer['b_state']);

        if ($customer['b_country']==231)
            $country_b = "USA";
            
        $fullname=explode(' ',strip_tags($request['name']));
        $trans->billfname = $fullname[0];
        $trans->billlname = $fullname[1];
        $trans->billcompany = strip_tags($customer['b_company']);
        $trans->billstreet = strip_tags($customer['b_address1']);
        $trans->billstreet2 = strip_tags($customer['b_address2']);
        $trans->billstate = $state_b;
        $trans->billcity = strip_tags($customer['b_city']);
        $trans->billzip = strip_tags($customer['b_zip']);
        $trans->billcountry = $country_b;

        $trans->shipfname = $fullname[0];
        $trans->shiplname = $fullname[1];
        $trans->shipcompany = strip_tags($customer['b_company']);
        $trans->shipstreet = strip_tags($customer['b_address1']);
        $trans->shipstreet2 = strip_tags($customer['b_address2']);
        $trans->shipstate = $state_b;
        $trans->shipcity = strip_tags($customer['b_city']);
        $trans->shipzip = strip_tags($customer['b_zip']);
        $trans->shipcountry = $country_b;

        if($trans->Process())
        {
            return $trans;
            // echo "<b>Card Approved</b><br>";
            // echo "<b>Authcode:</b> " . $trans->authcode . "<br>";
            // echo "<b>RefNum:</b> " . $trans->refnum . "<br>";
            // echo "<b>AVS Result:</b> " . $trans->avs_result . "<br>";
            // echo "<b>Cvv2 Result:</b> " . $trans->cvv2_result . "<br>";
        } else {
            return $trans;
            //echo "<b>Card Declined</b> (" . $trans->result . ")<br>";
            //echo "<b>Reason:</b> " . $trans->error . "<br>";
            //if(@$trans->curlerror) echo "<b>Curl Error:</b> " . $trans->curlerror . "<br>";
        } 
    }

    private function getCartTotal() {
        $tax = 0;
        $subtotal = 0;
        $total = 0;
        $webprice=0;

        $customer= session()->get('customer');
        $discountRule = $this->discountRule();

        foreach (Cart::products() as $product) {
            $webprice+=$product['webprice']*$product['qty'];
            $items[] = [
                'ProductName'           => $product['sku'],
                'ProductDescription'    => $product['product_name'],
                'ProductSellingPrice'   => $product['webprice'],
                'ProductQty'            => $product['qty'],
                'ProductCostPrice'      => $product['cost']
            ];
        }

        if ($discountRule) {
            if ($discountRule['action'] == 4 || $discountRule['action'] == 5) {
                $webprice = ceil($webprice - ($webprice * ($discountRule->amount/100)));
            } else {
                $discount = $this->getDiscountAmount();
                $webprice -= $discount;
            }

            if (!$discountRule['free_shipping']) {
                if ($customer['b_country'] == 231)
                    $freight = $this->domestic;
                else $freight = $this->international;
            } else $freight = 0;
        } else {
            if ($customer['b_country'] == 231)
                $freight = $this->domestic;
            else $freight = $this->international;
        }
        
        if ($customer['b_state'] == 3956 && $customer['b_country'] == 231) {
            $tax = Taxable::where('state_id',$customer['b_state'])->value('tax');
            $total = $webprice + ($webprice * ($tax/100))+$freight;
        } else
            $total = $webprice + $freight;

        $this->cartFreight = $freight;
        $this->cartTax = $tax;
        $this->cartSubTotal = $webprice;
        $this->cartTotal = (Float)number_format($total,2, '.', '');
        $this->cartItems = $items;
    }

    public function checkIfOrderIsValid($latestOrderId) {
        $post_array = array(
            'ApiLogin' => 'bCbp27XH',
            'ApiKey'=> '66r6neTYbIzEUlon',
            'Action' => 'getOrderStatus',
            'OrderNumber'  => $latestOrderId
        );

        // Convert post array into a query string
        $post_query = http_build_query($post_array);
        
        // Do the POST
        $ch = curl_init('https://eye4fraud.com/api/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Show response
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
    }

    public function finalizePurchase(Request $request) {
        $customer= session()->get('customer');
        
        if ($request['number']==null && $request['expiry']==null) {
            $latestOrderId = Str::uuid()->toString(); //Order::latest('id')->first()->id+1;
            $this->getCartTotal();
            $method = "Wire Transfer";
            $order = $this->createOrder($method);
        } else {
            $this->getCartTotal();
            $trans = $this->AuthorizeNet($request,$customer);
            //$trans[0] = ''; $trans[1] = 123;
            $latestOrderId = Str::uuid()->toString(); //Order::latest('id')->first()->id+1;
        
            if ($trans[0] == "Error" || $trans[0] == "Declined") {
                session()->flash('message',$trans[1]);
                return redirect()->action('App\Http\Controllers\CartController@Unsuccessful');
            }

            $method = ''; $methodPayment='';
            switch ($request['number'][0]) {
                case '3':
                    $method = 'American Express';
                    $methodPayment = "AMEX";
                    break;
                case '5':
                    $method = 'Master Card';
                    $methodPayment = "MC";
                    break;
                case '4':
                    $method = 'Visa';
                    $methodPayment = "VISA";
                    break;
                case '6':
                    $method = 'Discover';
                    $methodPayment = "DISC";
                    break;
                default:
                    $methodPayment = "OTHER";
            }

            $company = strip_tags($customer['b_company']);
            if (!$company)
                $company = strip_tags($customer['b_firstname'].' '.$customer['b_lastname']);

            $countries = new \App\Libs\Countries;
            $country_b = $countries->getCountry($customer['b_country']);
            $state_b = $countries->getStateCodeFromCountry($customer['b_state']);

            $cc = strip_tags(str_replace(" ","",$request['number']));
            $fullname=explode(' ',strip_tags($request['name']));
            
            $post_array = array(
                //////// Required fields //////////////
                'ApiLogin'              => 'bCbp27XH',
                'ApiKey'                => '66r6neTYbIzEUlon',
                'TransactionId'         => $trans[1],
                'OrderDate'             => date("Y-m-d G:i:s"),
                'OrderNumber'           => $latestOrderId,
                'BillingFirstName'      => $fullname[0],
                'BillingMiddleName'     => '',
                'BillingLastName'       => $fullname[1],
                'BillingCompany'        => $company,
                'BillingAddress1'       => strip_tags($customer['b_address1']),
                'BillingAddress2'       => strip_tags($customer['b_address2']),
                'BillingCity'           => strip_tags($customer['b_city']),
                'BillingState'          => $state_b,
                'BillingZip'            => strip_tags($customer['b_zip']),
                'BillingCountry'        => $country_b,
                'BillingEveningPhone'   => strip_tags($customer['b_phone']),
                'BillingEmail'          => strip_tags($customer['email']),
                'IPAddress'             => $_SERVER['REMOTE_ADDR'],
                'ShippingFirstName'     => $fullname[0],
                'ShippingMiddleName'    => '',
                'ShippingLastName'      => $fullname[1],
                'ShippingCompany'       => $company,
                'ShippingAddress1'      => strip_tags($customer['b_address1']),
                'ShippingAddress2'      => strip_tags($customer['b_address2']),
                'ShippingCity'          => strip_tags($customer['b_city']),
                'ShippingState'         => $state_b,
                'ShippingZip'           => strip_tags($customer['b_zip']),
                'ShippingCountry'       => $country_b,
                'ShippingEveningPhone'  => strip_tags($customer['b_phone']),
                'ShippingEmail'         => strip_tags($customer['email']),
                'ShippingCost'          => $this->cartFreight,
                'GrandTotal'            => $this->cartTotal,
                'CCType'                => $methodPayment,
                'CCFirst6'              => substr($cc,0,6),
                'CCLast4'               => substr($cc,-4),
                //'CIDResponse'           => $trans->cvv2_result_code,
                'AVSCode'               => 'Y',
                'LineItems'             => $this->cartItems,

                /////////// Optional fields /////////////
                'SiteName'                  => 'Woocommerce',
                'ShippingMethod'            => $country_b == 'United States' ? 'FedEx Overnight' : "FedEx International Priority",
                'CCExpires'                 => str_replace(" ", "", $request['expiry']),
                //'CustomerComments'          => 'Please send soon',
                //'SalesRepComments'          => 'No comment',
            );
            

            // Convert post array into a query string
            $post_query = http_build_query($post_array);
            
            // Do the POST
            $ch = curl_init('https://eye4fraud.com/api/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $order = $this->createOrder($methodPayment,$latestOrderId,$trans[1]);
            
            //$order = Order::find(6821)  ;
            if (session()->has('discount')) {
                $discountPromo = session()->get('discount');
                $discountRule = DiscountRule::where('discount_code',$discountPromo['promocode'])->first();
                $discountRule->update(['is_active'=>0]);
            }
            
            $this->clearAllCookies();
            
            if ($order->email) {    
                $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
                $filename = $printOrder->print($order,"email"); // Print newly create proforma.
            }
        }

        return view('payment/thankyou',['order'=>$order]);

    }

    private function AuthorizeNet($request,$customer) {   
        $gateway = Omnipay::create('AuthorizeNetApi_Api');
        $gateway->setAuthName(config('authorize_net.login_id'));
        $gateway->setTransactionKey(config('authorize_net.key'));

        $fullname=explode(' ',strip_tags($request['name']));
        if (count($fullname)==1) {
            session()->flash('message','You must enter First name and Last name on the card');
            return redirect()->action('App\Http\Controllers\CartController@cart');
        }
        $exp = explode('/',strip_tags($request['expiry']));

        $items='';
        foreach (Cart::products() as $product) {
            $items .= $product['product_name'] . ' (' . $product['id'] . "); " ;
        }

        $discountRule = $this->discountRule();
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($customer['b_country']);
        $this->getCartTotal();
        
        if ($exp[0] < date("m") && $exp[1] <= date("y")) {
            return ["Error","Credit Card has expired"];
        }

        $card = new CreditCard(array(
            'firstName'    => $fullname[0],
            'lastName'     => $fullname[1],
            'number'       => strip_tags($request['number']),
            'expiryMonth'  => $exp[0],
            'expiryYear'   => $exp[1],
            'cvv'          => strip_tags($request['cvc']),
            'description'  => $items,
            //'invoiceNumber'      => $latestOrderId,
            'billingAddress1' => strip_tags($customer['b_address1']),
            'billingCity' => strip_tags($customer['b_city']),
            'billingPostcode' => strip_tags($customer['b_zip']),
            'billingPhone' => strip_tags($customer['b_phone']),
            'billingCountry' => $country_b,

            'shippingAddress1' => strip_tags($customer['b_address1']),
            'shippingCountry' => $country_b,
            'shippingCity' => strip_tags($customer['b_city']),
            'shippingPostcode' => strip_tags($customer['b_zip']),
            'shippinggPhone' => strip_tags($customer['b_phone'])
        ));
        
        Log::debug(array($request['name'],$request['number'],$request['expiry'],$request['cvc']));

        $number = $card->getNumber();
        $validateNumber = Helper::validateLuhn($number);
        
        if ($validateNumber) {
            
            // Generate a unique merchant site transaction ID.
            $transactionId = rand(100000000, 999999999);
            
            $response = $gateway->authorize(array(
                'amount'                   => number_format($this->cartTotal,2, '.', ''),
                'currency'                 => $this->currencyName,
                'transactionId'            => $transactionId,
                'card'                     => $card,
            ))->send();
            
            $transactionReference = $response->getTransactionReference();
            
            $resultCode = $response->getData()['messages']['resultCode'];
            if ($response->isSuccessful()) {
                return [$resultCode, $transactionReference];
            } else {
                return [$resultCode, $response->getData()['messages']['message']['text']];
            }
        } else {
            return ["Error","There was an issue processing your credit card. Please verify that your billing address matches your credit card."];
        }
    }

    private function clearAllCookies() {
        //dd('clearAllCookes');
        session()->put('cart_product',[]);
        session()->forget('cart_product');

        session()->put("customer",[]);
        session()->forget('customer');
        
        session()->put('discount',[]);
        session()->forget('discount');
        if (Cookie::has('cookie_cart')) {
            Cookie::queue(Cookie::forget('cookie_cart'));
        }
    }

    public function finalizePurchase2(Request $request) {
        //$r = $this->loadFromSession($request);
        

        $customer= session()->get('customer');
        // $credentials = array(
        //     'username'       => config('paypal.live.payflow_username'),
        //     'password'       => config('paypal.live.payflow_password'),
        //     'vendor'         => 'swissmadecorp',
        //     'partner'        => 'PayPal',
        //     'testMode'       => true, // Or false for live transactions.
        // );

        //$gateway->initialize($credentials);
        
        $gateway = Omnipay::create('AuthorizeNetApi_Api');
        $gateway->setAuthName(config('authorize_net.login_id'));
        $gateway->setTransactionKey(config('authorize_net.key'));
        //$gateway->setTestMode(true);

        $fullname=explode(' ',strip_tags($request['name']));
        if (count($fullname)==1) {
            session()->flash('message','You must enter First name and Last name on the card');
            return redirect()->action('App\Http\Controllers\CartController@cart');
        }
        $exp = explode('/',strip_tags($request['expiry']));

        $items='';$webprice=0;
        foreach (Cart::products() as $product) {
            $webprice+=$product['webprice'];
            $items .= $product['product_name'] . ' (' . $product['id'] . "); " ;
        }

        $discountRule = $this->discountRule();
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($customer['b_country']);
        $latestOrderId = Str::uuid()->toString(); //Order::latest('id')->first()->id+1;

        $card = new CreditCard(array(
            'firstName'    => $fullname[0],
            'lastName'     => $fullname[1],
            'number'       => strip_tags($request['number']),
            'expiryMonth'  => $exp[0],
            'expiryYear'   => $exp[1],
            'cvv'          => strip_tags($request['cvc']),
            'description'  => $items,
            'billingAddress1' => strip_tags($customer['b_address1']),
            'billingCity' => strip_tags($customer['b_city']),
            'billingPostcode' => strip_tags($customer['b_zip']),
            'billingPhone' => strip_tags($customer['b_phone']),
            'billingCountry' => $country_b,

            'shippingAddress1' => strip_tags($customer['b_address1']),
            'shippingCountry' => $country_b,
            'shippingCity' => strip_tags($customer['b_city']),
            'shippingPostcode' => strip_tags($customer['b_zip']),
            'shippinggPhone' => strip_tags($customer['b_phone'])
        ));
        
        Log::debug(array($request['name'],$request['number'],$request['expiry'],$request['cvc']));

        $number = $card->getNumber();
        $validateNumber = Helper::validateLuhn($number);
        
        if ($validateNumber) {
            if ($discountRule) {
                $webprice = ceil($webprice - ($webprice * ($discountRule->amount/100)));
            } else {
                $discount = $this->getDiscountAmount();
                $webprice -= $discount;
            }
            
            $tax = 0;$subtotal = 0;$total = 0;$orderstatus = 0;

            if ($customer['b_country'] == 231)
                $freight = $this->domestic;
            else $freight = $this->international;

            if ($customer['b_state'] == 3956 && $customer['b_country'] == 231) {
                $tax = Taxable::where('state_id',$customer['b_state'])->value('tax');
                $total = $webprice + ($webprice * ($tax/100))+$freight;
            } else
                $total = $webprice + $freight;

            // Generate a unique merchant site transaction ID.
            $transactionId = rand(100000000, 999999999);
            
            $response = $gateway->authorize(array(
                'amount'                   => number_format($total,2, '.', ''),
                'currency'                 => $this->currencyName,
                'transactionId'            => $transactionId,
                'card'                     => $card,
            ))->send();
            
            $method = '';
            
            $transsactionReference = $response->getTransactionReference();
           
            if ($response->isSuccessful()) {
                
                switch ($request['number'][0]) {
                    case '3':
                        $method = 'American Express';
                        break;
                    case '5':
                        $method = 'Master Card';
                        break;
                    case '4':
                        $method = 'Visa';
                        break;
                    case '6':
                        $method = 'Discover';
                        break;
                }

                $response = $gateway->capture([
                    'amount' =>  number_format($total,2, '.', ''),
                    'currency' => $this->currencyName,
                    'transactionReference' => $transsactionReference,
                ])->send();
                //dd();
                $transsactionReference = $response->getTransactionReference();
               
                //$order = $this->createOrder($method,$sale_id,$freight);
                $order = Order::find(6810);
                if (session()->has('discount')) {
                    $discountPromo = session()->get('discount');
                    $discountRule = DiscountRule::where('discount_code',$discountPromo['promocode'])->first();
                    $discountRule->update(['is_active'=>0]);
                }
                session()->forget('customer');
                session()->forget('discount');

                $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
                $filename = $printOrder->print($order,'email'); // Print newly create proforma.
                
                $data = array(
                    'template' => 'emails.invoice',
                    'to' =>$order->email,
                    'company' => $order->s_company,
                    'order_id' => $order->id,
                    'filename'=>$filename[0],
                    'purchasedFrom' => 1,
                    'subject' => 'Swiss Made Corp.',
                    'from' => 'info@swissmadecorp.com'
                );
        
                // $gmail = new GmailCustomer($data);
                // $gmail->send();
                $gmailer = new GMailer($data);
                $gmailer->send();

                return view('payment/thankyou',['order'=>$order]);
            } else {
                session()->flash('message',$response->getMessage());
                return redirect()->action('App\Http\Controllers\CartController@Unsuccessful');
            }

            return redirect()->action('CartController@Thankyou');
        } else {
            session()->flash('message','The Credit card you entered is invalid.');
            return redirect()->action('App\Http\Controllers\CartController@Unsuccessful');
        }
    }

    public function Unsuccessful() {
        return view('payment/unsuccessful');
    }

    public function Thankyou() {
        return view('payment/thankyou');
    }

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = \App\Models\DiscountRule::where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();
        
        return $discountRule;
    }

    private function _addToCart($id,$qty) {
        $product = Product::findOrFail($id);
        
        if ($product) {
            $p_image = $product->images->toArray();
            if (!empty($p_image)) {
                if (file_exists(base_path().'/public/images/thumbs/'.$p_image[0]['location']))
                    $image='images/thumbs/'.$p_image[0]['location'];
                else $image = 'images/no-image.jpg'; 
            } else $image = 'images/no-image.jpg';

            if ($product->web_price==0) {
                $wp = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
            } else $wp = $product->web_price;

            $wire = $product->p_newprice;

            $discount = $this->discountRule();
            if ($discount) {
                if ($discount->action == 5)
                    $productDiscount=unserialize($discount->product);

                if ($discount && $discount->action == 4) {
                    $wp = ceil($wp - ($wp * ($discount->amount/100)));
                } elseif ($discount->action == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount)) {
                    $wp = ceil($wp - ($wp * ($discount->amount/100)));
                } 
            }

            $dt = $dt = Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s')); //subDays(3)->addMinutes(15));
            if (session()->has('customer')) {
                $customer = session()->get('customer');
                
                if ($customer['payment'] == 1) {
                    $temp = $wp;
                    $wp = $wire;
                    $wire = $temp;
                }
            }
            
            $qty = isset($qty) ? $qty : 1;
            $cartProducts= array(
                'id' => $product->id,
                'cost' => $product->p_price,
                'serial' => $product->p_serial,
                'sku' => $product->id,
                'qty' => $qty,
                'onhand' => $product->p_qty,
                'webprice' => $wp,
                'wireprice' => $wire,
                'iswire' => $product->wire_discount,
                'condition' => Conditions()->get($product->p_condition),
                'slug' => $product->slug,
                'product_name' => $product->title,
                'image' => $image,
                'reserve_time' => $dt,
                'reserve_for' => "Shopping Cart"
            );
            
            if (Cart::products()) {
                $cart = Cart::insert($cartProducts,$qty);
                if (session()->has('discount')) {
                    $promoCode = session()->get('discount');
                    $this->discount($promoCode['promocode'],$promoCode['original_amount'],$promoCode['action']);
                }
            } else
                $cart = Cart::add($cartProducts);


            //return $dt;
            $product->update([
                'reserve_amount' => $cartProducts['webprice'],
                "reserve_for" => 'Shopping Cart',
                "reserve_date" => $dt,
                "p_status" => '2'
            ]);

        }
    }

    public function WirePayment(Request $request) {
        $wireprice = 0;
        
        foreach (Cart::products() as $product) {
            Cart::updateItem($product['id'], "webprice", $product['wireprice']);
            Cart::UpdateItem($product['id'], "wireprice", $product['webprice']);
        }
        
        $customer= session()->get('customer');
        $customer['payment'] = $request['payment'];

        $r['tax'] = 0;
        if ($customer['b_state'] == 3956 && $customer['b_country'] == '231') {
            $tax = Taxable::where('state_id',$customer['b_state'])->value('tax');
            $r['tax'] = $tax;
        } 
        
        session()->put('customer',$customer);
        
        $applyFreeShipping = false;
        
        if (session()->has('discount')) {
            $discount = session()->get('discount');
            $now = (date('Y-m-d',strtotime(now())));
            $discountRule = DiscountRule::where('discount_code',$discount['promocode'])
                ->where('start_date','<=',$now)
                ->where('end_date','>=',$now)
                ->first();
            
                if ($discountRule) {
                    if ($discountRule->is_active) {
                        if ($discountRule['free_shipping']) {
                            $applyFreeShipping = true;   
                            $r['freight'] = 0;
                        }
                    }
            }
        }

        if (!$applyFreeShipping) {
            if ($customer['b_country'] == '231')
                $r['freight'] = $this->domestic;
            else $r['freight'] = $this->international;
        }
        
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($customer['b_country']);
        $customer['b_country']=$country_b;
        
        $html = view('carttemplate',['products' => Cart::products(),'discount' => $this->getDiscountAmount(),'tax'=>$r['tax'],'freight'=>$r['freight']])->render();
        return $html;
    }

    public function ProductTotalTimeLeft(Request $request) {
        $id = $request['id'];
        $product_cart = Cart::find($id);
        if ($product_cart)  {
            $product = Product::find($id);
            if ($product->p_status == 2) {
                $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $product_cart['reserve_time']->toDateTimeString());
                $dt2 = \Carbon\Carbon::now();
                
                $length = number_format($dt2->diffInMinutes($dt),"0"); 
                if ($length>15) $length = 0;
            } else $length = null;
            return $length;
        } else return null;
    }

    public function ReleaseHold(Request $request) {
        $id = $request['id'];
        Cart::UpdateItem($id,"reserve_for","");
        $product_cart = Cart::find($id);
        
        $product = Product::find($id);
        
        $product->update([
            'p_status' => 0,
            'reserve_for' => null,
            'reserve_amount' => null,
            'reserve_date' => null,
        ]);
    }

    public function CartUpdate(Request $request) {
        $output='';
        $keys = $request['product'];
       
        foreach ($keys as $index => $key) {
            $product_id = $request['product'][$index];
            //if (isset($request['qty'])) {
                $qty = $request['qty'][$index];
                $this->_addToCart($product_id, $qty);
            //}
        }

        return redirect('cart');
    }

    public function addToCart(Request $request) {
        $this->_addToCart($request['id'], $request['qty']);
    }

    public function cart(Request $request) {
        $cookie = null;
        \Log::info('Cart: '. getClientIP());
        if (Cookie::has('cookie_cart')) {
            $cookie_cart=Cookie::get('cookie_cart');
            $cookie = unserialize($cookie_cart);
            
            //dd($cookie);
            
            if (isset($cookie['items'])) {
                foreach ($cookie['items'] as $item) {
                    $this->_addToCart($item[0], $item[1]);
                    //$this->addToCart($item);
                }
            }
            //return view("checkout",['products' => Cart::products(),'discount' => $this->getDiscountAmount(),'cookie_cart'=>$cookie]);
            //return view('cart',['products'=>Cart::products(),'discount' => $this->getDiscountAmount()]);
        }


        $discount = $this->getDiscountAmount();
        
        $cart_products = Cart::products();
        
        return view('cart',['products'=>$cart_products,'discount' => $discount]);
    }

    public function remove(Request $request) {

        $product_id = $request['id'];
        $cart = Cart::Remove($product_id);

        $product = Product::find($product_id);
        if ($product) {
            $product->update([
                'p_status' => 0,
                'reserve_for' => null,
                'reserve_amount' => null,
                'reserve_date' => null,
            ]);
        }
        if (Cookie::has('cookie_cart')) {
            Cookie::queue(Cookie::forget('cookie_cart'));
        }

        if (session()->has('discount')) {
            $promoCode = session()->get('discount');
            $this->discount($promoCode['promocode'],$promoCode['original_amount'],$promoCode['action']);
            if ($cart == 0)
                session()->forget('discount');
        }
        
        //$this->clearAllCookies();
        return $cart;
    }

    private function saveEmailForNewsLetter($email) {
        $count = Newsletter::select('email')->where('email',$email)->count();
        if (!$count)
            Newsletter::create([
                'email' => $email,
                'subscribed' => 1
            ]);
    
    }

    public function checkoutpayment(Request $request) {

        $cookieData = $request->all();
        if (Session::has('customer')) {
            $cookieData = session()->get('customer');
        } else {
            $cookieData['payment'] = 0;
            session()->put('customer',$cookieData);
        }
        
        $email = strip_tags($request['email']);
        if ($email)
            $this->saveEmailForNewsLetter($email);

        $cookie=null;
        $items='';

        if (Cart::products()) {
            
            foreach (Cart::products() as $product) {
                $product_item = Product::find($product['id']);
                if ($product_item->p_qty > 0) {
                    $items .= $product['product_name'] . ' (' . $product['id'] . ")<br><br>" ;
                    $cookieData['items'][] = [$product['id'],$product['qty']];
                }
            }
        }
        
        if (!$items) {
            $this->clearAllCookies();
            return redirect('cart')->with('products', null);
        } else {
            if (Session::has('exchange_rate')) {
                if (session('exchange_rate')) {
                    $exchangeRate = session('exchange_rate');
                    $this->rate = $exchangeRate['rate'];
                    $this->currencyName = $exchangeRate['currency_name'];
                } 
            }

            $data = array(
                'to' => "info@swissmadecorp.com",
                'from' => "info@swissmadecorp.com",
                'subject' => 'Credit Card Attempt',
                'template' => 'emails.creditcard',
                'fullname' =>strip_tags($request['b_firstname'].' '.$request['b_lastname']),
                'purchasedFrom' => 1,
                'phone' => strip_tags($request['b_phone']),
                'customer_email' =>$email,
                'ip' => $request['ip'],
                'item' => $items,
            );

            $week_timespan = time() + 7 * 24 * 60 * 60;
            Cookie::queue('cookie_cart', serialize($cookieData), $week_timespan);
            if (isset($cookieData['items'])) {
                \App\Models\AbandonedCart::create([
                    'email' => $email,
                    'full_name' => strip_tags($request['b_firstname'].' '.$request['b_lastname']),
                    'products' => serialize($cookieData['items'])
                ]);
            }

            $gmailer = new GMailer($data);
            $gmailer->send();
            // $gmail = new GmailCustomer($data);
            // $gmail->send();

            $r['tax'] = 0;
            if ($request['b_state'] == 3956 && $request['b_country'] == '231') {
                $tax = Taxable::where('state_id',$request['b_state'])->value('tax');
                $r['tax'] = $tax;
            } 
            
            $applyFreeShipping = false;
            if (session()->has('discount')) {
                $discount = session()->get('discount');
                $now = (date('Y-m-d',strtotime(now())));
                $discountRule = DiscountRule::where('discount_code',$discount['promocode'])
                    ->where('start_date','<=',$now)
                    ->where('end_date','>=',$now)
                    ->first();
                
                    if ($discountRule) {
                        if ($discountRule->is_active) {
                            if ($discountRule['free_shipping']) {
                                $applyFreeShipping = true;   
                                $r['freight'] = 0;
                            }
                        }
                }
            }

            if (!$applyFreeShipping) {
                if ($request['b_country'] == '231')
                    $r['freight'] = $this->domestic;
                else $r['freight'] = $this->international;
            }
            
            $countries = new \App\Libs\Countries;
            $country_b = $countries->getCountry($request['b_country']);
            $request['b_country']=$country_b;

            if (Cart::products())
                return view("checkoutpayment",['products' => Cart::products(), 'discount' => $this->getDiscountAmount(), 'tax'=>$r['tax'],'freight'=>$r['freight'],'customer'=>$request->all(),'cookie_cart'=>$cookieData]);
            else return redirect('cart')->with('products', null);
        }
    }

    private function getDiscountAmount() {
        $discount = 0;

        if (session()->has('discount')) {
            $discount = session()->get('discount');
            
            if ($discount['action'] == 5 || $discount['action'] == 1)
                $discount = $discount['amount'];
            elseif ($discount['action'] == 2) {
                $discount = $discount['original_amount']/100;
                $discountSession= session()->get('discount');
                $now = (date('Y-m-d',strtotime(now())));
        
                $discountRule = DiscountRule::where('discount_code',$discountSession['promocode'])
                    ->where('start_date','<=',$now)
                    ->where('end_date','>=',$now)
                    ->get();
        
                foreach ($discountRule as $rule) {
                    if (is_array(unserialize($rule->product))) {
                        foreach (unserialize($rule->product) as $product) {
                            $products[] = $product;
                        }
                    }
                }
        
                $totalCartAmt = 0;
                foreach (Cart::products() as $product) {
                    if (in_array($product['id'],$products)) {
                        $totalCartAmt +=$product['webprice'];
                    }
                }
        
                $discount = $totalCartAmt * $discount;
        
            } else
                $discount = $discount['original_amount'];
        }

        return $discount;
    }

    public function checkout(Request $request)
    {
        //$r = $this->getProduct();
     //dd($r);
        $cookie = null;
        if (Cookie::has('cookie_cart')) {
            $cookie_cart=Cookie::get('cookie_cart');
            $cookie = unserialize($cookie_cart);
            foreach ($cookie['items'] as $item) {
                $this->_addToCart($item[0], $item[1]);
            }
                
        } else {
            $cart = Cart::products();
        }

        
        return view("checkout",['products' => Cart::products(),'discount' => $this->getDiscountAmount(),'cookie_cart'=>$cookie]);
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

    private function createOrder($method,$order_id=null,$transaction=null) {
        $countries = new \App\Libs\Countries;
        $tax = 0;$subtotal = 0;$total = 0;$orderstatus = 0;$totalWebprice=0;

        $customer= session()->get('customer');
        if (session()->has('discount'))
            $discount= session()->get('discount');
        else {
            $discount['amount']=0;
            $discount['promocode']='';
        }

        $company = strip_tags($customer['b_company']);
        if (!$company)
            $company = strip_tags($customer['b_firstname'].' '.$customer['b_lastname']);

        $orderArray = [
            'b_firstname' => '',
            'b_lastname' => '',
            'b_company' => 'Website',
            'b_address1' => '',
            'b_address2' => '',
            'b_phone' => '',
            'b_city' => '',
            'b_state' => strip_tags($customer['b_state']),
            'b_country' => strip_tags($customer['b_country']),
            'b_zip' => '',
            's_firstname' => strip_tags($customer['b_firstname']),
            's_lastname' => strip_tags($customer['b_lastname']),
            's_company' => $company,
            's_address1' => strip_tags($customer['b_address1']),
            's_address2' => strip_tags($customer['b_address2']),
            's_phone' => strip_tags($customer['b_phone']),
            's_city' => strip_tags($customer['b_city']),
            's_state' => strip_tags($customer['b_state']),
            's_country' => strip_tags($customer['b_country']),
            's_zip' => strip_tags($customer['b_zip']),
            'payment_options' => strip_tags($method),
            'method' => 'Invoice',
            'email' => strip_tags($customer['email']),
            'discount' => $discount['amount'],
            'discount_code' => $discount['promocode'],
            'purchased_from' => 0,
            'sale_id' => $order_id,
            'code' => $transaction,
            'cc_status' => "Pending Insurance",
            'status' => $method,
            'freight' => $this->cartFreight,
            'taxable' => $this->cartTax,
            'subtotal' => $this->cartSubTotal,
            'discount' => $discount['amount'],
            'total' => $this->cartTotal,
        ];

        //dd($orderArray);
        //dd($request->session()->all());
        
        $new_customer = array(
            'cgroup' => 1,
            'email' => $customer['email'],
            'firstname' => $orderArray['b_firstname'],
            'lastname' => $orderArray['b_lastname'],
            'company' => $company,
            'address1' => $orderArray['b_address1'],
            'address2' => $orderArray['b_address1'],
            'phone' => $orderArray['b_phone'],
            'country' => $customer['b_country'],
            'state' => $customer['b_state'],
            'city' => $orderArray['b_city'],
            'zip' => $orderArray['b_zip']
        );
        
        
        $customer = Customer::updateOrCreate(['email'=>$customer['email']],$new_customer);
        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);

        foreach (Cart::products() as $product) {
            $order->products()->attach($product['id'], [
                'qty' => $product['qty'],
                'price' => $product['webprice'],
                'serial' => $product['serial'],
                'product_name' => $product['product_name'],
                'cost' => $product['cost']
            ]);

            $fproduct=Product::find($product['id']);
            $fproduct->p_qty -= $product['qty'];
            $fproduct->update();
        }

        //$totalWebprice -= $discount['amount'];

        // if ($customer['b_state'] == 3956 && $customer['b_country'] == 231) {
        //     $tax = Taxable::where('state_id',$customer['b_state'])->value('tax');
        //     $total = $totalWebprice + ($totalWebprice * ($tax/100))+$freight;
        // } else
        //     $total = $totalWebprice + $freight;

        $order->update([
            'freight' => $this->cartFreight,
            'taxable' => $this->cartTax,
            'subtotal' => $this->cartSubTotal,
            'discount' => $discount['amount'],
            'total' => $this->cartTotal,
        ]);
        
        return $order;
    }
}
