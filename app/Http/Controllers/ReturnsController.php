<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Models\CustomerCredit;
use PDF;
use DB;
use App\Models\Returns;
use App\Models\OrderReturn;

class ReturnsController extends Controller
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
        $returns = DB::table('order_product')
            ->select(DB::raw('order_product.order_id, b_firstname, b_lastname, b_company, order_returns.created_at date,order_returns.returns_id,order_returns.ret_op_id, SUM( price * order_product.qty ) AS amount'))
            ->join('order_returns','order_product.order_id','=','order_returns.order_id')
            ->join('orders','orders.id','=','order_returns.order_id')
            ->whereRaw('order_product.product_id=order_returns.product_id')
            ->groupBy('order_product.order_id')
            ->get();

        return view('admin.returns',['pagename' => 'Returns','returns' => $returns,'includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $order = Order::find($id);
        if (!$order)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        $returns = OrderReturn::select(\DB::raw('product_id,qty,max(order_returns.created_at) as date,ret_op_id'))
                    ->join('returns','id','=','order_returns.returns_id')
                    ->join('orders', 'orders.id', '=','order_returns.order_id')
                    ->where('returns.order_id',$id)
                    ->groupBy('ret_op_id')
                    ->get();

        $productName = 'Create Return Order # '.$order->id;
        //dd($returns);
        return view('admin.returns.create',['pagename' => $productName,'order' => $order,'orderreturns' => $returns]);
    }

    public function ajaxReturnAll(Request $request) {
        if ($request->ajax()) {
            parse_str($request['_form'],$output);

            $comment = $request['_comment'];
            $order_id=$output['order_id'];
            $order = Order::find($order_id);

            $return = Returns::where('order_id',$order_id)->get();

            if ($return->isEmpty()) {
                $return = Returns::create ([
                    'order_id' => $order_id,
                    'comment' => $comment
                ]);

            } else {
                $return = Returns::where('order_id',$order_id)->update ([
                    'comment' => $comment
                ]);
                $return = Returns::where('order_id',$order_id)->get();
            }

            $total=0;
            for ($i=0;$i < count($output['product_id']);$i++) {
                $product_id = $output['product_id'][$i];
                $opid = $output['op_id'][$i];
                $qty_purchased = $output['qty_purchased'][$i];

                $order_product=\DB::table('order_product')->where('op_id', $opid);
                $obj_order_product=$order_product->first();

                $order->returns()->attach($return,[
                    'ret_op_id' => $opid,
                    'product_id' => $product_id,
                    'qty' => abs($qty_purchased),
                ]);

                $product = $order->products->find($product_id);
                if ($order->method=='Invoice') {
                    if ($product->p_status!=4 && $product->category_id!=74) {
                        $product->update([
                            'p_qty' => ($product->p_qty + $qty_purchased),
                            'p_status' => 0
                        ]);
                    }

                    if ($total == 0)
                        $status = 2; // mark 2 for return

                } elseif ($order->method=='On Memo') {
                    $status=2;
                    if ($product->p_status!=4) {
                        $product->update([
                            'p_status' => 0
                        ]);
                    }
                }

                $order_product->update(['qty'=>-($obj_order_product->qty)]);
            }

            $order->update([
                'total' => 0,
                'status' => $status
            ]);

            return response()->json(array('date' => date('m-d-Y')));
        }
    }

    public function ajaxReturnItem(Request $request){
        if ($request->ajax()) {
            //return response()->json(array('qty'=>1,'date' => date('m-d-Y')));
            $id = $request['_id'];
            $opid = $request['_opid'];
            $order_id = $request['_orderid'];
            $qty =$request['_qty'];
            $comment = $request['_comment'];

            $order_product=\DB::table('order_product')->where('op_id', $opid);
            $obj_order_product=$order_product->first();

            $order = Order::find($order_id);
            $return = Returns::where('order_id',$order_id)->get();

            if ($return->isEmpty()) {
                $return = Returns::create ([
                    'order_id' => $order_id,
                    'comment' => $comment
                ]);

            } else {
                $return = Returns::where('order_id',$order_id);

                $return->update ([
                    'comment' => $comment
                ]);

                $return = $return->get();
            }

            $order->returns()->attach($return,[
                'ret_op_id' => $opid,
                'product_id' => $id,
                'qty' => $qty,
            ]);

            $status = $order->status;

            $product=$order->products->find($id);
            $subtotal = ($order->subtotal-($obj_order_product->price*$qty));
            $total = ($order->total-($obj_order_product->price*$qty));

            if ($order->method=='Invoice') {
                if ($product->p_status!=4 && $product->category_id!=74)
                    $product->update([
                        'p_qty' => ($product->p_qty + $qty),
                        'p_status' => 0
                    ]);

                if ($total == 0) {
                    $status = 2; // mark 3 for return

                    foreach ($order->payments as $payment) {

                        $payment->delete();

                    }

                } else {
                    $totalPaid = $order->payments->sum('amount');

                    if ($totalPaid == $total && $totalPaid != 0)
                        $status = 1; // mark 1 for paid in full
                    elseif ($totalPaid > $total && $totalPaid != 0) {
                        $status = 1; // mark 1 for paid in full
                        CustomerCredit::create([
                            'customer_id' => $order->customers->first()->id,
                            'amount'=>$obj_order_product->price
                        ]);
                    }
                }
            } elseif ($order->method=='On Memo') {

                if ($total == 0)
                    $status=2;

                if ($product->p_status!=4)
                    $product->update([
                        'p_status' => 0
                    ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => $status
            ]);

            $orgQty = abs($product->pivot->qty);
            $order_product->update(['qty'=>($obj_order_product->qty-$qty)]);

            return response()->json(array('qty'=>$orgQty,'date' => date('m-d-Y')));
        }
    }

    public function print($id) {
        $order=Order::find($id);

        $this->printReturn($order);
    }

    protected function printReturn($order) {
        // set document information

        $return = $order->returns->first();
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        PDF::setHeaderCallback(function($pdf){
            // Logo
            $image_file = '/images/logo.jpg';
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
        $orderStatus = '';

        // if ($order->status==0) {
        //     $orderStatus = "Invoice";
        // } else {
        //     $orderStatus = "Order";
        // }
        if ($order->method == 'On Memo'){
            $orderStatus = "Memo";
        } else $orderStatus = "Invoice";

        $pdf::setXY($pdf::getPageWidth()-85,20);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold"><?= $orderStatus.' Return'?></div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $return->pivot->created_at->format('F d, Y') ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= "Return No: " . $return->id ?></td>
            </tr>
        </table>
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //$pdf::writeHTMLCell(40, 10, $pdf::getPageWidth()-46, 23, '<div style="font-size:25px;color:#6b8dcb;font-weight:bold">'.$orderStatus.'</div>', 0, 0, 0, false, 'L', false);
        $pdf::SetFont('helvetica', '', 10);
        //$pdf::setXY($pdf::getPageWidth()-50,33);
        //$pdf::Write(0, date('F d, Y',time()), '', 0, 'L', true, 0, false, false, 0);
        $pdf::setY(24);
        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>212-840-8463<br>info@swissmadecorp.com", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $countries = new \App\Libs\Countries;
        $country = $countries->getCountry($order->b_country);
        $state_b = $countries->getStateCodeFromCountry($order->b_state);
        $state_s = $countries->getStateCodeFromCountry($order->s_state);
        $pdf::setY(45);
        ob_start();
        ?>
            <table cellpadding="1">
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
                        <?= $order->b_firstname . ' ' . $order->b_lastname ?><br>
                        <?= !empty($order->b_company) ? $order->b_company . '<br>' : '' ?>
                        <?= !empty($order->b_address1) ? $order->b_address1 .'<br>' : ''?>
                        <?= !empty($order->b_address2) ? $order->b_address2 .'<br>' : '' ?>
                        <?= !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': '' ?>
                        <?= !empty($country) ? $country.'<br>' : '' ?>
                        <?= !empty($order->b_phone) ? $order->b_phone . '<br>' : '' ?>
                    </td>
                    <td style="width: 80px"></td>
                    <td style="width: 43%;">
                        <?= $order->s_firstname . ' ' . $order->s_lastname ?><br>
                        <?= !empty($order->b_company) ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': '' ?>
                        <?= !empty($country) ? $country.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                    </td>
                </tr>
            </table>

            <table cellpadding="8">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="border: 1px solid #ddd;color:#fff"><?= $order->po ? "PO" : $orderStatus ?> #</th>
                        <th style="border: 1px solid #ddd;color:#fff">Invoice Date</th>
                        <th style="border: 1px solid #ddd;color:#fff">Return #</th>
                        <th style="border: 1px solid #ddd;color:#fff">Return Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= $order->po ? strtoupper($order->po) : $order->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                        <td style="border: 1px solid #ddd"><?= $return->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $return->pivot->created_at->format('m-d-Y') ?></td>
                    </tr>
                </tbody>
            </table>

            <?php
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
                $amount=0;
                ob_start();
            ?>

            <table cellpadding="5" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th width="100" style="border: 1px solid #ddd;color:#fff">Image</th>
                        <th width="200" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <th width="80" style="border: 1px solid #ddd;color:#fff">Serial #</th>
                        <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                        <th style="border: 1px solid #ddd;color:#fff">Retail</th>
                        <th style="border: 1px solid #ddd;color:#fff">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $returns = Order::join('returns','orders.id','=','returns.order_id')
                    ->join('order_returns','returns.order_id','=','order_returns.order_id')
                    ->where('order_returns.order_id',$order->id)
                    ->groupBy('product_id')
                    ->get();
                ?>
                <?php foreach ($returns as $return) {
                        foreach ($order->products as $product) { ?>
                        <?php if ($product->id==$return->product_id) {?>
                        <?php $amount += $product->pivot->price ?>
                    <tr>
                        <td width="100" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                            <img style="height: 60px" src="<?= '/images/'.$product->images->first()->location ?>" />
                        </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="200"><?= $product->title ?> </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="80"><?= $product->pivot->serial ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="50"><?= $product->pivot->qty ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right"><?= number_format($product->p_retail,2)?></td>
                        <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee"><?= number_format($product->pivot->price*$product->pivot->qty,2)?></td>
                    </tr>
                    <?php } ?>
                    <?php } ?>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Total Return</b></td>
                        <td style="text-align: right">$<?= number_format($amount,2)?></td>
                    </tr>
                </tfoot>
            </table>

        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        $pdf::Ln();
        //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);
        $pdf::Write(0, "If you have any questions regarding this return, please contact us.", '', 0, 'C', true, 0, false, false, 0);

        //Close and output PDF document
        PDF::Output(str_replace(' ','-',$order->b_company).'-'.$orderStatus.'-'.$order->id.'.pdf', 'I');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
}
