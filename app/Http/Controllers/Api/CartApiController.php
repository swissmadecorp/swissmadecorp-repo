<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\GMailer; 
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class CartApiController extends Controller
{
    private $cartItems = [];
    private $error = '';
    private $po = '';
    private $method = '';
    private $products = array();
    private $unitPrice = 0;
    private $itemId = '';
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Create a temporary order id
        //\Log::debug($request);dd('');
        $this->po = Str::uuid()->toString();
        $this->total = number_format($request['total'],2, '.', '');

        // run apple pay
        $trans=$this->ApplePay($request);
        
        // if apple pay passes run eye4fraud and finalize the order
        if ($this->error !="Error") {
            $ret = $this->Eye4Fraud($request,$trans);
            
            $this->createOrder($request);
        }
        
    }

    private function ApplePay($request) {
        $appleToken = base64_encode($request['json']);

        //$this->itemId = $item['itemId'];
        // $cart = array (
        //     'state' => 'NY',
        //     'lastName' => 'Kurayev',
        //     'firstName' => 'Edward',
        //     'phone' => '7186147678',
        //     'postcode' => '10036',
        //     'paymentMethod' => 'AmEx 3003',
        //     'tax' => 4286.625,
        //     'shipping' => 55,
        //     'street' => '15 W 47th St
        //   Ste 503',
        //     'unitPrice' => 48000,
        //     'total' => 52641.625,
        //     'item' => '[{"quantity":"1","itemId":"10571","unitPrice":"3800.00","description":"Cartier Ballon Bleu 28mm W69010Z4 Stainless Steel Women\'s Watch"},{"quantity":"1","itemId":"10570","unitPrice":"5500.00","description":"Cartier Ballon Bleu 42mm W69012Z4 Stainless Steel Unisex Watch"},{"quantity":"1","itemId":"10569","unitPrice":"39000.00","description":"Rolex Daytona 40mm 116500LN Stainless Steel Men\'s Watch"}]',
        //     'json' => '{"data":"r7sdakXJMTIlUgq5pSXZJGpWsvvKyq44rT+GIBxHYr7ecMGNh2CQUN66Q01DToA0hDvUmCK\\/ZRabHYSv4zHFd1jVMRBd7MmPrQIW2\\/23QVPh05JeSp8Hw6rbf6ZRFS25a0zwyb8HGJ3n2DavKd3ihsS7VSWV1E68zVdHH7rBFY9nHPbZphaJkoDFf696br78X9LWkuuU2hbxzsTxt0C13G7h2yyBE0di3xJqWJMDwUYSkGu4JKFQcjRYPr2OBBdlCpPgdHEpg39p+59JKXYz7K8\\/dg2KYZnSbuJdUoAd2GUGMARGe7fUqpH5MK74bX1dUTSZZulYs3eIJOavJRyKUfv\\/xkInGZavnGmFYu3KxnjwYjTRQBhecuCvg1faw5ahfJZ9UYKCkzv1lKt5jlYe","signature":"MIAGCSqGSIb3DQEHAqCAMIACAQExDzANBglghkgBZQMEAgEFADCABgkqhkiG9w0BBwEAAKCAMIID4zCCA4igAwIBAgIITDBBSVGdVDYwCgYIKoZIzj0EAwIwejEuMCwGA1UEAwwlQXBwbGUgQXBwbGljYXRpb24gSW50ZWdyYXRpb24gQ0EgLSBHMzEmMCQGA1UECwwdQXBwbGUgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMB4XDTE5MDUxODAxMzI1N1oXDTI0MDUxNjAxMzI1N1owXzElMCMGA1UEAwwcZWNjLXNtcC1icm9rZXItc2lnbl9VQzQtUFJPRDEUMBIGA1UECwwLaU9TIFN5c3RlbXMxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEwhV37evWx7Ihj2jdcJChIY3HsL1vLCg9hGCV2Ur0pUEbg0IO2BHzQH6DMx8cVMP36zIg1rrV1O\\/0komJPnwPE6OCAhEwggINMAwGA1UdEwEB\\/wQCMAAwHwYDVR0jBBgwFoAUI\\/JJxE+T5O8n5sT2KGw\\/orv9LkswRQYIKwYBBQUHAQEEOTA3MDUGCCsGAQUFBzABhilodHRwOi8vb2NzcC5hcHBsZS5jb20vb2NzcDA0LWFwcGxlYWljYTMwMjCCAR0GA1UdIASCARQwggEQMIIBDAYJKoZIhvdjZAUBMIH+MIHDBggrBgEFBQcCAjCBtgyBs1JlbGlhbmNlIG9uIHRoaXMgY2VydGlmaWNhdGUgYnkgYW55IHBhcnR5IGFzc3VtZXMgYWNjZXB0YW5jZSBvZiB0aGUgdGhlbiBhcHBsaWNhYmxlIHN0YW5kYXJkIHRlcm1zIGFuZCBjb25kaXRpb25zIG9mIHVzZSwgY2VydGlmaWNhdGUgcG9saWN5IGFuZCBjZXJ0aWZpY2F0aW9uIHByYWN0aWNlIHN0YXRlbWVudHMuMDYGCCsGAQUFBwIBFipodHRwOi8vd3d3LmFwcGxlLmNvbS9jZXJ0aWZpY2F0ZWF1dGhvcml0eS8wNAYDVR0fBC0wKzApoCegJYYjaHR0cDovL2NybC5hcHBsZS5jb20vYXBwbGVhaWNhMy5jcmwwHQYDVR0OBBYEFJRX22\\/VdIGGiYl2L35XhQfnm1gkMA4GA1UdDwEB\\/wQEAwIHgDAPBgkqhkiG92NkBh0EAgUAMAoGCCqGSM49BAMCA0kAMEYCIQC+CVcf5x4ec1tV5a+stMcv60RfMBhSIsclEAK2Hr1vVQIhANGLNQpd1t1usXRgNbEess6Hz6Pmr2y9g4CJDcgs3apjMIIC7jCCAnWgAwIBAgIISW0vvzqY2pcwCgYIKoZIzj0EAwIwZzEbMBkGA1UEAwwSQXBwbGUgUm9vdCBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwHhcNMTQwNTA2MjM0NjMwWhcNMjkwNTA2MjM0NjMwWjB6MS4wLAYDVQQDDCVBcHBsZSBBcHBsaWNhdGlvbiBJbnRlZ3JhdGlvbiBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATwFxGEGddkhdUaXiWBB3bogKLv3nuuTeCN\\/EuT4TNW1WZbNa4i0Jd2DSJOe7oI\\/XYXzojLdrtmcL7I6CmE\\/1RFo4H3MIH0MEYGCCsGAQUFBwEBBDowODA2BggrBgEFBQcwAYYqaHR0cDovL29jc3AuYXBwbGUuY29tL29jc3AwNC1hcHBsZXJvb3RjYWczMB0GA1UdDgQWBBQj8knET5Pk7yfmxPYobD+iu\\/0uSzAPBgNVHRMBAf8EBTADAQH\\/MB8GA1UdIwQYMBaAFLuw3qFYM4iapIqZ3r6966\\/ayySrMDcGA1UdHwQwMC4wLKAqoCiGJmh0dHA6Ly9jcmwuYXBwbGUuY29tL2FwcGxlcm9vdGNhZzMuY3JsMA4GA1UdDwEB\\/wQEAwIBBjAQBgoqhkiG92NkBgIOBAIFADAKBggqhkjOPQQDAgNnADBkAjA6z3KDURaZsYb7NcNWymK\\/9Bft2Q91TaKOvvGcgV5Ct4n4mPebWZ+Y1UENj53pwv4CMDIt1UQhsKMFd2xd8zg7kGf9F3wsIW2WT8ZyaYISb1T4en0bmcubCYkhYQaZDwmSHQAAMYIBjDCCAYgCAQEwgYYwejEuMCwGA1UEAwwlQXBwbGUgQXBwbGljYXRpb24gSW50ZWdyYXRpb24gQ0EgLSBHMzEmMCQGA1UECwwdQXBwbGUgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTAghMMEFJUZ1UNjANBglghkgBZQMEAgEFAKCBlTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0yMjA2MDgxNjAxMzdaMCoGCSqGSIb3DQEJNDEdMBswDQYJYIZIAWUDBAIBBQChCgYIKoZIzj0EAwIwLwYJKoZIhvcNAQkEMSIEIH1j3NywCZtOF2RrIsOb9tqWOQmzvSj2V48tw06JpLuJMAoGCCqGSM49BAMCBEcwRQIgLiOD5lUDLhW4tNoP5W8z+Cq2cJFLnZBg7B2Xpr5HYlkCIQD28auqrFOOSsspWeAxN4xYvQwMTDPr4xjnsv+alA9RYAAAAAAAAA==","header":{"publicKeyHash":"4iH3Ikk0szJIy35s84c0piANOgQg9DWGc4NB3uM7qjU=","ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE3XJimi6PJnUmo1psQkV6BTconMQCpW0i\\/psUclutQ995KxEOsvykNmAjKc2BA5A9371B97Jg\\/\\/f6eqb3qnx1iw==","transactionId":"f2a12d53fa01e1139438223f20091e5c3a00dceed44a82819729c37f1ace2de3"},"version":"EC_v1"}',
        //     'city' => 'New York',
        //     'country' => 'US',
        //     'email' => 'edba34@gmail.com',
        // );  

        $cart = $request;
        $items = json_decode($cart['item'],true);

        $this->unitPrice = $cart['unitPrice'];
        
        foreach ($items as $item) {
            $lineNumber[] = [
                "itemId" => $item['itemId'],
                "name" => "Watch",
                "description" => $item['description'],
                "quantity" => $item['quantity'],
                "unitPrice" => $item['unitPrice'],
            ];

            $this->products[] = [
                'ProductName'           => $item['itemId'],
                'ProductDescription'    => $item['description'],
                'ProductSellingPrice'   => $item['unitPrice'],
                'ProductQty'            => $item['quantity'],
                'ProductCostPrice'      => $item['unitPrice']
            ];
        }

        $lineNumbers = ["lineItem" => $lineNumber];

        $address = [
            "firstName" => $request['firstName'],
            "lastName" => $request['lastName'],
            "company" => $request['firstName']. ' ' .$request['lastName'],
            "address" => $request['street'],
            "city" => $request['city'],
            "state" => $request['state'],
            "zip" => $request['postcode'],
            "country" => $request['country']
        ];

        $transactionPost = ["createTransactionRequest" => [
            "merchantAuthentication" => [
                "name" => config('authorize_net.login_id'), // "5n9hMPv4rW4",
                "transactionKey" => config('authorize_net.key') //"72T78dAMx6C52jpV"
            ],
            "transactionRequest" => [
                "transactionType" => "authCaptureTransaction",
                "amount" => $this->total,
                "payment" => [
                    "opaqueData" => [
                        "dataDescriptor" => "COMMON.APPLE.INAPP.PAYMENT",
                        "dataValue" => "$appleToken"
                    ]
                ],
                "lineItems" => $lineNumbers,
                "tax" => [
                    "amount" => number_format($request['tax'],2,'.',''),
                    "name" => "Tax",
                    "description" => "Sales Tax"
                ],
                "shipping" => [
                    "amount" => $request['shipping'],
                    "name" => "Shipping",
                    "description" => "FedEx Shipping"
                ],
                "poNumber" => $this->po,
                "billTo" => $address,
                "shipTo" => $address,
                ]
            ]
        ];
        //\Log::debug($transactionPost);
        $post_query = json_encode($transactionPost);
        
        // Do the POST
        $ch = curl_init('https://api.authorize.net/xml/v1/request.api');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $decode = json_decode(substr($response,3),true);
        \Log::debug("Apple Pay");
        \Log::debug ($decode);
        
        $this->error = $decode['messages']['resultCode'];

        return $decode;
    }

    private function Eye4Fraud($request,$trans) {
        $method = ''; $method=explode(' ',$request['paymentMethod']);

        //[0]['text'])
        switch ($method[0]) {
            case 'AmEx':
                $this->method = 'American Express';
                $paymentMethod = "AMEX";
                break;
            case 'MasterCard':
                $this->method = 'Master Card';
                $paymentMethod = "MC";
                break;
            case 'Visa':
                $this->method = 'Visa';
                $paymentMethod = "VISA";
                break;
            case 'Discover':
                $this->method = 'Discover';
                $paymentMethod = "DISC";
                break;
            default:
                $this->method = "Other";
                $paymentMethod = "OTHER";
        }

        $company = $request['firstName'] . " " . $request['lastName'];
        
        $transId = ''; $avsResultCode = '';

        if (isset($trans['transactionResponse']['transId'])) 
            $transId = $trans['transactionResponse']['transId'];

        if (isset($trans['transactionResponse']['avsResultCode']))
            $avsResultCode = $trans['transactionResponse']['avsResultCode'];

        $post_array = array(
            //////// Required fields //////////////
            'ApiLogin'              => 'bCbp27XH',
            'ApiKey'                => '66r6neTYbIzEUlon',
            'TransactionId'         => $transId,
            'OrderDate'             => date("Y-m-d G:i:s"),
            'OrderNumber'           => $this->po,
            'BillingFirstName'      => $request['firstName'],
            'BillingMiddleName'     => '',
            'BillingLastName'       => $request['lastName'],
            'BillingCompany'        => $company,
            'BillingAddress1'       => $request['street'],
            'BillingAddress2'       => "",
            'BillingCity'           => $request['city'],
            'BillingState'          => $request['state'],
            'BillingZip'            => $request['postcode'],
            'BillingCountry'        => $request['country'],
            'BillingEveningPhone'   => $request['phone'],
            'BillingEmail'          => $request['email'],
            'IPAddress'             => $_SERVER['REMOTE_ADDR'],
            'ShippingFirstName'     => $request['firstName'],
            'ShippingMiddleName'    => '',
            'ShippingLastName'      => $request['lastName'],
            'ShippingCompany'       => $company,
            'ShippingAddress1'      => $request['street'],
            'ShippingAddress2'      => "",
            'ShippingCity'          => $request['city'],
            'ShippingState'         => $request['state'],
            'ShippingZip'           => $request['postcode'],
            'ShippingCountry'       => $request['country'],
            'ShippingEveningPhone'  => $request['phone'],
            'ShippingEmail'         => $request['email'],
            'ShippingCost'          => $request['shipping'],
            'GrandTotal'            => $this->total,
            'CCType'                => $paymentMethod,
            'CCFirst6'              => "", // Since there is no full credit card, leave this one blank
            'CCLast4'               => $method[1],
            //'CIDResponse'           => $trans->cvv2_result_code,
            'AVSCode'               => $avsResultCode,
            'LineItems'             => $this->products,

            /////////// Optional fields /////////////
            'SiteName'                  => 'Woocommerce',
            'ShippingMethod'            => $request['country'] == 'US' ? 'FedEx Overnight' : "FedEx International Priority",
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
    }

    public function sendResponse($result, $message)
    {
    	$response = [
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }


    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //return preg_split('/\r\n|\r|\n/', "15 W 47th St");
    }

    public function remove(Request $request) {
        // \Session::forget('product_'.$request['id']);

        //return \Session::get('product_'.$request['id']);
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

    private function createOrder($request) {
        $countries = new \App\Libs\Countries;

        $country = $countries->getCountryBySortName($request['country']);
        $state = $countries->getStateByCode($request['state']);
        
        $tax = 0;$subtotal = 0;$total = 0;$orderstatus = 0;$totalWebprice=0;
        $company = $request['firstName'].' '.$request['lastName'];

        $address = preg_split('/\r\n|\r|\n/', $request['street']);
        if (count($address)==1) $address[] = "";

        $freight = number_format($request['shipping'],2, '.', '');
        $tax = number_format($request['tax'],2, '.', '');
        
        $orderArray = [
            'b_firstname' => '',
            'b_lastname' => '',
            'b_company' => 'Website',
            'b_address1' => '',
            'b_address2' => '',
            'b_phone' => '',
            'b_city' => '',
            'b_state' => $state,
            'b_country' => $country,
            'b_zip' => '',
            's_firstname' => $request['firstName'],
            's_lastname' => $request['lastName'],
            's_company' => $company,
            's_address1' => $address[0],
            's_address2' => $address[1],
            's_phone' => $request['phone'],
            's_city' => $request['city'],
            's_state' => $state,
            's_country' => $country,
            's_zip' => $request['postcode'],
            'payment_options' => $this->method,
            'method' => 'Invoice',
            'email' => $request['email'],
            'purchased_from' => 1,
            'status' => 0,
            'freight' => $freight,
            'taxable' => $tax,
            'subtotal' => $this->unitPrice,
            'total' => $this->total,
        ];

        $new_customer = array(
            'cgroup' => 1,
            'firstname' => $request['firstName'],
            'lastname' => $request['lastName'],
            'company' => $company,
            'address1' => $request['street'],
            'address2' => '',
            'phone' => $request['phone'],
            'country' => $country,
            'state' => $state,
            'city' => $request['city'],
            'zip' => $request['postcode']
        );
        
        $customer = Customer::updateOrCreate(['company'=>$company],$new_customer);
        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);
        $subTotal = 0;

        foreach ($this->products as $item) {
            $product = Product::find($item['ProductName']);
            $order->products()->attach($product->id, [
                'qty' => $item['ProductQty'],
                'price' => $item['ProductSellingPrice'],
                'serial' => $product->serial,
                'product_name' => $product->title,
                'cost' => $product->p_price
            ]);
    
            $subTotal += $item['ProductSellingPrice'];
            $product->decrement('p_qty');
        }

        $order->update([
            'freight' => $freight,
            'taxable' => $tax,
            'subtotal' => $subTotal,
            'discount' => 0,
            'total' => $this->total,
        ]);
        
        if ($order->email) {    
            $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
            $filename = $printOrder->print($order,"email"); // Print newly create proforma.
        }

        return $order;
    }
}
