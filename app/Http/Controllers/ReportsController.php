<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Order;
use App\Models\Product;
use Elibyy\TCPDF\Facades\TCPDF;
use PDF;
use Omnipay\Omnipay;
use Config;

class ReportsController extends Controller
{
    public function index() {
        
        return view('admin.reports',['pagename'=>'Reports']);
 
        // $gateway = Omnipay::create('PayPal_Pro');
        // $gateway->setUsername(Config::get('paypal.live.username'));
        // $gateway->setPassword(Config::get('paypal.live.password'));
        // $gateway->setSignature(Config::get('paypal.live.secret'));
        // $gateway->setCurrency(Config::get('paypal.live.currency'));

        // $formData = array('number' => '4111111111111111', 'expiryMonth' => '6', 'expiryYear' => '2030', 'cvv' => '123');
        // $response = $gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => $formData))->send();

        // dd($response);
    }

    public function show() {

    }
    
    public function byCriteria($criteria) {
        if ($criteria == 'product') {
            return view('admin.reportbyproduct',['pagename'=>'Report By Products']);
        } elseif ($criteria == "company") {
            return view('admin.reportbycompany',['pagename'=>'Report By Company']);
        } elseif ($criteria == 'memo') {
            $memos = Order::where('status','0')
                ->where('method','=','On Memo')
                ->get();

            return view('admin.reportbymemo',['pagename'=>'Report By Memo','memos'=>$memos]);
        } elseif ($criteria == 'unpaid') {
            $orders = Order::where('status','0')
                ->where('method','<>','On Memo')
                ->where('method','<>','On Hold')
                ->get();
        
            return view('admin.reportbyunpaidorder',['pagename'=>'Report By Unpaid Orders','orders'=>$orders]);
        } elseif ($criteria == 'paid') {
            return view('admin.reportbypaidorder',['pagename'=>'Report By Paid Orders']);
        } elseif ($criteria == 'supplier') {
            return view('admin.reportbysupplier',['pagename'=>'Report By Suppliers']);
        }
    }

    public function bySupplier() {
        $suppliers = Product::selectRaw('id,supplier,p_reference,p_model,p_serial,p_price,created_at')
        ->get();

        foreach ($suppliers as $supplier) {
            $data[] = array("<a href='/admin/products/$supplier->id/edit'>". $supplier->id.'</a>',
                $supplier->p_model,
                $supplier->p_serial,
                $supplier->created_at->format('m/d/Y'),
                $supplier->supplier,
                number_format($supplier->p_price,2)
            );
        }

        return response()->json(array('data'=>$data));
    }

    public function byCompany() {
        
        $companies = Order::selectRaw('company, firstname,lastname,sum(total) total')
            ->join('customer_order','customer_order.order_id','=','orders.id')
            ->join('customers','customer_order.customer_id','=','customers.id')
            ->groupBy('company','company', 'firstname','lastname')
            ->get();

//            dd($companies->payments);
//return $companies;
        // select company, firstname,lastname,sum(total) total from `orders` inner join 
        // `customer_order` on `customer_order`.`order_id` = `orders`.`id` inner join 
        // `customers` on `customer_order`.`customer_id` = `customers`.`id` 
        // group by `company`, `company`, `firstname`, `lastname`

        //$products = Product::with('orders')->get();
        foreach ($companies as $company) {
            $paidAmount = $company->payments->sum('amount');
            $fullname = (!$company->company) ? $company->firstname . " " . $company->lastname : $company->company;
            $data[] = array($fullname,
                "$".number_format($company->total,2),
                "$".number_format($paidAmount,2)
            );
        }

        return response()->json(array('data'=>$data));
    }

    public function byPaid() {
        $paidOrders = Order::where('status','1')
            ->where('method','<>','On Memo')
            ->where('method','<>','On Hold')
            ->get();

            
        $grandTotal=0;$subtotal=0;$cost=0;$totalcost=0;$profit=0;$totalprofit=0;$orderAmount=0;
        foreach ($paidOrders as $order) {
            foreach ($order->products as $product) {
                $orderAmount+=$product->p_price;
                $cost+=$product->p_price; 
            }   

            $grandTotal += $order->total;
            $subtotal = $order->total;
            $totalcost+=$cost;
            $profit=$subtotal-$orderAmount;
            $totalprofit+=$profit;
        
            $data['data'][] = array(
                "<a href='/admin/orders/$order->id'>". $order->id.'</a>',
                $order->created_at->format('m/d/Y'),
                $order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname,
                '$'.number_format($cost,2),
                '$'.number_format($subtotal,2),
                '$'.number_format($profit,2)
            );

            $cost=0;$profit=0;$orderAmount=0;
        }

        $totalcost='$'.number_format($totalcost,0);
        $grandTotal='$'.number_format($grandTotal,0);
        $totalprofit='$'.number_format($totalprofit,0);

        $data['totals'] = array($totalcost,$grandTotal,$totalprofit);
        return response()->json(array('data'=>$data));
    }

