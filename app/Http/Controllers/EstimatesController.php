<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Elibyy\TCPDF\Facades\TCPDF;
use PDF;
use App\Models\Order;
//use App\Mail\GMailer; 
use App\Models\Estimate;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Taxable;
use App\Models\Payment;
use Session;
use Input;


class EstimatesController extends Controller
{
    public function __construct() {
        //$this->middleware(['auth']);
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'store', 'edit', 'delete']]);
    }

    public function lvorders() {
        return view('admin.lvorders'); //->compact('users');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $estimates = Estimate::latest()->get();
        return view('admin.estimates',['pagename' => 'Orders','estimates' => $estimates,'includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.estimates.create',['pagename' => 'New Order']);
    }

    public function createFromEstimate($id)
    {
        $estimate = Estimate::find($id);
        return view('admin.estimates.createfromestimate',['pagename' => "Invoice for Order # $id",'estimate'=>$estimate]);
    }

    // Load USPS module to get the city and state based on the zip code
    public function addressFromZip(Request $request) {
        return addressFromZip($request['zip']);
    }
    
        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function storeInvoiceFromOrder(Request $request)
     {
         
         $validator = \Validator::make($request->all(), [
             'b_company' => "required",
         ]);
 
         if ($validator->fails()) {
             return back()
                 ->withInputs($request->all())
                 ->withErrors($validator);
         }
 
         $keys = array_keys($request['qty']);
         $id= $request['order_id'];
         foreach ($keys as $key) {
            $product_id = $key;
            $qty = $request['qty'][$key];
            $price = $request['price'][$key];
            $serial = $request['serial'][$key];
            $model = $request['model'][$key];
            
            $product = Product::where('p_serial',$serial)
                ->where('p_model',$model)
                ->first();
            
            if (!$product) {
                return redirect("admin/estimates/$id/invoice/create")
                    ->withInput($request->all())
                    ->withErrors(array('message' => "Serial number $serial  with model number $model does not match the inventory."));
            } elseif ($product->p_qty < 1) {
                return redirect("admin/estimates/$id/invoice/create")
                    ->withInput($request->all())
                    ->withErrors(array('message' => "Item with the serial number $serial with model number $model is out of stock."));
            }
        }

         $status = 1; // 1 = paid 2 = unpaid
         if ($request['payment_options'] != 'None') {
             $status = 0;
         } elseif ($request['payment'] == 'On Memo' || $request['payment'] == 'On Hold'){
             $status = 0;
         }
 
         $created_at = $request['created_at'];
         $estimatearray = array(
             'b_firstname' => $request['b_firstname'],
             'b_lastname' => $request['b_lastname'],
             'b_company' => $request['b_company'],
             'b_address1' => $request['b_address1'],
             'b_address2' => $request['b_address2'],
             'b_phone' => $request['b_phone'],
             'b_country' => $request['b-country'],
             'b_state' => $request['b-state'],
             'b_city' => $request['b-city'],
             'b_zip' => $request['b_zip'],
             's_firstname' => $request['s_firstname'],
             's_lastname' => $request['s_lastname'],
             's_company' => $request['s_company'],
             's_address1' => $request['s_address1'],
             's_address2' => $request['s_address2'],
             's_phone' => $request['s_phone'],
             's_country' => $request['s-country'],
             's_state' => $request['s-state'],
             's_city' => $request['s-city'],
             's_zip' => $request['s_zip'],
             'po' => strtoupper($request['po']),
             'method' => $request['payment'],
             'comments' => $request['comments'],
             'payment_options' => $request['payment_options'],
             'freight' => $request['freight'],
             'status' => $status
         );
 
         //dd($estimatearray);
         if ($created_at) {
             $estimatearray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
             $estimatearray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
         }
         
         $estimate = Order::create($estimatearray);
         
         $customer = Customer::find($request['customer_id']);
         $estimate->customers()->attach($customer->id);
         $subtotal = 0;
         $total = 0;
         $tax = 0;
         
         $status = 0;
         
         if ($request['payment'] == 'On Memo'){
             $status = 1;
         } elseif ($request['payment'] == 'On Hold'){
             $status = 2;
         } 

         foreach ($keys as $key) {
            $product_id = $key;
            $qty = $request['qty'][$key];
            $price = $request['price'][$key];
            $serial = $request['serial'][$key];
            $model = $request['model'][$key];
            
            $product = Product::where('p_serial',$serial)
                ->where('p_model',$model)
                ->first();
            
            $estimate->products()->attach($product->id, [
                'price' => $price,
                'qty' => $qty,
                'serial' => $serial
            ]);

            $tqty = $product->p_qty - $qty;
            
            $product->update([
                'p_qty' => $tqty,
                'p_status' => $status
            ]);

            $subtotal = $subtotal + ($price*$qty);
         }
         
         $freight = $request['freight'];
        if ($customer->cgroup == 0) {
            $tax = Taxable::where('state_id',$estimate->s_state)->value('tax');
            $total = $subtotal + ($subtotal * ($tax/100))+$freight;
        } else {
            $total = $subtotal+$freight;
        }
 
         $estimate->update([
             'subtotal' => $subtotal,
             'total' => $total,
             'taxable' => $tax,
             'freight' => $freight
         ]);
 
         return redirect("admin/orders/".$estimate->id);
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
            'b_company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInputs($request->all())
                ->withErrors($validator);
        }

        $status = 1; // 1 = paid 2 = unpaid
        if ($request['payment_options'] != 'None') {
            $status = 0;
        } elseif ($request['payment'] == 'On Memo' || $request['payment'] == 'On Hold'){
            $status = 0;
        }

        $estimatearray = array_slice($request->all(),4);
        $created_at = $request['created_at'];
        $freight = $request['freight'];

        $estimatearray['method'] = $request['payment'];
        $estimatearray['status'] = 0;
        
        if ($created_at) {
            $estimatearray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
            $estimatearray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
        }
        
        $customer = Customer::find($request['customer_id']);
        
        if (!$customer) {
            
            $data = array(
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

        $estimate = Estimate::create($estimatearray);
        
        $estimate->customers()->attach($customer->id);
        $subtotal = 0;
        $total = 0;
        $tax = 0;

        if ($customer->cgroup == 0) 
            $tax = Taxable::where('state_id',$request['s-state'])->value('tax');
        
        $keys = array_keys($request['qty']);
        
        foreach ($keys as $key) {
            $product_id = $request['id'][$key];
            if ($product_id) {
                $qty = $request['qty'][$key];
                $price = $request['price'][$key];
                $product_name = $request['product_name'][$key];

                $product = Product::where('id',$product_id)->first();
                $estimate->products()->attach($product_id, [
                    'price' => $price,
                    'qty' => $qty,
                    'product_name' => $product_name
                ]);

                $subtotal = $subtotal + ($price*$qty);
            }
        }
        
        if ($customer->cgroup == 0) {
            $total = $subtotal + ($subtotal * ($tax/100));
        } else 
            $total = $subtotal;

        $estimate->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'taxable' => $tax
        ]);

        return redirect("admin/estimates");
    }

    public function print($id) {
        $estimate=Estimate::find($id);
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
        //dd($estimate);
        $printOrder->print($estimate); // Print newly create proforma.
    }

    public function print1($id) {

        $estimate=Estimate::find($id);
        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        PDF::setHeaderCallback(function($pdf){
            // Logo
            $image_file = 'images/logo-swissmade.jpg';
            $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            //$pdf->SetFont('helvetica', 'T', 10);
            // Title            
        });

        PDF::setFooterCallback(function($pdf){
            // Position at 15 mm from bottom
            $pdf->SetY(-15);
            // Set font
            $pdf->SetFont('helvetica', 'I', 8);
                // Page number
            $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        });

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
        $estimateStatus = '';
        
        // $pdf::SetFont('helvetica', 'I', 23);
        // $pdf::writeHTMLCell(40, 10, $pdf::getPageWidth()-$pdf::GetStringWidth('Order')-20, 23, '<div style="font-size:25px;color:#6b8dcb;font-weight:bold">Proforma</div>', 0, 0, 0, false, 'L', false);
        // $pdf::SetFont('helvetica', '', 10);
        // $pdf::setXY($pdf::getPageWidth()-53,33);
        // $pdf::Write(0, date('F d, Y',time()), '', 0, 'L', true, 0, false, false, 0);
        $pdf::SetFont('helvetica', '', 10);
        $pdf::setXY($pdf::getPageWidth()-55,25);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold">Proforma</div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= date('F d, Y',time()) ?></td>
            </tr>
        </table>
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
        $pdf::setY(23);
        $pdf::WriteHTML('15 W 47th Street, Ste # 503<br>New York, NY 11367<br>212-840-8463<br>United States', true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();$pdf::Ln();
        $countries = new \App\Libs\Countries;
        $country = $countries->getCountry($estimate->b_country);
        $state_b = $countries->getStateCodeFromCountry($estimate->b_state);
        $state_s = $countries->getStateCodeFromCountry($estimate->s_state);
        
        $payments_options = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120'];
        
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
                    
                    <?php $b_fullname = $estimate->b_firstname . ' ' . $estimate->b_lastname ?>
                    <?= $b_fullname ?><br>
                    <?= !empty($estimate->b_company) && $b_fullname != $estimate->b_company? $estimate->b_company . '<br>' : '' ?>
                    <?= !empty($estimate->b_address1) ? $estimate->b_address1 .'<br>' : ''?>
                    <?= !empty($estimate->b_address2) ? $estimate->b_address2 .'<br>' : '' ?>
                    <?= !empty($estimate->b_city) ? $estimate->b_city .', '. $state_b . ' ' . $estimate->b_zip.'<br>': '' ?>
                    <?= !empty($country_b) ? $country_b.'<br>' : '' ?>
                    <?= !empty($estimate->b_phone) ? $estimate->b_phone . '<br>' : '' ?>
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;">
                    <?php $s_fullname = $estimate->s_firstname . ' ' . $estimate->s_lastname ?>
                    <?= $s_fullname ?><br>
                    <?= !empty($estimate->s_company) && $s_fullname != $estimate->s_company ? $estimate->s_company . '<br>' : '' ?>
                    <?= !empty($estimate->s_address1) ? $estimate->s_address1 .'<br>' : ''?>
                    <?= !empty($estimate->s_address2) ? $estimate->s_address2 .'<br>' : '' ?>
                    <?= !empty($estimate->s_city) ? $estimate->s_city .', '. $state_s . ' ' . $estimate->s_zip.'<br>': $state_s . ' ' . $estimate->s_zip .'<br>'?>
                    <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                    <?= !empty($estimate->s_phone) ? $estimate->s_phone . '<br>' : '' ?>
                </td>
                </tr>
            </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="border: 1px solid #ddd;color:#fff">Proforma Number</th>
                        <th style="border: 1px solid #ddd;color:#fff">PO Number</th>
                        <th style="border: 1px solid #ddd;color:#fff">Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd">PR<?= $estimate->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $estimate->po ?></td>
                        <td style="border: 1px solid #ddd"><?= $estimate->created_at->format('m-d-Y') ?></td>
                    </tr>
                </tbody>
            </table>

            <?php 
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
                ob_start();
            ?>

            <table cellpadding="5" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th width="100" style="border: 1px solid #ddd;color:#fff">Image</th>
                        <th width="170" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <th width="105" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                        <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                        <th style="border: 1px solid #ddd;color:#fff">Retail</th>
                        <th style="border: 1px solid #ddd;color:#fff">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estimate->products as $product) {
                        $p_image = $product->images->toArray();
                        if (!empty($p_image)) {
                            if (file_exists(base_path().production().'/images/thumbs/'.$p_image[0]['location']))
                                $image='images/thumbs/'.$p_image[0]['location'];
                            else $image = 'images/no-image.jpg'; 
                        } else $image = 'images/no-image.jpg';
                    
                            
                        ?>
                    <tr>
                    <td width="100" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                        <img style="height: 50px" src="<?= production().$image ?>" />
                        </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="170"><?= $product->categories->category_name . ' ' . $product->p_reference . ' ' . $product->p_model ?> </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="105">Not displayed</td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="50"><?= $product->pivot->qty ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right"><?= number_format($product->p_retail,2)?></td>
                        <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee"><?= number_format($product->pivot->price,2)?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Sub Total</b></td>
                        <td style="text-align: right"><?= number_format($estimate->subtotal,2)?></td>
                    </tr>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Freight</b></td>
                        <td style="text-align: right"><?=  number_format($estimate->freight,2)?></td>                            
                    </tr>
                    <?php if ($estimate->taxable) { ?>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Tax</b></td>
                        <td style="text-align: right"><?=  number_format(($estimate->taxable/100)*$estimate->subtotal,2)?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Grand Total</b></td>
                        <td style="text-align: right">$<?= number_format($estimate->total,2)?></td>
                    </tr>           
                </tfoot>
            </table>

            To avoid unessessery fees the price quoted above is only if you pay via the wire transfer. If you prefer to pay via the credit card,
            there will be a 3.5% credit card charge.
            <br><br>
            Our wire transfer information is:<br><br>

            <b>SWISS MADE CORP</b><br>
            15 west 47 th street suite 503<br>
            New York , NY 10036<br><br>

            <b>Citibank</b><br>
            1 ROCKEFELLER  PLAZA<br>
            New York , NY 10020<br>
            Routing #: 021000089<br>
            Account#: 4978134096<br>
            Swift Code: CITI  US 33<br>
        <?php

        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        $pdf::Ln();
        //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);
        $pdf::Write(0, "If you have any questions regarding this order, please contact us.", '', 0, 'C', true, 0, false, false, 0);

        //Close and output PDF document
        PDF::Output(str_replace(' ','-',$estimate->b_company).'-Proforma-'.$estimate->id.'.pdf', 'I');
    }

    public function printStatementsDue($id) {
        $estimates = estimate::where('status','0');
        foreach ($estimates as $estimate) {
            printStatement($estimate->id);
        }
    }

    public function printStatement($id) {

        $estimate=Estimate::find($id);
        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        PDF::setHeaderCallback(function($pdf){
            // Logo
            $image_file = production().'images/logo.jpg';
            $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            //$pdf->SetFont('helvetica', 'T', 10);
            // Title            
        });

        PDF::setFooterCallback(function($pdf){
            // Position at 15 mm from bottom
            $pdf->SetY(-15);
            // Set font
            $pdf->SetFont('helvetica', 'I', 8);
                // Page number
            $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        });

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

        $pdf::writeHTMLCell(60, 10, $pdf::getPageWidth()-60, 33, '<div style="font-size:25px;color:#6b8dcb;font-weight:bold">STATEMENT</div>', 0, 0, 0, false, 'L', false);
        $pdf::SetFont('helvetica', '', 10);
        $pdf::setXY($pdf::getPageWidth()-50,43);
        $pdf::Write(0, date('F d, Y',time()), '', 0, 'L', true, 0, false, false, 0);
        $pdf::setY(23);
        $pdf::WriteHTML('15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States', true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();$pdf::Ln();
        $countries = new \App\Libs\Countries;
        $country = $countries->getCountry($estimate->b_country);
        $state_b = $countries->getStateCodeFromCountry($estimate->b_state);
        $state_s = $countries->getStateCodeFromCountry($estimate->s_state);
        
        $payments_options = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120'];
        
        ob_start();
        ?>
            <table>
                <tr>
                    <td>
                        <b>To</b>:<br>
                        <?= $estimate->b_firstname . ' ' . $estimate->b_lastname ?><br>
                        <?= $estimate->b_company ?><br>
                        <?= $estimate->b_address1.'<br>' ?><?php if(!empty($estimate->b_address2)) echo ', '.$estimate->b_address2 .'<br>' ?>
                        <?= $estimate->b_city.', '. $state_b . ' ' . $estimate->b_zip ?><br>
                        <?= $country ?><br>
                        <?= $estimate->b_phone ?><br>
                    </td>
                    <td>
                        <b>Ship To</b><br>
                        <?= $estimate->s_firstname . ' ' . $estimate->s_lastname ?><br>
                        <?= $estimate->b_company ?><br>
                        <?= $estimate->s_address1 .'<br>'?><?php if(!empty($estimate->s_address2)) echo ', '.$estimate->s_address2 .'<br>' ?>
                        <?= $estimate->s_city.', '. $state_s . ' ' . $estimate->s_zip ?><br>
                        <?= $country ?><br>
                        <?= $estimate->s_phone ?><br>
                    </td>
                </tr>
            </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #3b4e87;color:#fff">
                        <th style="border: 1px solid #999;color:#fff">Date</th>
                        <th style="border: 1px solid #999;color:#fff">Reference</th>
                        <th style="border: 1px solid #999;color:#fff">Description</th>
                        <th style="border: 1px solid #999;color:#fff">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $customer_id=$estimate->customers->first()->id;
                        $estimates = Estimate::whereHas('customers',function($query) use($customer_id) {
                            $query->where('id', $customer_id);
                        })->where('status',0)->get();

                        $totals = 0;
                    foreach ($estimates as $estimate) { 
                        $totals += $estimate->total;
                    ?>
                    
                    <tr>
                        <td style="border-bottom: 1px solid #ddd"><?= $estimate->created_at->format('m/d/Y') ?></td>
                        <td style="border-bottom: 1px solid #ddd">Ord #<?= $estimate->id ?></td>
                        <td style="border-bottom: 1px solid #ddd"></td>
                        <td style="text-align:right;border-bottom: 1px solid #ddd"><?= number_format($estimate->total,2) ?></td>
                    </tr>
                        <?php foreach($estimate->payments->all() as $payment) { ?>
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
                        <td colspan="3" style="text-align:right;border-top: 2px solid #eee;background-color: #3b4e87;color:#000">BALANCE DUE</td>
                        <td style="text-align:right;border-top: 2px solid #eee;border-left: 2px solid #eee;background-color:#d2d9ec">$ <?= number_format($totals,2) ?></td>
                    </tr>
                </tbody>
            </table>
            
            

            <?php
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); ?>
             
            <?php
                $pdf::Ln();
                $pdf::Cell(0,0,'Please detach the remittance slip below and return it with your payment');
                echo str_repeat('.',183);
                $pdf::Ln();
            ?>
            <div style="text-align: center">REMITTANCE</div>
            
            <br><table style="width: 100%" cellpadding="3">
                <tr>
                    <td style="width: 56%">Please make checks payable to Swiss Made Inc. and mail to:</td>
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
            <br>Swiss Made Inc.<br>15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States
            <br><table style="width: 100%" cellpadding="5">
                <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right"><b>DUE DATE</b></td>
                    <td style="width: 15%;text-align:right;border:1px solid #eee;width: 100px"><?= date('m/1/Y', strtotime("+30 days")) ?></td>
                </tr>
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
        PDF::Output('po.pdf', 'I');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $estimate = Estimate::find($id);
        if (!$estimate)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view("admin.estimates.show",['pagename' => 'Order #'.$id, 'estimate' => $estimate]);
    }

    public function memotransfer(Request $request,$id)
    {
        $estimate = Estimate::find($id);
        $status=0;

        if ($request['payment_options'] == 'None')
            $status = 1;

        $estimate->update([
            'method' => $request['payment'],
            'payment_options' => $request['payment_options'],
            'status' => $status
        ]);

        return redirect("admin/estimates");
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $estimate = Estimate::find($id);
        if (!$estimate)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view("admin.estimates.edit",['pagename' => 'Estimate #'.$id, 'estimate' => $estimate]);
    }

    // Deletes individual product from the Estimate when in edit mode.
    public function destroyestimatedproduct(Request $request) {
        if ($request->ajax()) {
            $id = $request['estimateid'];
            $product_id = $request['productid'];
            $estimate = Estimate::find($id);
            //return response()->json($estimate);
            $product = $estimate->products->find($product_id);
            
            $estimate->products()->detach($product);
            $cgroup = $estimate->customers->first()->cgroup;
            $subtotal = 0;
            $total = 0;
            $tax = 0;

            $estimate->load('products'); // Refreshes products after removal from the table

            $product->update([
                'p_qty' => $product->p_qty + $product->pivot->qty
            ]);
            
            $this->refreshEstimateTotals($estimate);
                       
            return response()->json('success');
        }
    }

    public function refreshEstimateTotals($estimate) {
        $cgroup = $estimate->customers->first()->cgroup;
        $subtotal = 0;
        $total = 0;
        $tax = 0;

        foreach ($estimate->products as $product) {
            $qty = $product->pivot->qty;
            $price = $product->pivot->price;

            $subtotal = $subtotal + ($price*$qty);
        }
        
        $freight = $estimate->freight;
        if ($cgroup == 0) {
            $tax = Taxable::where('state_id',$estimate->s_state)->value('tax');
            $total = $subtotal + ($subtotal * ($tax/100))+$freight;
        } else {
            $total = $subtotal+$freight;
        }

        $estimate->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'taxable' => $tax,
            'freight' => $freight
        ]);
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
            'b_company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInputs($request->all())
                ->withErrors($validator);
        }

        $status = 1; // 1 = paid 2 = unpaid
        if ($request['payment_options'] != 'None') {
            $status = 0;
        } elseif ($request['payment'] == 'On Memo' || $request['payment'] == 'On Hold'){
            $status = 0;
        }

        //dd($request->all());
        $estimate = Estimate::find($id);
        $estimate->update($request->all());
        
        if ($request['price']) {
            $keys = array_keys($request['price']);
                        
            foreach ($keys as $key) {
                $product_id = $key;
                $price = $request['price'][$key];
                $qty = $request['qty'][$key];
                $product_name = $request['product_name'][$key];

                if (isset($request['id'][$key])) {
                    $id = $request['qty'][$key];
                    $product = Product::where('id',$product_id)->first();
                    $estimate->products()->attach($product->id, [
                        'price' => $price,
                        'qty' => $qty,
                        'product_name' => $product_name
                    ]);  
                } else {
                    $product = $estimate->products()->updateExistingPivot($product_id,[
                        'qty'=>$qty,
                        'price'=>$price,
                        'product_name' => $product_name
                    ]);
                    
                }

            }
        }


        $estimate->load('products'); // Refreshes products after removal from the table

        $this->refreshEstimateTotals($estimate);
        return redirect("admin/estimates");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $estimate = Estimate::find($id);
        
        $estimate->products()->detach();
        $estimate->customers()->detach();

        $estimate->delete();

        Session::flash('message', "Successfully deleted Estimate!");
        return back();
    }
}
