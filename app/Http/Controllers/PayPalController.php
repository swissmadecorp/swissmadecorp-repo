<?php

namespace App\Http\Controllers;

use PDF;
use Input;
use PayPal;
use Session;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\TheShow;
use App\Models\Taxable;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
use App\Models\EbayListing;
use App\Models\AmazonListings;
use App\Mail\GmailCustomer; 
use Illuminate\Http\Request;
use App\Jobs\RevokeWalmartProduct;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Mail;
use App\Jobs\AmazonSubmitProductQueue;
use App\Http\Controllers\EbayController;

class PayPalController extends Controller
{
    protected $displayPDF = true;
    private $domestic = 45;
    private $international = 115;
    private $rate = '1';
    private $currencyName = 'USD';

    /**
     * Retrieve IPN Response From PayPal
     *
     * @param \Illuminate\Http\Request $request
     */
    public function postNotify(Request $request)
    {
        \Log::debug($request);
        //\Log::debug('pappal postNotify');
        // Import the namespace Srmklive\PayPal\Services\ExpressCheckout first in your controller.
        // $provider = PayPal::setProvider('express_checkout');
        
        // $request->merge(['cmd' => '_notify-validate']);
        // $post = $request->all();
        
        // $response = (string) $provider->verifyIPN($post);
        
        // if ($response === 'VERIFIED') {
            // Your code goes here ...
        //     \Log::debug($response);
        //     \Log::debug(1);
        //     \Log::debug($post);
        // }
        
        //return 'asfd';
    } 

    public function CancelPage(Request $request) {
        return view('payment/cancel');
    }

