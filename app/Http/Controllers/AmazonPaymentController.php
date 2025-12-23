<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmazonPay\Client;
use App\Models\Taxable;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Customer;
use App\Mail\GmailCustomer; 

class AmazonPaymentController extends Controller
{
    private $domestic = 45;
    private $international = 115;
    private $sandbox = false;

    private function getAmazonConfig() {
        $amazonpay_config = array(
            'merchant_id'   => config('merchant_id'),
            'access_key'    => config('access_key'), 
            'secret_key'    => config('secret_key'), 
            'client_id'     => config('client_id'),
            'region'        => 'us',  // us, de, uk, jp
            'currency_code' => 'USD', // USD, EUR, GBP, JPY
            'sandbox'       => $this->sandbox
        ); 

        return $amazonpay_config;
    }

    public function getCartDetails(Request $request) {
        
        $amazonpay_config = $this->getAmazonConfig();

        $client = new Client($amazonpay_config);
        $requestParameters = array();
        $totalWebprice = 0; $soldItems = '';$items='';

        // Create the parameters array to set the order
        foreach (Cart::products() as $product) {
            $totalWebprice += $product['webprice'];
            $soldItems .= $product['product_name'].', ';
        }
        
        $requestParameters['amazon_order_reference_id'] = $request['orderReferenceId'];
        $response = $client->getOrderReferenceDetails($requestParameters);
        
        $customerAddress = $response->toArray()['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];
        $values = array();$tax = 0;$withTax=0;

        if ($customerAddress['CountryCode']=="US") {
            $shipping = $this->domestic;
            if ($customerAddress['StateOrRegion']=="NY") {
                $tax = Taxable::where('state_id',3956)->value('tax');
                $withTax = $totalWebprice * ($tax/100);
                $total = $totalWebprice + $withTax + $shipping;
                $values=array('$'.number_format($total,2),'$'.number_format($withTax,2),'$'.number_format($shipping,2));
                
            } else {
                $total = $totalWebprice + $shipping;
                $values=array('$'.number_format($total,2),'$0.00','$'.number_format($shipping,2));
            }
        }else{
            $shipping=$this->international;
            $total = $totalWebprice + $shipping;
            $values=array('$'.number_format($total,2),'$0.00','$'.number_format($shipping,2));
        }
        $order=Order::latest()->first();
        $requestParameters['amount']            = $total;
        $requestParameters['currency_code']     = $amazonpay_config['currency_code'];
        $requestParameters['seller_note']       = $soldItems;
        $requestParameters['seller_order_id']   = 'AMZ'.$order->id;
        $requestParameters['store_name']        = 'Swiss Made Corp';
        //$requestParameters['custom_information']= $soldItems;
        $requestParameters['mws_auth_token']    = null; // only non-null if calling API on behalf of someone else
        
        // Set the Order details by making the SetOrderReferenceDetails API call
        $response = $client->setOrderReferenceDetails($requestParameters);
        
        // If the API call was a success Get the Order Details by making the GetOrderReferenceDetails API call
        if ($client->success)
        {
            $requestParameters['access_token'] = $request['accessToken'];
            $response = $client->getOrderReferenceDetails($requestParameters);
            $customer = $response->toArray()['GetOrderReferenceDetailsResult']['OrderReferenceDetails'];
            $customerAddress = $customer['Destination']['PhysicalDestination'];

            $customerEmail = $customer['Buyer']['Email'];

            $address = array(
                'address1' => $customerAddress['AddressLine1'],
                'address2' => isset($customerAddress['AddressLine2']) ? $customerAddress['AddressLine2'] : '',
                'city' => $customerAddress['City'],
                'country' => $customerAddress['CountryCode'],
                'name' => $customerAddress['Name'],
                'phone' => isset($customerAddress['Phone']) ? $customerAddress['Phone'] : '',
                'state'=>$customerAddress['StateOrRegion'],
                'zip' => $customerAddress['PostalCode'],
                'email' => $customerEmail,
                'taxAmount' => number_format($withTax,2, '.', ''),
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => number_format($total,2, '.', ''),
                'orderReferenceId' => $request['orderReferenceId']
            );

            $this->sendEmail($address,$items);
            session()->put('customer',$address);
        }
        
        // Adding the Order Reference ID to the session so that we can use it in ConfirmAndAuthorize.php
        
        // Pretty print the Json and then echo it for the Ajax success to take in
        $json = json_decode($response->toJson());
        return array($json,$values);

    }

    private function sendEmail($address,$items) {
        $data = array(
            'to' => 'info@swissmadecorp.com',
            'fullname' =>$address['name'],
            'purchasedFrom' => 1,
            'phone' => $address['phone'],
            'customer_email' =>$address['email'],
            'template' => 'emails.creditcard',
            'subject' => 'Amazon Pay',
            'item' => $items,
        );
        
        // if ($request['email'])
        //     $this->saveEmailForNewsLetter($request['email']);

        $gmail = new GmailCustomer($data);
        $gmail->send();
    }

    public function ClearSession() {
        //if (session()->has('customer')) {
            session()->forget('customer');
            // return session()->all();
        //}
    }

    public function processAmazonPayment() {
        $amazonpay_config = $this->getAmazonConfig();
        $client = new Client($amazonpay_config);
        
        // Create the parameters array to set the order
        $customerInfo = session()->get('customer');
        
        $requestParameters = array();
        $requestParameters['amazon_order_reference_id'] = $customerInfo['orderReferenceId'];
        $requestParameters['mws_auth_token'] = null;

        // Confirm the order by making the ConfirmOrderReference API call
        $response = $client->confirmOrderReference($requestParameters);

        $responsearray['confirm'] = json_decode($response->toJson());

        //return print_r(session()->get('customer'),true);
        // If the API call was a success make the Authorize (with Capture) API call
        if ($client->success)
        {
         
            $order = $this->createOrder($customerInfo);
            //$order=Order::find($id);
            $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
            $ret = $printOrder->print($order,'email'); // Print newly create proforma.

            $requestParameters = array();
            
            // Create the parameters array to set the order
            $requestParameters['amazon_order_reference_id'] = $customerInfo['orderReferenceId'];
            $requestParameters['authorization_amount'] = $customerInfo['total'];
            $requestParameters['authorization_reference_id'] = uniqid();
            $requestParameters['seller_authorization_note'] = 'Authorizing and capturing the payment';
            $requestParameters['transaction_timeout'] = 0;
            
            // For physical goods the capture_now is recommended to be set to false
            // When set to false, you will need to make a separate Capture API call in order to get paid
            // If you are selling digital goods or plan to ship the physical good immediately, set it to true
            $requestParameters['capture_now'] = true;
            $requestParameters['soft_descriptor'] = null;
            
            $response = $client->authorize($requestParameters);
            $responsearray['authorize'] = json_decode($response->toJson());
            $responsearray['order_id'] = $order->id;
            $this->ClearSession();
            // $this->sendConfirmationEmail($customerInfo);
        }

        // Echo the Json encoded array for the Ajax success
        echo json_encode($responsearray);
    }

    public function finilizeAmazon(Request $request) {

        $id = $request['order_id'];
        $order=Order::find($id);
        if ($order)
            return view('payment/thankyou',['order'=>$order]);

        session()->flash('message','There were some issues with the Amazon Payment. Please contact us for more details.');
        return redirect()->action('CartController@Unsuccessful');
    }

    private function sendConfirmationEmail($customer) {
        $data = array(
            'to' => 'info@swissmadecorp.com',
            'fullname' =>$customer['name'],
            'purchasedFrom' => 1,
            'phone' => $customer['phone'],
            'customer_email' =>$customer['email'],
            'template' => 'emails.creditcard',
            'subject' => 'Credit Card',
            'item' => $items,
        );
        
        if ($customer['email'])
            $this->saveEmailForNewsLetter($customer['email']);

        $gmail = new GmailCustomer($data);
        $gmail->send();

    }

    private function createOrder($customer) {
        $countries = new \App\Libs\Countries;
        $tax = 0;$subtotal = 0;$total = 0;$orderstatus = 0;$totalWebprice=0;

        if (session()->has('discount'))
            $discount= session()->get('discount');
        else {
            $discount['amount']=0;
            $discount['promocode']='';
        }

        $name = explode(" ",$customer['name']);
        $freight = $customer['shipping'];
        $country = $countries->getCountryBySortName( $customer['country']);
        $state = $countries->getStateByName($customer['state'],$country);
        
        $orderArray = [
            'b_firstname' => '',
            'b_lastname' => '',
            'b_company' => 'Amazon',
            'b_address1' => '',
            'b_address2' => '',
            'b_phone' => '',
            'b_city' => '',
            'b_state' => $state,
            'b_country' => $country,
            'b_zip' => '',
            's_firstname' => $name[0],
            's_lastname' => $name[1],
            's_company' => $customer['name'],
            's_address1' => $customer['address1'],
            's_address2' => $customer['address2'],
            's_phone' => $customer['phone'],
            's_city' => $customer['city'],
            's_state' => $state,
            's_country' => $country,
            's_zip' => $customer['zip'],
            'payment_options' => "Amazon Pay",
            'method' => 'Invoice',
            'email' => $customer['email'],
            // 'discount' => $discount['amount'],
            // 'discount_code' => $discount['promocode'],
            'purchased_from' => 1,
            'sale_id' => $customer['orderReferenceId'],
            'status' => 0
        ];

        //dd($orderArray);
        //dd($request->session()->all());
            
        $new_customer = array(
            'cgroup' => 1,
            'firstname' => $name[0],
            'lastname' => $name[1],
            'company' => $customer['name'],
            'address1' => $customer['address1'],
            'address2' => $customer['address2'],
            'phone' => $customer['phone'],
            'country' => $country,
            'state' => $state,
            'city' => $customer['city'],
            'zip' => $customer['zip']
        );
        
        $customer = Customer::updateOrCreate(['email'=>$customer['email']],$new_customer);
        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);

        foreach (Cart::products() as $product) {
            $order->products()->attach($product['id'], [
                'qty' => 1,
                'price' => $product['webprice'],
                'serial' => $product['serial'],
                'product_name' => $product['product_name'],
                'cost' => $product['cost']
            ]);

            Product::find($product['id'])->decrement('p_qty');
            $totalWebprice += $product['webprice'];

        }

        $order->update([
            'freight' => $freight,
            'taxable' => $tax,
            'subtotal' => $totalWebprice,
            'discount' => $discount['amount'],
            'total' => $customer['total'],
        ]);
        
        return $order;
    }
}
