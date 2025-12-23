<?php

namespace App\Http\Controllers;

use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;
use Session;
use PDF;

class PaymentsController extends Controller
{

    public function __construct() {
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'store', 'edit', 'delete']]);
        
        // Alternativly
        //$this->middleware('role:viewer', ['except' => ['index', 'show']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.payments',['pagename' => 'Payments','includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function getInvoicePayments() {
        
        $orders = Customer::select(\DB::Raw('customer_id, company, amount, max_date'))
            ->join(\DB::Raw('(select customer_id, sum(amount) amount, max(order_payment.created_at) max_date
                    FROM customer_order JOIN order_payment ON customer_order.order_id = order_payment.order_id
                    GROUP BY customer_id) name_date'),'customers.id','=','name_date.customer_id')
            ->groupBy('customer_id')
            ->orderByRaw('max_date desc')
            ->get();
        
        foreach ($orders as $order) {
            $data[] = array(
                $order->customer_id,
                $order->company,
                '$'. number_format($order->amount,2),
                date('m-d-Y',strtotime($order->max_date))
            );
        }

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $payments = Payment::where('order_id',$id)->orderBy('created_at','asc')->get();
        $order = Order::find($id);
        return view('admin.payments.create',['pagename' => 'Payments for Invoice #' . $id,'payments' => $payments, 'order' => $order]);
    }

    public function print(Order $order) {
        
        // set document information
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $purchasedFrom = $order->purchased_from;
        $email = 'info@swissmadecorp.com';
        if ($purchasedFrom==2)
            $email = 'signtimeny@gmail.com';

        PDF::setHeaderCallback(function($pdf) use ($purchasedFrom) {
            // Logo
            if ($purchasedFrom==1) {
                $image_file = 'assets/logo-swissmade.jpg';
                $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            } else {
                $image_file = 'assets/logo-signaturetime.jpg';
                $pdf->Image($image_file, 14, 10, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // $pdf->SetY(17);
                // $pdf->SetFont('helvetica', 'T', 13);
                // $pdf->WriteHTML("<b><i>SIGNATURE TIME</i></b>", false, false, false, false, 'L');
            }

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
            $pdf->Cell(0, 0, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C');
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
        $pdf::setXY($pdf::getPageWidth()-55,25);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold">Statement</div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $order->created_at->format('F d, Y') ?></td>
            </tr>
        </table>
        <?php
        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
        $pdf::SetFont('helvetica', '', 10);
        $pdf::setY(23);
        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>212-840-8463<br>$email", true, false, false, false, '');
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
                <?php $s_fullname = $order->s_firstname . ' ' . $order->s_lastname ?>
                        <?= $s_fullname ?><br>
                        <?= !empty($order->s_company) && $s_fullname != $order->s_company ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': $state_s . ' ' . $order->s_zip .'<br>'?>
                        <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>    
                </td>
                <td style="width: 80px"></td>

                </tr>
            </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #3b4e87;color:#fff">
                        <th width="80" style="border: 1px solid #999;color:#fff">Date</th>
                        <th width="100" style="border: 1px solid #999;color:#fff">Invoice #</th>
                        <th width="300" style="border: 1px solid #999;color:#fff">Reference</th>
                        <th width="160" style="border: 1px solid #999;color:#fff">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $totals = $order->total;
                        foreach($order->payments->all() as $payment) { ?>
                        <tr>
                            <td width="80" style="border-bottom: 1px solid #ddd"><?= $payment->created_at->format('m/d/Y') ?></td>
                            <td width="100" style="border-bottom: 1px solid #ddd"><?= $payment->order_id ?></td>
                            <td width="300" style="border-bottom: 1px solid #ddd"><?= $payment->ref ?></td>
                            <td width="160" style="text-align:right;border-bottom: 1px solid #ddd">-<?= number_format($payment->amount,2) ?></td>
                        </tr>
                        <?php $totals = $totals - $payment->amount; ?>
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

            <?php if ($totals > 0) { ?>
            <?=  str_repeat('.',183); ?>
            
            <br><table style="width: 100%" cellpadding="3">
                <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right">STATEMENT DATE</td>
                    <td style="width: 15%;text-align:right"><?=date('m/d/Y',time())?></td>
                </tr>
                <tr>
                    <td style="width: 56%"></td>
                    <td style="width: 30%;text-align:right">CUSTOMER ID</td>
                    <td style="width: 15%;text-align:right"><?=$order->customers->first()->id?></td>
                </tr>
            </table>
            
            <br>
            <br>Swiss Made Corp.<br>15 W 47th Street, Ste # 503<br>New York, NY 10036<br>212-840-8463<br>info@swissmadecorp.com
            <br>
            <table style="width: 100%" cellpadding="5" cellspacing="5">
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
            </table>
            
            <?php
            } else { ?>
                <br><br><br>

                <table style="width: 100%" cellpadding="5" cellspacing="5">
                    <tr>
                        <td style="text-align:center;font-weight:bold;color:green;font-size: 20px;font-family:times">Order has been paid in full.</td>
                    </tr> 
                </table>

            <?php } 

            $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        
        PDF::Output(str_replace(' ','-',$order->b_company).'- Order -'.$order->id.'.pdf', 'I');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $validator = \Validator::make($request->all(),[
            'payment' => 'required',
            'payment_option' => 'required'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        if ($request['payment'] > $request['totalLeft'])
            $applyAmount = $request['totalLeft'];
        else $applyAmount = $request['payment'];

        Payment::create ([
            'amount' => $applyAmount,
            'ref' => $request['payment_option'],
            'order_id' => $request['order_id']
        ]);

        $order = Order::find($request['order_id']);
        if ($order->payments->sum('amount') == $order->total) {
        // if ($applyAmount == $request['totalLeft']) {
            $order = Order::find($request['order_id']);
            
            $order->update([
                'status' => 1
            ]);
        }
        //     $totalAmountLeft = $request['payment']-$applyAmount;

        //     if ($totalAmountLeft > 0) {
        //         $customer_id=$order->customers->first()->id;
        //         $orders = Order::whereHas('customers', function($query) use($customer_id) {
        //             $query->where('id',$customer_id);
        //         })
        //         ->where('status',0)
        //         ->where('method','<>','On Memo')
        //         ->orderBy('created_at','asc')
        //         ->get();

        //         foreach ($orders as $order) {
        //             if ($totalAmountLeft > $order->total)
        //                 $applyAmount = $order->total;
        //             else $applyAmount = $totalAmountLeft;

        //             $totalAmountLeft = $totalAmountLeft-$applyAmount;
        //             Payment::create ([
        //                 'amount' => $applyAmount,
        //                 'ref' => 'Credit applied from previous invoice #: '.$request['order_id'],
        //                 'order_id' => $order->id
        //             ]);
        //             if ($totalAmountLeft <= 0) break;
        //         }
        //     }
        // }

        //Session::flash('message', "The remainding amount was applied towards older invoice(s).");
        return back();
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
        $customer = Customer::find($id);//->orderBy('created_at','desc');
        
        return view('admin.payments.edit',['pagename' => 'Payment for customer #'.$customer->id, 'customer' => $customer]);
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
    public function destroy($id,$payment)
    {
        $payment = Payment::find($payment);
        $payment->delete();

        Order::find($id)->update(['status' => 0]);

        Session::flash('message', "Successfully deleted payment!");
        return back();
    }
}