    public function SuccessPage(Request $request) {
        
        $provider = PayPal::setProvider('express_checkout');
        $token = $request['token'];

        $response = $provider->getExpressCheckoutDetails($token);
        //die('<br>There has been an error in the payment process. Please contact us directly at 212-840-8463.<br>We apologize for any inconvenience.');
        

        if ($response['ADDRESSSTATUS'] == 'Confirmed') {
            $total = 0; $subtotal = 0;

            $data = array(
                'item' => $response['L_PAYMENTREQUEST_0_NAME0'],
                'order_from' => 'PayPal',
                'from'=>'info@swissmadecorp.com',
                'customer_email' =>$response['EMAIL'],
                'fullname' =>$response['SHIPTONAME'],
                'phone' => $response['PHONENUM'],
                'is_confirmed' => $response['ADDRESSSTATUS'],
                'form' => 'emails.paypal'
            );
            
            if ($response['SHIPTOCOUNTRYCODE'] != 'US') {
                $response['SHIPPINGAMT'] = $this->international;
                $response['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->international;
            } else {
                $response['SHIPPINGAMT'] = $this->domestic;
                $response['PAYMENTREQUEST_0_SHIPPINGAMT'] = $this->domestic;
            }
    
            $subtotal = $response['PAYMENTREQUEST_0_AMT'];
            
            if ($response['SHIPTOSTATE'] == 'NY') {
                $response['PAYMENTREQUEST_0_TAXAMT'] = round(($response['PAYMENTREQUEST_0_AMT'] * .08875),2);
                $response['PAYMENTREQUEST_0_AMT'] = $response['PAYMENTREQUEST_0_AMT']+$response['PAYMENTREQUEST_0_TAXAMT']+$response['SHIPPINGAMT'];
                $response['TAXAMT'] = $response['PAYMENTREQUEST_0_TAXAMT'];
            } else {
                
                $response['PAYMENTREQUEST_0_AMT'] = $response['SHIPPINGAMT'] + $response['PAYMENTREQUEST_0_AMT'];
                $response['AMT'] = $response['PAYMENTREQUEST_0_AMT'];
            }
            
            //dd($response);
            //Mail::to('info@swissmadecorp.com')->queue(new EmailConfirm($data));
            //\Log::debug(2);
            //\Log::debug($response);

            return view('payment.success',['response' => $response, 'nofilters' => false,'fullpage'=>true]);
        } else {
            return view('payment.success',['response' => ['error'=>'unconfirmed','SHIPTONAME'=>$response['SHIPTONAME']], 'nofilters' => false,'fullpage'=>true]);
        }
    }

    public function Thankyou() {
        $order = Session::get('order');
        return view('payment.thankyou',['order'=>$order,'fullpage'=>true]);
    }

    public function Checkout(Request $request) {
        $i=0;
        $data['items'] = [];
        $webprice=0;
        $total = 0;

        $provider = PayPal::setProvider('express_checkout');
        $r_data = $provider->getExpressCheckoutDetails($request['token']);
        
        $token = $r_data['TOKEN'];
        $payerId = $r_data['PAYERID'];

        if (Session::has('discount'))
            $discount= Session::get('discount');
        else $discount = 0;
        
        if (Cart::products()) {
            foreach (Cart::products() as $product) {
                $newdata = [
                    'name' =>  $product['condition'] . ' '. $product['product_name'] . " ({$product['id']})",
                    'sku' => $product['id'],
                    'price' => $product['webprice'],
                    'qty' => 1,
                ];
                array_push($data['items'],$newdata);
                $webprice += $product['webprice'];
            }
        } else {
            session()->flash('message','It looks like your cart is empty or there was as error in the system. Please remove any items from the cart and try again.');
            return redirect()->action('CartController@cart');   
        }

        if ($discount['amount']) {
            $newdata = [
                'name' =>  "Discount",
                'sku' => $discount['promocode'],
                'price' => -round($discount['amount']),
                'qty' => 1,
            ];
            array_push($data['items'],$newdata);
            $webprice += -round($discount['amount']);
        }
            
        if ($r_data['SHIPTOCOUNTRYCODE'] != 'US') {
            $data['shipping'] = $this->international;
        } else {
            $data['shipping'] = $this->domestic;
        }

        $data['tax'] = 0;
        if ($r_data['SHIPTOSTATE'] == 'NY') {
            $data['tax'] = round(($webprice * .08875),2);
            $total = round($webprice+$data['tax'],2);
            
            //$r_data['TAXAMT'] = $total;
        } else {
            $total = $webprice;
        }

        $data['total'] = $data['shipping']+$total;
        $data['subtotal'] = $webprice;
        $data['invoice_description'] = '';

        $invoice = $this->createOrderFromPayPal($data,$r_data);
        $data['invoice_id'] = $invoice->id;
        //$data['invoice_id'] = 3148;
        
        //$invoice = Order::find(3197);
        $provider = PayPal::setProvider('express_checkout');      // To use express checkout(used by default).

        $response = $provider->doExpressCheckoutPayment($data, $token, $payerId);
        //dd($response);
        // if ($response['L_LONGMESSAGE0'] == "This transaction couldn't be completed. Please redirect your customer to PayPal.")
        //     return redirect('http://www.paypal.com');

        $this->print($invoice->id,$r_data['EMAIL']);

        Session::put('order', $invoice);
        return redirect('payment/thankyou');
    }

    public function print($id,$email) {
        $order=Order::find($id);
       
        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $orderStatus='';
        $purchasedFrom = $order->purchased_from;

        PDF::setHeaderCallback(function($pdf) use($purchasedFrom){
            // Logo
            $pdf->SetFont('helvetica', 'T', 10);
            if ($purchasedFrom==1) {
                $image_file = 'public/images/logo.jpg';
                $pdf->Image($image_file, 14, 10, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            } else {
                $pdf->SetY(17);
                $pdf->SetFont('helvetica', 'T', 13);
                $pdf->WriteHTML("<b><i>SIGNATURE TIME</i></b>", false, false, false, false, 'L');
            }
        });

        PDF::setFooterCallback(function($pdf) use ($orderStatus){
            $pdf->SetFont('helvetica', 'I', 8);

            // $pdf->Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
            // $pdf->WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');

                // Page number
            $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            
        });
        
        $pdf::SetFont('helvetica', '', 10, '', true);
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
        $pdf::SetAutoPageBreak(TRUE, 32);

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
        $orderStatus = '';
        
        if ($order->method == 'On Memo'){
            $orderStatus = "Memo";
        } else $orderStatus = "Invoice";

        $pdf::setXY($pdf::getPageWidth()-55,20);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold"><?= $orderStatus?></div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $orderStatus . " No: " . $order->id ?></td>
            </tr>            
        </table>
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 

        $pdf::SetFont('helvetica', '', 10);
        $pdf::setY(25);
        $email = 'info@swissmadecorp.com';
        if ($purchasedFrom==2)
            $email = 'signtimeny@gmail.com';

        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>212-840-8463<br>$email", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();
        $countries = new \App\Libs\Countries;
        $country_b = $countries->getCountry($order->b_country);
        $country_s = $countries->getCountry($order->s_country);
        $state_b = $countries->getStateCodeFromCountry($order->b_state);
        $state_s = $countries->getStateCodeFromCountry($order->s_state);
        $method='';
        
        if ($order->status==1) {
            $payment = "Paid";
            foreach ($order->payments as $payments) 
                $method .= $payments->ref.'<br>';

            $method = substr($method,0,strlen($method)-4);
        } else {
            $payment = ($orderStatus=='Memo') ? 'Memo' : PaymentsOptions()->get($order->payment_options);
            $method = $order->method;
        }

        ob_start();
        ?>
            <table cellpadding="1" nobr="true">
            <tr>
                <td style="width: 43%;background-color:#111;color:#fff">
                    <b>To</b>:
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;background-color:#111;color:#fff">
                    <b>Ship To</b>:
                </td>                    
            </tr>
            <tr>
            <td style="width: 43%;">
                    <?php $b_fullname = $order->b_firstname . ' ' . $order->b_lastname ?>
                    <?= $b_fullname ?><br>
                    <?= !empty($order->b_company) && $b_fullname != $order->b_company? $order->b_company . '<br>' : '' ?>
                    <?= !empty($order->b_address1) ? $order->b_address1 .'<br>' : ''?>
                    <?= !empty($order->b_address2) ? $order->b_address2 .'<br>' : '' ?>
                    <?= !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': '' ?>
                    <?= !empty($country_b) ? $country_b.'<br>' : '' ?>
                    <?= !empty($order->b_phone) ? $order->b_phone . '<br>' : '' ?>
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;">
                    <?php $s_fullname = $order->s_firstname . ' ' . $order->s_lastname ?>
                    <?= $s_fullname ?><br>
                    <?= !empty($order->s_company) && $s_fullname != $order->s_company ? $order->s_company . '<br>' : '' ?>
                    <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                    <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                    <?= !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': '' ?>
                    <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                    <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                </td>
            </tr>
        </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> #</th>
                        <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> Date</th>
                        <th style="border: 1px solid #ddd;color:#fff">Payment Method</th>
                        <th style="border: 1px solid #ddd;color:#fff">Terms</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= ($order->po) ? strtoupper($order->po) : $order->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                        <td style="border: 1px solid #ddd"><?= $method ?></td>
                        <td style="border: 1px solid #ddd"><?= $payment ?></td>
                    </tr>
                </tbody>
            </table>

            <?php 
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
                ob_start();
            ?>

            <table cellpadding="4" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                        <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                        <th width="210" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <th width="75" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                        <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                        <th width="81" style="border: 1px solid #ddd;color:#fff">Retail</th>
                        <th width="81" style="border: 1px solid #ddd;color:#fff">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->products as $product) {
                        $p_image = $product->images->toArray();
                        if (!empty($p_image)) {
                            if (file_exists(base_path().'/images/thumbs/'.$p_image[0]['location']))
                                $image='images/thumbs/'.$p_image[0]['location'];
                            else $image = 'images/no-image.jpg'; 
                        } else $image = 'images/no-image.jpg';
                        
                        ?>
                    <tr nobr="true">
                        <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                            
                                <img style="height: 50px" src="<?= $image ?>" />
                            
                        </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="50"><?= ($product->p_status==4 ? '' : $product->id==1) ? 'Misc.' : $product->id ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="210"><?= (!$product->pivot->product_name) ? $product->title : $product->pivot->product_name ?> </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="75"><?= ($product->p_status==4 || $product->id==1) ? '' : $product->p_serial ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="50"><?= $product->pivot->qty ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right" width="81" ><?= ($product->p_status==4) ? '' : number_format($product->p_retail,2)?></td>
                        <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="81"><?= number_format($product->pivot->price*$product->pivot->qty,2)?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right" colspan="6"><b>Sub Total</b></td>
                        <td style="text-align: right"><?= number_format($order->subtotal,2)?></td>
                    </tr>
                    <?php if ( $order->discount>0 ) {?>
                    <tr>
                        <td style="text-align: right" colspan="6"><b>Discount</b></td>
                        <td style="text-align: right;color:red">(<?= number_format($order->discount,2)?>)</td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <?php if ( $order->customers()->first()->cgroup==0) {?>
                            <td style="text-align: right" colspan="6"><b>Freight</b></td>
                            <td style="text-align: right"><?=  number_format($order->freight,2)?></td>
                        <?php } else {?>
                            <td style="text-align: right" colspan="6"><b>Tax</b></td>
                            <td style="text-align: right"><?=  number_format($order->subtotal*($order->taxable/100),2)?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right" colspan="6"><b>Freight</b></td>
                            <td style="text-align: right"><?=  number_format($order->freight,2)?></td>                            
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="text-align: right" colspan="6"><b>Grand Total</b></td>
                        <td style="text-align: right">$<?= number_format($order->total,2)?></td>
                    </tr>           
                </tfoot>
            </table>
                        
        <?php
        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        $pdf::Ln();
        //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);
        $pdf::Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
        $pdf::WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');

        //Close and output PDF document
        $filename = 'invoice.pdf';

        // $data = array(
        //     'company' => $order->b_company,
        //     'order_id' => $order->id,
        //     'from'=>'info@swissmadecorp.com',
        //     'filename'=>$filename,
        //     'purchased_from' => $purchasedFrom
        // );

        PDF::Output(public_path().'/uploads/'.$filename, 'F');
        
        //Mail::to($order->email)->queue(new EmailCustomer($data));

        $data = array(
            'to' => $order->email,
            'company' => $order->b_company,
            'order_id' => $order->id,
            'filename'=>$filename,
            'purchasedFrom' => $purchasedFrom,
            'template' => 'emails.invoice',
            'subject' => 'Swiss Made Corp. Order'
        );

        $gmail = new GmailCustomer($data);
        $gmail->send();
    }

    private function createOrderFromPayPal($data,$customer) {
        $tax = 0;$address2='';$subtotal = 0;$total = 0;$orderstatus = 0;
        $product_ids=array();

        if ($customer['SHIPTOSTATE'] == "NY") {
            $tax = Taxable::where('state_id',$customer['SHIPTOSTATE'])->value('tax');
        }

        if (isset($customer['SHIPTOSTREET2']))
            $address2 = $customer['SHIPTOSTREET2'];

        $countries = new \App\Libs\Countries;
        $state_b = $countries->getStateByCode( $customer['SHIPTOSTATE']);

        if (Session::has('discount'))
            $discount= Session::get('discount');
        else $discount = 0;

        $orderArray = [
            'b_firstname' => '',
            'b_lastname' => '',
            'b_company' => 'Website',
            'b_address1' => '',
            'b_address2' => '',
            'b_phone' => '',
            'b_city' => '',
            'b_state' => $state_b,
            'b_country' => 231,
            'b_zip' => '',
            's_firstname' => $customer['FIRSTNAME'],
            's_lastname' => $customer['LASTNAME'],
            's_company' => $customer['FIRSTNAME'].' '.$customer['LASTNAME'],
            's_address1' => $customer['SHIPTOSTREET'],
            's_address2' => $address2,
            's_phone' => $customer['PHONENUM'],
            's_city' => $customer['SHIPTOCITY'],
            's_state' => $state_b,
            's_country' => 231,
            's_zip' => $customer['SHIPTOZIP'],
            'freight' => $data['shipping'],
            'taxable' => $tax,
            'subtotal' => $customer['PAYMENTREQUEST_0_ITEMAMT'],
            'total' => $customer['PAYMENTREQUEST_0_AMT'],
            'payment_options' => 'Due upon receipt',
            'method' => 'PayPal',
            'discount' => $discount['amount'],
            'email' => $customer['EMAIL'],
            'purchased_from' => 1,
            'status' => 0
        ];

        $customer = Customer::where('email',$customer['EMAIL'])->first();
        
        if (!$customer) {
            
            $new_customer = array(
                'firstname' => $orderArray['b_firstname'],
                'lastname' => $orderArray['b_lastname'],
                'company' => $orderArray['b_company'],
                'address1' => $orderArray['b_address1'],
                'address2' => $address2,
                'phone' => $orderArray['b_phone'],
                'country' => 231,
                'state' => $state_b,
                'city' => $orderArray['b_city'],
                'zip' => $orderArray['b_zip']
            );
            
            $customer = Customer::updateOrCreate(['email'=>$customer['EMAIL']],$new_customer);
         } 

        $order = Order::create($orderArray);
        $order->customers()->attach($customer->id);

        foreach (Cart::products() as $product) {
            $product_id = $product['id'];
            $product_ids[]=$product_id;
            $price = $product['webprice'];

            $order->products()->attach($product_id, [
                'qty' => 1,
                'price' => $price,
                'serial' => $product['serial'],
                'product_name' => $product['product_name'],
                'cost' => $product['cost']
            ]);

            Product::find($product_id)->decrement('p_qty');
            $theshow=TheShow::where('product_id',$product_id);
            if($theshow->get()){
                $theshow->delete(); 
            }
            
            $subtotal += $price;
        }
        
        $freight = $data['shipping'];
        if ($customer->cgroup == 1) {
            $tax = Taxable::where('state_id',$order->s_state)->value('tax');
            $total = number_format($subtotal + ($subtotal * ($tax/100))+$freight,2, '.', '');
            $total -=$discount;
        } else {
            $tax = 0;
            $total = $subtotal+$freight-$discount;
        }

        $order->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'taxable' => $tax,
            'freight' => $freight
        ]);

        $this->removeFromEbay($product_ids);
        RevokeWalmartProduct::dispatch($product_ids)->onConnection('sqs');
        
        // Put all sold item skus on Amazon out of stock.
        //$this->setAmazonItemOutOfStock($product_ids);
        return $order;
    }

    private function removeFromEbay($product_ids) {
        // Remove all sold items from eBay.
        $listings = EbayListing::whereIn('product_id',$product_ids)->where('status','active');
        $ebayListings = $listings->get();
        if ($ebayListings){
            foreach ($ebayListings as $listing) {
                $item=EbayController::EndItem(['datainfo' => "reason=NotAvailable&itemID=".$listing->listitem]);
            }
            $listings->delete();
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

    public function PayPalCheckout(Request $request) {
        //$provider = new ExpressCheckout;      // To use express checkout.
        
        if (Session::has('exchange_rate')) {
            if (session('exchange_rate')) {
                $exchangeRate = session('exchange_rate');
                 $this->rate = $exchangeRate['rate'];
                 $this->currencyName = $exchangeRate['currency_name'];
            } 
        }

        if (Session::has('discount'))
            $discount= Session::get('discount');
        else $discount['amount'] = 0;

        $provider = PayPal::setProvider('express_checkout');      // To use express checkout(used by default).
        $provider->setCurrency($this->currencyName);
        
        $options = [
            'BRANDNAME' => 'Swiss Made Corp.',
            'LOGOIMG' => 'https://swissmadecorp.com/public/images/logo.png',
            'CHANNELTYPE' => 'Merchant',
            'NOSHIPPING' => 0
        ];
        
        $data['items'] = [];$total = 0;
        
        foreach (Cart::products() as $product) {
            $newdata = [
                'name' =>  $product['condition'] . ' '. $product['product_name'] . " ({$product['id']})",
                'sku' => $product['id'],
                'price' => round($product['webprice']*$this->rate),
                'qty' => 1,
            ];
            array_push($data['items'],$newdata);
            $total += $product['webprice'];
        }
        
        if ($discount['amount']) {
            $newdata = [
                'name' =>  "Discount",
                'sku' => $discount['promocode'],
                'price' => -round($discount['amount']*$this->rate),
                'qty' => 1,
            ];
            array_push($data['items'],$newdata);
            $total += -round($discount['amount']);
        }

        // $order = Order::orderBy('id','desc')->first();
        $data['invoice_id'] = ''; //$order->id+1;
        $data['invoice_description'] = '' ;//"Order #{$data['invoice_id']}";
        $data['return_url'] = url('https://swissmadecorp.com/payment/success');
        $data['cancel_url'] = url('https://swissmadecorp.com/payment/cancel');
//        $data['notify_url'] = 'https://swissmadecorp.com/ipn/notify';
        $data['total'] = round($total*$this->rate);
        $data['subtotal'] = round($total*$this->rate);
        
        //give a discount of 10% of the order amount
        //$data['shipping_discount'] = round((10 / 100) * $total, 2);

        $response = $provider->addOptions($options)->setExpressCheckout($data);

        // This will redirect user to PayPal
        return redirect($response['paypal_link']);
    }
}
