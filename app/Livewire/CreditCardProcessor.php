<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Country;
use App\Models\State;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Mail\GMailer;
use Omnipay\Omnipay;
use Livewire\Attributes\Validate;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Helper;
use App\Models\Taxable;
use Hashids\Hashids;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

class CreditCardProcessor extends Component
{
    public $customer = [];
    public $selectedCountry = 231;
    public $selectedState = 0;
    public $invoiceId = 0;
    public $order;
    public $orderSum;
    public $tax;
    public $totalPrice;
    public $processItems = [];
    public $paymentResponse;
    public $status = null;

    #[Computed]
    public function countries() {
        return Country::All();
    }

    #[Computed]
    public function shippingStates() {
        return State::where('country_id',$this->selectedCountry)->get();
    }

    public function processPayment() {
        // cc trial # 4246315206263121
        $this->validate([
            'customer.cardname'   => 'required|string|min:3',
            'customer.cardnumber' => 'required',
            'customer.cardexp'    => 'required',
            'customer.cardcvc'    => 'required',
        ], [
            'customer.cardname.required' => 'The cardholder name is required.',
            'customer.cardname.min'      => 'The cardholder name must be at least 3 characters.',
            'customer.cardnumber.required' => 'The card number is required.',
            'customer.cardexp.required'    => 'The expiry date is required.',
            'customer.cardcvc.required'    => 'The CVC is required.',
        ]);

        $countries = new \App\Libs\Countries;

        $this->resetErrorBag();

        $customer = $this->customer;
        $customer['b_country'] = $this->selectedCountry;
        $customer['b_state'] = $this->selectedState;


        $country_b = $countries->getCountry($customer['b_country']);
        $state_b = $countries->getStateCodeFromCountry($customer['b_state']);

        $this->paymentResponse = $this->AuthorizeNet();
        \Log::debug($this->paymentResponse);

        $latestOrderId = $this->order->id;

        if ($this->paymentResponse[0] == "Error" || $this->paymentResponse[0] == "Declined") {
            $this->addError('paymentResponse', $this->paymentResponse[1]);
            return;
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

        $trans = 0;
        if ($this->paymentResponse[0] != "Error")
            $trans = $this->paymentResponse[0];

        $post_array = array(
            //////// Required fields //////////////
            'ApiLogin'              => 'bCbp27XH',
            'ApiKey'                => '66r6neTYbIzEUlon',
            'TransactionId'         => $trans,
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
            'ShippingCost'          => $this->order->freight,
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

        if ($this->order->email) {
            $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
            $filename = $printOrder->print($this->order,"email"); // Print newly create proforma.
        }

        $this->order->code = null;
        $this->order->sale_id = $this->order->id;
        $this->order->save();
        $this->status = "Success";

        // $this->dispatch('response', 'Payment processed successfully!');
    }

    private function AuthorizeNet() {
        $countries = new \App\Libs\Countries;

        $customer = $this->customer;

        $gateway = Omnipay::create('AuthorizeNetApi_Api');
        $gateway->setAuthName(config('authorize_net.login_id'));
        $gateway->setTransactionKey(config('authorize_net.key'));

        $fullname=explode(' ',strip_tags($customer['cardname']));
        $exp = explode('/',strip_tags($customer['cardexp']));

        $p_items=''; $items = [];

        // \Log::debug('1');
        foreach ($this->order->products as $product) {
            $p_items .= $product->pivot->product_name . ' (' . $product->id . "); " ;
            $items[] = [
                'ProductName'           => $product->id,
                'ProductDescription'    => $product->pivot->product_name,
                'ProductSellingPrice'   => $product->pivot->price,
                'ProductQty'            => $product->pivot->qty,
                'ProductCostPrice'      => $product->p_price
            ];
        }

        $customer['b_country'] = $this->selectedCountry;
        $customer['b_state'] = $this->selectedState;


        $this->processItems = $items;
        $country_b = $countries->getCountry($customer['b_country']);
        $state_b = $countries->getStateCodeFromCountry($customer['b_state']);

        if ($exp[0] < date("m") && $exp[1] <= date("y")) {
            return ["Error","Credit Card has expired"];
        }

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
            'billingState' => strip_tags($state_b),
            'billingPostcode' => strip_tags($zip),
            'billingPhone' => strip_tags($customer['b_phone']),
            'billingCountry' => $country_b,

            'shippingAddress1' => strip_tags($customer['b_address1']),
            'shippingCountry' => $country_b,
            'shippingState' => strip_tags($state_b),
            'shippingCity' => strip_tags($city),
            'shippingPostcode' => strip_tags($zip),
            'shippinggPhone' => strip_tags($customer['b_phone'])
        ));

        \Log::debug($customer);

        $number = $card->getNumber();
        $validateNumber = Helper::validateLuhn($number);

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
                $this->status = "Error";
                return [$resultCode, $response->getData()['messages']['message'][0]['text']];
            }

        } else {
            $this->status = "Error";
            return ["Error","There was an issue processing your credit card. Please verify that your billing address matches your credit card."];
        }
    }

    public function mount()
    {
        $this->invoiceId = Route::current()->parameter('id');
        $hashids = new Hashids(config('app.key'), 10); // min 10 chars for nicer URLs
        $this->order = Order::find($this->invoiceId);

        if (!$this->order['code']) {
            $this->order = null;
            return;
        }

        $decoded = $hashids->decode($this->order['code']);

        if ($this->invoiceId != $decoded[0]) {
            return;
        } else {
            $decoded = $hashids->decode($this->order['code']);

            if (empty($decoded)) {
                abort(404); // invalid hash
            }

            $this->invoiceId = $decoded[0];

            $this->order = Order::find($this->invoiceId);

            $cgroup = $this->order->customers->first()->cgroup;
            $freight = $this->order->freight;
            $this->orderSum = $this->order->products->sum('pivot.price');

            if ($cgroup == 1) {
                $this->tax = $this->order->taxable;
                $this->totalPrice = number_format(
                    $this->order->total + ($this->order->total * ($this->tax / 100)) + $freight,
                    2,
                    '.',
                    ''
                );
            } else {
                $this->tax = 0;
                $this->totalPrice = $this->order->total + $freight;
            }
        }
    }

    public function render()
    {

        return view('livewire.credit-card-processor', [
            'order' => $this->order,
            'orderSum' => $this->orderSum,
            'tax' => $this->tax,
            'totalPrice' => $this->totalPrice,
        ]);
    }
}
