<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;
use App\Models\Product;
use App\Models\Customer;
use App\Models\RepairProduct;
use Session;
use Elibyy\TCPDF\Facades\TCPDF;
use PDF;

class RepairsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $repairs = Repair::orderBy('id','desc')->get();
        return view('admin.repairs',['repairs'=>$repairs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.repairs.create',['pagename' => 'New Repair Ticket']);
    }

    public function print($id,$customer) {
        
        $repair=Repair::find($id);

        // set document information
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        PDF::setHeaderCallback(function($pdf){
            // Logo
            $image_file ='images/logo.jpg';
            $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        });

        PDF::setFooterCallback(function($pdf) {
            // Position at 15 mm from bottom
            $pdf->SetFont('helvetica', 'I', 8);
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

        $pdf::setXY($pdf::getPageWidth()-55,20);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold">Repair</div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $repair->created_at->format('m-d-Y')  ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica">Repair No <?= $repair->id ?></td>
            </tr>            
        </table>
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 

        $pdf::SetFont('helvetica', '', 10);
        $pdf::setY(23);
        
        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>212-840-8463<br>info@swissmadecorp.com", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();
        
        //$paymentoptions = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120'];
        
        ob_start();
        $technicians = array('Michael','Zalman','Rami','Chronostore','Gilmen','Simcha Barayev');
        
            if ($customer==1) {
        ?>
            <table cellpadding="1">
                <tr>
                    <td style="width: 43%;">
                        <?= $technicians[$repair->assigned_to] ?><br>
                    </td>
                </tr>
            </table>
        <?php
            } else { ?>
                <table cellpadding="1">
                <tr>
                    <td style="width: 43%;">
                        <?= $repair->firstname . ' ' . $repair->lastname ?><br>
                        <?= !empty($repair->company) ? $repair->company . '<br>' : '' ?>
                        <?= !empty($repair->address1) ? $repair->address1 .'<br>' : ''?>
                        <?= !empty($repair->phone) ? $repair->phone . '<br>' : '' ?>
                    </td>
                </tr>
            </table>

            <?php } ?>

        <table cellpadding="5">
            <thead>
                <tr style="background-color: #111;color:#fff">
                    <th style="border: 1px solid #ddd;color:#fff">Job #</th>
                    <th style="border: 1px solid #ddd;color:#fff">Job Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ddd"><?= $repair->id ?></td>
                    <td style="border: 1px solid #ddd"><?= $repair->created_at->format('m-d-Y') ?></td>
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
                        <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                        <th width="210" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <th width="75" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                        <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                        <?php if ($customer==1) { ?>
                            <th style="border: 1px solid #ddd;color:#fff" width="215">Jobs</th>
                        <?php } elseif ($customer==0) { ?>
                            <th width="125">Jobs</th>
                            <th width="90" style="border: 1px solid #ddd;color:#fff">Amount</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($repair->jobs as $job) { 
                        $p_image = $job->images_path;
                        if (!empty($p_image)) {
                            $image=$p_image;
                        } else $image = 'no-image.jpg';?>    
                
                    
                    <tr>
                        <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff">
                            <img style="width: 70px" src="<?= '/images/'.$image ?>" />
                        </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="210"><?= $job->product_name . '<br><br>' . $job->instructions?> </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="75"><?= $job->serial ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="50">1</td>

                        <?php if ($customer==0) { ?>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="125" >
                        <?php 
                            if ($job->job!="N;") {
                                foreach (unserialize($job->job) as $sjob) {
                                    echo $sjob.'<br>';
                                } 
                            }
                        ?></td>
                        <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="90"><?= number_format($job->amount,2) ?></td>
                        <?php } else { ?>
                            <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="215" >
                            <?php 
                            if ($job->job!="N;") {
                                foreach (unserialize($job->job) as $sjob) {
                                    echo $sjob.'<br>';
                                } 
                            }
                        ?></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>
                <?php if ($customer==0) { ?>
                <tfoot>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Total</b></td>
                        <td style="text-align: right"><?= number_format($repair->total,2)?></td>                        
                    </tr>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Freight</b></td>
                        <td style="text-align: right"><?= number_format($repair->freight,2)?></td>
                    </tr>
                    <tr>
                        <td style="text-align: right" colspan="5"><b>Grand Total</b></td>
                        <td style="text-align: right"><?= number_format($repair->freight+$repair->total,2)?></td>
                    </tr>                    
                </foot>
                <?php }  ?>
            </table>
                        
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
        ob_start();
        ?>
        <table cellpadding="5">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd;font-weight: bold">Comments</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #ddd"><?= $repair->customer_comments ?></td>
                </tr>
            </tbody>
        </table>

        <?php 
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, ''); 
        $pdf::Ln();
        $message="I agree that the approximate values and descriptions of the items listed above ";
        $message.="are correct, and I accept the following terms and conditions. Items are submitted ";
        $message.="to this store for repair only and we will not act as an insurer of the item listed above. ";
        $message.="We will repair or replace any article that is lost, stolen, or damaged due to our negligence, ";
        $message.="up to the value declared above.";

        $xc=100;
        $pdf::SetDrawColor(200, 200, 200);
        $pdf::Write(0, $message, '', 0, 'L', true, 0, false, false, 0);
        
        $y = $pdf::GetY();
        $pdf::Line(53, $y+15, 153, $pdf::GetY()+15);
        $pdf::writeHTMLCell(50, 10, 15,$y+11 , "Customer's Signature", 0, 0, false, true, 'L', true );

        //Close and output PDF document
        $filename = str_replace(' ','-',$repair->company).'-repair-'.$repair->id.'.pdf';
        PDF::Output($filename, 'I');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $paid = isset($request['paid']) && $request['paid'] == 'on' ? 1 : 0;
        $validator = \Validator::make($request->all(), [
            'company' => "required",
        ]);

        if ($validator->fails()) {
            return back()
                ->withInputs($request->all())
                ->withErrors($validator);
        }

        $customer = Customer::find($request['customer_id']);
        
        if (!$customer) {
            $data = array(
                'firstname' => allFirstWordsToUpper($request['firstname']),
                'lastname' => allFirstWordsToUpper($request['lastname']),
                'company' => allFirstWordsToUpper($request['company']),
                'address1' => allFirstWordsToUpper($request['address1']),
                'phone' => localize_us_number($request['phone']),
                'email' => localize_us_number($request['email']),
            );

            $customer=Customer::updateOrCreate(['company'=>$request['company']],$data);
        }
        
        $created_at = $request['created_at'];

        $repairArray = array(
            'customer_id' => $request['customer_id'],
            'firstname' => allFirstWordsToUpper($request['firstname']),
            'lastname' => allFirstWordsToUpper($request['lastname']),
            'company' => allFirstWordsToUpper($request['company']),
            'address1' => $request['address1'],
            'phone' => localize_us_number($request['phone']),
            'comments' => $request['comments'],
            'customer_comments' => $request['customer_comments'],
            'assigned_to' => $request['assigned_to'],
            'email' => $request['email'],
            'freight' => $request['freight']
        );

        //dd($repairArray);
        if ($created_at) {
            $repairArray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
            $repairArray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
        }

        $repair = Repair::create($repairArray);
        $keys = $request['product_name'];
        $total=0;

        foreach ($keys as $index => $key) {
            if ($key) {
                $product_id = $request['id'][$index];
                $qty = $request['qty'][$index];
                $price = $request['price'][$index];
                $serial = $request['serial'][$index];
                $product_name = $request['product_name'][$index];
                $instructions =$request['instructions'][$index];
                $cost =$request['cost'][$index];
                $image = $request['filename'][$index];

                $repairProduct=array(
                    'job_id' =>$repair->id,
                    'product_id' => $product_id,
                    'image_path' => $image,
                    'cost' => $cost,
                    'amount' => $price,
                    'serial' => $serial,
                    'product_name' => $product_name,
                    'instructions' => $instructions,
                    'job' => serialize($request["jobs_".($index+1)])
                );

                $total += $price;
                Repairproduct::create($repairProduct);

                //print_r($repairProduct);
            }
        }

        $repair->update([
            'total' => $total
        ]);

;
        //die;
        return redirect("admin/repairs");        
    }

    public function DestroyRepairProduct(Request $request) {
        if ($request->ajax()) {
            $id=$request['productid'];
            $repairProduct = RepairProduct::find($id);
            $repairProduct->delete();
                
            return \Response::json('success', 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RepairController  $repairController
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RepairController  $repairController
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $repair = Repair::find($id);
        if (!$repair)
            return response()->view('errors/admin-notfound',['id'=>$id],404);
        
        if ($repair->status == 1)
            $status = " - Paid";
        else $status = " - UnPaid";

        return view('admin.repairs.edit',['pagename' => 'Edit Repair Ticket'.$status,'repair'=>$repair]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RepairController  $repairController
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        
        $repair = Repair::find($id);
        $paid = isset($request['paid']) && $request['paid'] == 'on' ? 1 : 0;

        // $validator = \Validator::make($request->all(), [
        //     'company' => "required",
        // ]);

        // if ($validator->fails()) {
        //     return back()
        //         ->withInputs($request->all())
        //         ->withErrors($validator);
        // }

        $created_at = $request['created_at'];
        $repairArray = array(
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'company' => $request['company'],
            'address1' => $request['address1'],
            'phone' => localize_us_number($request['phone']),
            'comments' => $request['comments'],
            'customer_comments' => $request['customer_comments'],
            'assigned_to' => $request['assigned_to'],
            'email' => $request['email'],
            'freight' => $request['freight'],
            'status' => $paid
        );

        if ($created_at) {
            $repairArray['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
            $repairArray['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
        }
        
        $repair->update($repairArray); 
        $keys = $request['job_id'];
        $total = 0;
        
        foreach ($keys as $index => $key) {
            if ($key) {
                $job_id = $request['job_id'][$index];
                $price = $request['price'][$index];
                $cost = $request['cost'][$index];
                $product_name =$request['product_name'][$index];
                $filename =$request['filename'][$index];

                $product=RepairProduct::find($job_id);

                $repairProduct=array(
                    'amount' => $price,
                    'cost' => $cost,
                    'product_name' => $product_name,
                    'image_path'=>$filename
                );
                
                $total += $price;
                $product->update($repairProduct);
            }
        }

        $repair->update([
            'total' => $total
        ]);

        Session::flash('message', "Successfully updated repair ticket!");
        return redirect("admin/repairs/$id/edit");
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RepairController  $repairController
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $repair = Repair::find($id);
        //$repair->detach($repair)
        $repairProduct = RepairProduct::where('repair_id',$id);

        $repairProduct->delete();
        $repair->delete();
        
        Session::flash('message', "Successfully deleted product!");
        return redirect('admin/repairs');
    }
}