    public function byProduct() {
        $products = DB::table('products')->selectRaw('if(b_company="",concat(b_firstname, " " ,b_lastname),b_company) as company,order_id,p_reference,p_model,title,p_serial,COALESCE(product_id,0) product_id, orders.created_at, p_retail')
            ->leftJoin('order_product','product_id','=','products.id')
            ->Join('orders','orders.id','=','order_id')

            //->groupBy('p_model','product_id')
            ->orderBy('product_id','asc')
            ->get();

        //$products = Product::with('orders')->get();
        foreach ($products as $product) {
            $data[] = array("<a href='/admin/orders/$product->order_id'>". $product->order_id.'</a>',
                $product->product_id,
                $product->title,
                $product->p_serial,
                $product->p_retail,
                $product->company,
                date('m-d-Y',strtotime($product->created_at))
            );
        }

        return response()->json(array('data'=>$data));
    }

    private function initializePDF($pdf,$title,$orienation='P') {
        PDF::setHeaderCallback(function($pdf) use ($title) {
            // Logo
            $pdf->SetFont('helvetica', 'I', 8);
            // Page number
            $pdf->Cell(0, 10, $title. " - ".date('F d, Y',time()), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-10, PDF_MARGIN_RIGHT);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER-15);

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
        $pdf::AddPage($orienation);

        $pdf::SetFont('helvetica', '', 10);
        $count = 0;$sub_count=0;$oldModel='';
    }

    public function printMemos() {
        $orders = Order::where('status','0')
            ->where('method','=','On Memo')
            ->get();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->initializePDF($pdf,"Berd Vaye Inc. - Memos",'L');

        ob_start();
        ?>
        <table id="orders" cellpadding="3" >
        <thead>
            <tr>
            <th style="width: 80px;background-color: #b9b9b9"><b>Memo Id</b></th>
            <th style="width: 80px;background-color: #b9b9b9"><b>Date</b></th>
            <th style="width: 210px;background-color: #b9b9b9"><b>Customer</b></th>
            <th style="width: 250px;background-color: #b9b9b9"><b>Product</b></th>
            <th style="width: 110px;background-color: #b9b9b9"><b>SN</b></th>
            <th style="width: 100px;background-color: #b9b9b9"><b>Cost</b></th>
            <th style="width: 100px;background-color: #b9b9b9"><b>Total Amount</b></th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0;$subtotal=0;$cost=0;
                foreach ($orders as $order) {
                    $arr=array();
                    foreach ($order->products as $product) {
                        $cost+=$product->p_price;
                        $product_name= $product->title;
                        $arr[]=array('title'=>$product_name,'size'=>$product->p_model,'serial'=>$product->p_serial,'price'=>$product->p_price,'cost'=>$product->pivot->price);
                    }
                    ?>
                        <?php $grandTotal += $order->total ?>
                        <?php $subtotal = $order->total ?>
                        
                        <?php foreach($order->payments as $payment) { ?>
                            <?php $subtotal -= $payment->amount ?>
                            <?php $grandTotal -= $subtotal ?>
                        <?php } ?>
                    
                    <?php foreach ($arr as $pr) {?>
                    <tr>
                        <td style="width: 80px;"><?= $order->id ?></td>
                        <td style="width: 80px;"><?= $order->created_at->format('m-d-Y') ?></td>
                        <td style="width: 210px;"><?=$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname ?></td>
                        <td style="width: 250px;"><?= $pr['title'] ?></td>
                        <td style="width: 110px;"><?= $pr['serial'] ?></td>
                        <td style="width: 100px;text-align: right">$<?= number_format($pr['price'],2) ?></td>
                        <td style="width: 100px;text-align: right">$<?= number_format($pr['cost'],2) ?></td>
                        
                    </tr>
                    <?php } ?>
                    <?php $cost=0;?>
                    <?php } ?>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                </tbody>
                <tfoot >
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;font-weight:bold;">Total:</td>
                        <td style="text-align: right;font-weight:bold;">$<?= number_format($grandTotal,2) ?></td>
                    </tr>
                </tfoot>
            </table>

        <?php 
            
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        PDF::Output('memos.pdf', 'I');
    }

    public function printSales($param='') {
        if ($param=='unpaid') {
            $status=0;
            $title = 'Unpaid Sales';
        } else {
            $status=1;
            $title = 'Paid Sales';
        }

        $orders = Order::where('status',$status)
            ->where('method','<>','On Memo')
            ->where('method','<>','On Hold')
            ->get();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->initializePDF($pdf,"Swiss Made Inc. - $title");
        ob_start();
        ?>
        <table id="orders" cellpadding="3" >
        <thead>
            <tr>
            <th style="width: 80px;background-color: #b9b9b9"><b>Invoice Id</b></th>
            <th style="background-color: #b9b9b9"><b>Date</b></th>
            <th style="width: 180px;background-color: #b9b9b9"><b>Customer</b></th>
            <th style="background-color: #b9b9b9"><b>Total Cost</b></th>
            <th style="background-color: #b9b9b9"><b>Total Amount</b></th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0;$subtotal=0;$cost=0;$totalcost=0;
                foreach ($orders as $order) {
                    foreach ($order->products as $product) {
                        $cost+=$product->p_price;
                    }

                    $grandTotal += $order->subtotal-$order->discount;
                    $subtotal = $order->subtotal-$order->discount; 
                    $totalcost+=$cost;
                ?>

                    <tr>
                        <td style="width: 80px;"><?= $order->id ?></td>
                        <td><?= $order->created_at->format('m-d-Y') ?></td>
                        <td style="width: 180px;"><?=$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname ?></td>
                        <td style="text-align: right">$<?= number_format($cost,2) ?></td>
                        <td style="text-align: right">$<?= number_format($subtotal,2) ?></td>
                    </tr>
                    <?php $cost=0;?>
                    <?php } ?>
                    <tr>
                        <td colspan="5"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;font-weight:bold;">Total:</td>
                        <td style="text-align: right;font-weight:bold;">$<?= number_format($totalcost,2) ?></td>
                        <td style="text-align: right;font-weight:bold;">$<?= number_format($grandTotal,2) ?></td>
                    </tr>
                </tfoot>
            </table>

        <?php 
            
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        PDF::Output('unpaid-sales.pdf', 'I');
    }

    public function printSalesWithProducts($param='') {
        
        if ($param=='unpaid') {
            $orders = Order::join('customer_order','orders.id','=','customer_order.order_id')->where('status',0);
        } else {
            $orders = Order::join('customer_order','orders.id','=','customer_order.order_id')->where('status',1);
        }

        $orders= $orders->where('method','<>','On Memo')
        ->where('method','<>','On Hold')
        ->orderBy('id','asc')
        ->get();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->initializePDF($pdf,"Swiss Made Inc. - Ordered Products","L");
        ob_start();
        ?>
        <table id="orders" cellpadding="3">
        <thead>
            <tr>
            <th style="width: 80px;background-color: #b9b9b9"><b>Invoice Id</b></th>
            <th style="background-color: #b9b9b9"><b>Date</b></th>
            <th style="background-color: #b9b9b9"><b>Customer</b></th>
            <th style="width: 350px;background-color: #b9b9b9"><b>Product</b></th>
            <th style="width: 100px;background-color: #b9b9b9"><b>Cost</b></th>
            <th style="width: 100px;background-color: #b9b9b9"><b>Total Amount</b></th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0;$subtotal=0;$cost=0;$totalcost=0;$product_name='';$totals=0;
                foreach ($orders as $order) {
                    $arr=array();
                    foreach ($order->products as $product) {
                        $cost+=$product->pivot->price;
                        $product_name= $product->categories->category_name . ' ' . $product->p_model . ' ' . $product->p_reference.' (SN:'.$product->p_serial.')';
                        $arr[]=array('product_name'=>$product_name,'price'=>$product->pivot->price);
                    }

                    if ($order->status==0) {
                        foreach ($order->payments as $payment){
                            $totals += $payment->amount;
                        }
                    } 

                    $grandTotal += $order->total-$totals;
                    $subtotal = $order->total-$totals;
                    $totalcost+=$cost;
                    $totals=0
                ?>

                    <?php foreach ($arr as $pr) {?>
                    <tr>
                        <td style="width: 80px;"><?= $order->id ?></td>
                        <td><?= $order->created_at->format('m-d-Y') ?></td>
                        <td><?=$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname ?></td>
                        <td style="width: 350px;"><?= $pr['product_name'] ?></td>
                        <td style="width: 100px;text-align: right">$<?= number_format($pr['price'],2) ?></td>    
                        <td style="width: 100px;text-align: right">$<?= number_format($subtotal,2) ?></td>
                    </tr>
                    <?php } ?>
                    <?php $cost=0;?>
                    <?php } ?>
                    <tr>
                        <td colspan="6"></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;font-weight:bold;">Total:</td>
                        <td style="text-align: right;font-weight:bold;">$<?= number_format($totalcost,2) ?></td>
                        <td style="text-align: right;font-weight:bold;">$<?= number_format($grandTotal,2) ?></td>
                    </tr>
                </tfoot>
            </table>

        <?php 
                   
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        PDF::Output('unpaid-sales.pdf', 'I');
    }
}
