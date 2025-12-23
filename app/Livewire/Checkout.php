<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\DiscountRule;
use App\Models\Taxable;
use App\Jobs\eBayEndItem;
use App\Models\Country;
use App\Models\State;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Customer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Mail\GMailer;
use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Helper;

class Checkout extends Component
{
    public $totalPages = 5;
    public $currentPage = 1;
    public $selectedCountry = 231;
    public $selectedState = 0;
    public $paymentOption = 1;
    public $isOptionPaymentTriggered = -1;
    public $freight = 79;
    public $tax = 0;
    public $totalPrice = 0;
    public $subTotalPrice = 0;
    public $discount = 0;
    public $processItems = [];

    protected $queryString = [
        'currentPage' => ['except' => 1],
    ];

    public $customer = [];

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

    public function finalizePurchase() {
        $customer= session()->get('customer');
        // cc trial # 4246315206263121
        if ($this->paymentOption == 2) {
            $latestOrderId = Str::uuid()->toString(); //Order::latest('id')->first()->id+1;
            $order = $this->createOrder('Wire Transfer',$latestOrderId);
            // $order=Order::find('9558');
        } else {
            // dd($this->paymentOption);
            // $this->getCartTotal();
            $trans = $this->AuthorizeNet($customer);

            // $trans[0] = ''; $trans[1] = 123;
            $latestOrderId = Str::uuid()->toString(); //Order::latest('id')->first()->id+1;

            if ($trans[0] == "Error" || $trans[0] == "Declined") {
                session()->flash('message',$trans[1]);
                return redirect()->action('App\Http\Controllers\CartController@Unsuccessful');
            }

            $method = ''; $methodPayment='';
            switch (substr($customer['cardnumber'], 0, 1)) {
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

            $company = ''; $b_address2 = '';
            if (!empty($customer['b_company']))
                $company = strip_tags($customer['b_company']);

            if (!empty($customer['b_address2']))
                $b_address2 = $customer['b_address2'];

            if (!$company)
                $company = strip_tags($customer['b_firstname'].' '.$customer['b_lastname']);

            $countries = new \App\Libs\Countries;
            $country_b = $countries->getCountry($customer['b_country']);
            $state_b = $countries->getStateCodeFromCountry($customer['b_state']);

            $cc = strip_tags(str_replace(" ","",$customer['cardnumber']));
            $fullname=explode(' ',strip_tags($customer['cardname']));

            $zip = ''; $city = '';
            if ($customer["b_country"]==231) {
                $zip = $customer['b_zip'];
                $city = $customer['b_city'];
            }

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
                'BillingAddress2'       => strip_tags($b_address2),
                'BillingCity'           => strip_tags($city),
                'BillingState'          => $state_b,
                'BillingZip'            => strip_tags($zip),
                'BillingCountry'        => $country_b,
                'BillingEveningPhone'   => strip_tags($customer['b_phone']),
                'BillingEmail'          => strip_tags($customer['email']),
                'IPAddress'             => $_SERVER['REMOTE_ADDR'],
                'ShippingFirstName'     => $fullname[0],
                'ShippingMiddleName'    => '',
                'ShippingLastName'      => $fullname[1],
                'ShippingCompany'       => $company,
                'ShippingAddress1'      => strip_tags($customer['b_address1']),
                'ShippingAddress2'      => strip_tags($b_address2),
                'ShippingCity'          => strip_tags($city),
                'ShippingState'         => $state_b,
                'ShippingZip'           => strip_tags($zip),
                'ShippingCountry'       => $country_b,
                'ShippingEveningPhone'  => strip_tags($customer['b_phone']),
                'ShippingEmail'         => strip_tags($customer['email']),
                'ShippingCost'          => $this->freight,
                'GrandTotal'            => str_replace(',','',$this->totalPrice),
                'CCType'                => $methodPayment,
                'CCFirst6'              => substr($cc,0,6),
                'CCLast4'               => substr($cc,-4),
                //'CIDResponse'           => $trans->cvv2_result_code,
                'AVSCode'               => 'Y',
                'LineItems'             => $this->processItems,

                /////////// Optional fields /////////////
                'SiteName'                  => 'Woocommerce',
                'ShippingMethod'            => $country_b == 'United States' ? 'FedEx Overnight' : "FedEx International Priority",
                'CCExpires'                 => str_replace(" ", "", $customer['cardexp']),
                //'CustomerComments'          => 'Please send soon',
                //'SalesRepComments'          => 'No comment',
            );


            // Convert post array into a query string
            $post_query = http_build_query($post_array);

            // Do lPOST
            $ch = curl_init('https://eye4fraud.com/api/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);
            // return;

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

        session()->put('order',$order);
        $this->currentPage = 1;

        return redirect()->route('finalize.checkout');

        // return redirect()->to('payment/thankyouorder',['order'=>$order]);

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

    private function createOrder($method, $order_id=null,$transaction=null) {
        $countries = new \App\Libs\Countries;
        $tax = 0;$subtotal = 0;$total = 0;$orderstatus = 0;$totalWebprice=0;

        $customer= session()->get('customer');

        if (session()->has('discount'))
            $discount= session()->get('discount');
        else {
            $discount['amount']=0;
            $discount['promocode']='';
        }

        $company = '';
        if (isset($customer['b_company']))
            $company = strip_tags($customer['b_company']);
            if (!$company)
                $company = strip_tags($customer['b_firstname'].' '.$customer['b_lastname']);

        $address2 = '';
        if (isset($customer['b_address2']))
            $address2 = $customer['b_address2'];

        $city = '';
        if ($customer["b_country"]==231) {
            $zip = $customer['b_zip'];
            $city = $customer['b_city'];
        } else $zip = "00000";

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
            's_address2' => strip_tags($address2),
            's_phone' => strip_tags($customer['b_phone']),
            's_city' => strip_tags($city),
            's_state' => strip_tags($customer['b_state']),
            's_country' => strip_tags($customer['b_country']),
            's_zip' => strip_tags($zip),
            'payment_options' => $method,
            'method' => 'Invoice',
            'email' => strip_tags($customer['email']),
            'discount' => $discount['amount'],
            'discount_code' => $discount['promocode'],
            'purchased_from' => 0,
            'sale_id' => $order_id,
            'code' => $transaction,
            'cc_status' => $this->paymentOption == 1 ? "Pending Insurance" : '',
            'status' => $method,
            'freight' => $this->freight,
            'taxable' => $this->tax,
            'subtotal' => $this->subTotalPrice,
            'discount' => $discount['amount'],
            'total' => str_replace(',','',$this->totalPrice),
        ];

        // dd($orderArray);
        //dd($request->session()->all());

        $new_customer = array(
            'cgroup' => 1,
            'email' => $customer['email'],
            'firstname' => $orderArray['s_firstname'],
            'lastname' => $orderArray['s_lastname'],
            'company' => $company,
            'address1' => $orderArray['s_address1'],
            'address2' => $orderArray['s_address2'],
            'phone' => $orderArray['s_phone'],
            'country' => $orderArray['s_country'],
            'state' => $orderArray['s_state'],
            'city' => $orderArray['s_city'],
            'zip' => $orderArray['s_zip']
        );

        // \Log::debug($new_customer);
        $customer = Customer::updateOrCreate(['company' => $company],$new_customer);
        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);

        $productToEnd = array();
        foreach (Cart::products() as $product) {
            $order->products()->attach($product['id'], [
                'qty' => $product['qty'],
                'price' => $product['webprice'],
                'serial' => $product['serial'],
                'product_name' => $product['product_name'],
                'cost' => $product['cost']
            ]);

            $fproduct=Product::find($product['id']);
            $productToEnd[]=$product['id'];
            $fproduct->p_qty -= $product['qty'];
            $fproduct->update();
        }

        if (count($productToEnd)>0)
            eBayEndItem::dispatch($productToEnd);

        // $order->update([
        //     'freight' => $this->freight,
        //     'taxable' => $this->tax,
        //     'subtotal' => $this->subTotalPrice,
        //     'discount' => $discount['amount'],
        //     'total' => $this->totalPrice,
        // ]);

        return $order;
    }

    private function AuthorizeNet($customer) {
        $gateway = Omnipay::create('AuthorizeNetApi_Api');
        $gateway->setAuthName(config('authorize_net.login_id'));
        $gateway->setTransactionKey(config('authorize_net.key'));

        $fullname=explode(' ',strip_tags($customer['cardname']));
        $exp = explode('/',strip_tags($customer['cardexp']));

        $p_items=''; $items = [];

        // \Log::debug('1');
        foreach (Cart::products() as $product) {
            $p_items .= $product['product_name'] . ' (' . $product['id'] . "); " ;
            $items[] = [
                'ProductName'           => $product['sku'],
                'ProductDescription'    => $product['product_name'],
                'ProductSellingPrice'   => $product['webprice'],
                'ProductQty'            => $product['qty'],
                'ProductCostPrice'      => $product['cost']
            ];
        }

        $this->processItems = $items;
        $discountRule = $this->discountRule();
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($customer['b_country']);
        // $this->getCartTotal();

        if ($exp[0] < date("m") && $exp[1] <= date("y")) {
            return ["Error","Credit Card has expired"];
        }
        // \Log::debug('2');
        $zip = ''; $city = '';
        if ($customer["b_country"]==231) {
            $zip = $customer['b_zip'];
            $city = $customer['b_city'];
        }

        $card = new CreditCard(array(
            'firstName'    => $fullname[0],
            'lastName'     => $fullname[1],
            'number'       => strip_tags($customer['cardnumber']),
            'expiryMonth'  => $exp[0],
            'expiryYear'   => $exp[1],
            'cvv'          => strip_tags($customer['cardcvc']),
            'description'  => $items,
            //'invoiceNumber'      => $latestOrderId,
            'billingAddress1' => strip_tags($customer['b_address1']),
            'billingCity' => strip_tags($city),
            'billingPostcode' => strip_tags($zip),
            'billingPhone' => strip_tags($customer['b_phone']),
            'billingCountry' => $country_b,

            'shippingAddress1' => strip_tags($customer['b_address1']),
            'shippingCountry' => $country_b,
            'shippingCity' => strip_tags($city),
            'shippingPostcode' => strip_tags($zip),
            'shippinggPhone' => strip_tags($customer['b_phone'])
        ));

        \Log::debug($customer);

        $number = $card->getNumber();
        $validateNumber = Helper::validateLuhn($number);
        // \Log::debug($response);
        if ($validateNumber) {
            // Generate a unique merchant site transaction ID.
            $transactionId = rand(100000000, 999999999);

            $response = $gateway->authorize(array(
                'amount'                   => str_replace(',','',$this->totalPrice),
                'currency'                 => 'USD',
                'transactionId'            => $transactionId,
                'card'                     => $card,
            ))->send();

            $transactionReference = $response->getTransactionReference();

            $resultCode = $response->getData()['messages']['resultCode'];
            // \Log::debug($response);
            if ($response->isSuccessful()) {
                return [$resultCode, $transactionReference];
            } else {
                // dd($response->getData());
                return [$resultCode, $response->getData()['messages']['message'][0]['text']];
            }
        } else {
            return ["Error","There was an issue processing your credit card. Please verify that your billing address matches your credit card."];
        }
    }

    public function NextStep() {
        if ($this->currentPage == 2) {// Check to see if customer info was filled in
            // dd($this->getErrorBag()->all());
            $validatedData = $this->validate([
                'customer.email' => 'required',
                'customer.b_firstname' => 'required',
                'customer.b_lastname' => 'required',
                'customer.b_address1' => 'required',
                'customer.b_phone' => 'required',
            ], [
                'customer.email.required' => 'The email field is required.',
                'customer.b_firstname.required' => 'The first name field is required.',
                'customer.b_lastname.required' => 'The last name field is required.',
                'customer.b_address1.required' => 'The address field is required.',
                'customer.b_phone.required' => 'The phone field is required.'
            ]);

            // $this->customer['b_city'] = $this->selectedCity;
            $this->customer['b_state'] = $this->selectedState;
            $this->customer['b_country'] = $this->selectedCountry;

            session()->put('customer',$this->customer);
            $this->dispatch('card-reinitialize');
        }

        if ($this->currentPage != $this->totalPages)
            $this->currentPage ++;

        if ($this->currentPage == 3) {
            $items="";

            if (Cart::products()) {

                foreach (Cart::products() as $product) {
                    $product_item = Product::find($product['id']);
                    if ($product_item->p_qty > 0) {
                        $items .= $product['product_name'] . ' (' . $product['id'] . ")<br><br>" ;
                        //$cookieData['items'][] = [$product['id'],$product['qty']];
                    }
                }
            }

            $data = array(
                'to' => "info@swissmadecorp.com",
                'from' => "info@swissmadecorp.com",
                'subject' => 'Credit Card Attempt',
                'template' => 'emails.creditcard',
                'fullname' =>strip_tags($this->customer['b_firstname'].' '.$this->customer['b_lastname']),
                'purchasedFrom' => 1,
                'phone' => strip_tags($this->customer['b_phone']),
                'customer_email' => $this->customer['email'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'item' => $items,
            );

            $gmailer = new GMailer($data);
            $gmailer->send();
        }

    }

    public function OrderConfirmation() {
        if ($this->paymentOption == 1) {
            $this->dispatch('card-reinitialize');
            $this->validate([
                'customer.cardname' => 'required|string|min:3',
                // 'customer.cardnumber' => 'required|digits:16',
                // 'customer.cardexp' => [
                //     'required',
                //     'regex:/^(0[1-9]|1[0-2]) \/ \d{2}$/'
                // ],
                // 'customer.cardcvc' => [
                //     'required',
                //     'regex:/^\d{3,4}$/'
                // ],
                // 'customer.cardcvc' => 'required|digits:3',
            ], [
                'customer.cardname.required' => 'The cardholder name is required.',
                'customer.cardname.min' => 'The cardholder name must be at least 3 characters.',
                'customer.cardnumber.required' => 'The card number is required.',
                // 'customer.cardnumber.digits' => 'The card number must be 16 digits.',
                // 'customer.cardexp.regex' => 'The expiration date must be in the format MM / YY.',
                'customer.cardexp.required' => 'The expiry date is required.',
                // 'customer.cardexp.date_format' => 'The expiry date must be in the format mm/yy.',
                'customer.cardcvc.required' => 'The CVC is required.',
                // 'customer.cardcvc.regex' => 'The CVC must be 3 or 4 digits.',
            ]);

            $this->calculateTotals();

        }
        $this->customer['paymentOption'] = $this->paymentOption;
        session()->put('customer',$this->customer);
        $this->currentPage ++;
    }

    public function PreviousStep() {
        if ($this->currentPage > 0 )
            $this->currentPage --;
    }

    #[Computed]
    public function countries() {
        return Country::All();
    }

    #[Computed]
    public function shippingStates() {
        return State::where('country_id',$this->selectedCountry)->get();
    }

    public function updated($propertyName,$value) {

        // dd($propertyName);
        if ($propertyName=='paymentOption') {
            // dd(Cart::products());
            if ($value == 0) {
                $this->isOptionPaymentTriggered = 0;
                foreach (Cart::products() as $product) {
                    Cart::updateItem($product['id'], "webprice", $product['wireprice']);
                    Cart::UpdateItem($product['id'], "wireprice", $product['webprice']);
                }
            }else {
                $this->isOptionPaymentTriggered = 1;
                foreach (Cart::products() as $product) {
                    Cart::updateItem($product['id'], "wireprice", $product['webprice']);
                    Cart::UpdateItem($product['id'], "webprice", $product['wireprice']);
                }
            }
            $this->customer['paymentOption'] = $this->paymentOption;
            $this->dispatch('card-reinitialize');
        } elseif ($propertyName=='paymentOption' && $value==0) {
            $this->customer['paymentOption'] = $this->paymentOption;
            $this->dispatch('card-reinitialize');
        } elseif ($propertyName == 'selectedState') {
            $this->selectedState = $value;
            $this->calculateTotals();
        } elseif ($propertyName == 'selectedCountry') {
            // $this->customer['freight'] = $this->freight;
            $this->isOptionPaymentTriggered = -1;
            $this->selectedCountry = $value;
            $this->calculateTotals();
        }
    }

    private function calculateTotals() {
        $tax = 0; $totalPrice = 0; $discount = 0;

        $this->discount = $this->discountRule();

        if ($this->selectedCountry == 231)
            $this->freight = 79;
        else $this->freight = 129;

        $this->customer['freight'] = $this->freight;

        if ($this->selectedState == 3956) {
            $tax = Taxable::where('state_id',$this->selectedState)->value('tax');
            $this->customer['tax'] = $tax;
            $this->tax = $tax;
        } else {
            $this->tax = 0;
            $this->customer['tax'] = null;
        }
        if (!empty($this->customer['discount'])) {
            $discount = $this->customer['discount'];
        }

        if (Cart::products()) {
            foreach (Cart::products() as $products) {
                $totalPrice+=$products['webprice']*$products['qty'];
            }

            if (is_numeric($totalPrice)) {
                $this->subTotalPrice = $totalPrice;
                $total = $totalPrice -$this->discount + $this->freight;
                $total = $total + ($total * ($tax/100));
                $this->totalPrice = number_format($total,2);
            }
        }
    }

    #[On('remove-from-checkout-page')]
    public function removeItemFromCart($product_id) {
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

        if (Cart::count() == 0)
            session()->forget('customer');
    }

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();

        return $discountRule;
    }

    public function render() {

        if (session()->has('customer') && $this->currentPage > 2) {
            $this->customer = session()->get('customer');
            $this->selectedCountry = $this->customer['b_country'];
            $this->selectedState = $this->customer['b_state'];
        }

        $totalPrice = 0;

        $this->totalPrice = $totalPrice;
        $this->calculateTotals();
        $this->dispatch('card-reinitialize');
        return view('livewire.checkout',['products' => Cart::products(), 'discount' => $this->discount, 'totalPrice' => $this->totalPrice]);
    }

}
