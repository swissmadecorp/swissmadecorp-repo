<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Elibyy\TCPDF\Facades\TCPDF;
use PDF;
use App\Models\Product;
use App\Models\TheShow;
use Carbon\Carbon;

class TheshowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $theshow = TheShow::with('product')->orderBy('product_id')->get();
        return view('admin.theshow',['pagename'=>'At The Show','theshow'=>$theshow]);
    }

    public function ajaxRemoveProduct(Request $request) {
        if ($request->ajax()) {
            $id = $request['id'];
            $theshow=TheShow::where('product_id',$id);
            
            if(count($theshow->get())){
                $product = Product::find($id);
                $product->update(['p_status'=>0]);

                $theshow->delete(); 
                return response()->json(array('error'=>'','cost'=>$product->p_price,'qty'=>$product->p_qty));
            } else {
                $product = Product::with('orders')->find($id);
                if ($product) {
                    $order=$product->orders->first();

                    if ($order)
                        return response()->json(array('error'=>'Product was reserved for order #'.$order->id . ' for ' .$order->b_company));
                }

                return response()->json(array('error'=>'No product found in this collection'));
            }
        }
    }

    public function print() {
        $theshow = TheShow::with('product')->orderBy('product_id')->get();

        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        
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

        $pdf::setXY(15,10);
        ob_start(); $tamount=0;$tprice=0;
        ?>
        <table cellpadding="3" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color: #111;color:#fff">
                    <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                    <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                    <th width="220" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                    <th width="100" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                    <th width="90" style="border-right: 1px solid #ddd;color:#fff">Cost</th>
                    <th width="90" style="border-right: 1px solid #ddd;color:#fff">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $i=0;
                    foreach ($theshow as $atshow) { 
                    $i++;
                    $product = $atshow->product;
                    if ($product->images->first()) {
                        $img = "images/thumbs/".$product->images->first()->location;
                    } else $img = "images/no-image.jpg";

                    $tamount = $tamount + $product->p_price;
                    $tprice = $tprice + $product->p_price3P;
                    ?>
                <tr nobr="true">
                    <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                    <img style="width: 70px" src="<?= $img ?>">
                    </td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="50"><?= $product->id ?></td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="220"><?= $product->title ?> </td>
                    <td style="border-left: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="100"><?= $product->p_serial ?></td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;text-align: right;" width="90"><?= number_format($product->p_price,0) ?> </td>
                    <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;text-align: right;" width="90"><?= number_format($product->p_price3P,0) ?> </td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: right" colspan="2"><b>Total</b></td>
                    <td style="text-align: right" colspan="2"><?= $i ?></td>
                    <td style="text-align: right">$<?= number_format($tamount,0) ?></td>
                    <td style="text-align: right">$<?= number_format($tprice,0) ?></td>
                </tr>
            </tfoot>
        </table>
                        
        <?php
        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        PDF::Output('items.pdf', 'I');
    }

    public function ajaxgetProduct(Request $request) {

        if ($request->ajax()) {
            $id = $request['id'];

            $theshow=TheShow::where('product_id',$id)->first();
            if(!$theshow){
                $product = Product::with('categories')
                    ->where('id',$id)
                    ->first();
                if (!$product) {
                    return response()->json(array('error'=>"Product doesn't exist in the database"));    
                }
                
                $product->p_status=5;
                $product->update();

                Theshow::insert([
                    'product_id'=>$id, 
                    'created_at'=>Carbon::now('America/New_York'),
                ]);

                ob_start();?>
                <tr>
                    <td class="text-center" style="width: 80px">
                    <?php if (count($product->images)) { ?>
                        <img style="width: 70px" title="<?php $product->title ?>" alt="<?php $product->title ?>" src="<?= '/images/thumbs/' . $product->images->first()->location ?>">
                    <?php } else { ?>
                        <img style="width: 70px" title="<?php $product->title ?>" alt="<?php $product->title ?>" src="/images/no-image.jpg">
                    <?php } ?>
                    </td>
                    <td><?= $id ?></td>
                    <td><?= $product->title ?></td>
                    <td><?= $product->p_serial ?></td>
                    <td class="text-right">$<?= number_format($product->p_price,0) ?></td>
                </tr>
                <?php
                $content=ob_get_clean();
                return response()->json(array('error'=>'','content'=>$content,'cost'=>$product->p_price,'qty'=>$product->p_qty));
            } else 
                return response()->json(array('error'=>'Product already transferred'));
        }
    }

    public function ajaxStore(Request $request)
    {
        if ($request->ajax()) {
            $data = array();

            parse_str($request['_form'],$output);
            
            $amount=$output['margin-amount'];
            foreach ($request['_options'] as $option) {
                $data[] = array(
                    'product_id'=>$option, 
                    'amount'=>$amount,
                    'margin'=>$output['marginamount'],
                    'created_at'=>Carbon::now('America/New_York'),
                    'updated_at'=>Carbon::now('America/New_York'),
                );
            }

            Margin::insert($data);
            $product = Product::find($option);

            if ($output['marginamount']=='Percent') {
                $product->update(['p_newprice' => $product->p_retail-($product->p_retail*($amount/100))]);
            } else
                $product->update(['p_newprice' => $amount]);
                
            return response()->json('success');
        }
    }

    public function ajaxUpdate(Request $request)
    {
        if ($request->ajax()) {
            $data = array();

            parse_str($request['_form'],$output);
            
            foreach ($request['_options'] as $option) {
                $amount=$output['margin-amount'];
                $data = array(
                    'amount'=>$amount,
                    'margin'=>$output['marginamount'],
                    'updated_at'=>Carbon::now('America/New_York'),
                );

                Margin::where('product_id','=',$option)->update($data);
                
                $product = Product::find($option);

                if ($output['marginamount']=='Percent') {
                    $product->update(['p_newprice' => $product->p_retail-($product->p_retail*($amount/100))]);
                } else
                    $product->update(['p_newprice' => $amount]);
             }
            
            return response()->json('success');
        }
    }

}
