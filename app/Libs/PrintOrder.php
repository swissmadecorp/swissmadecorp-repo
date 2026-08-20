<?php

namespace App\Libs;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Models\Order;
use App\Mail\GMailer;
use Imagick;
use Session;
use PDF;

class PrintOrder {

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
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM-10);

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

    public function printOwed() {
        //$from = date('Y-m-d',strtotime("0 days"));
        $invoices = Order::where("status",0)
            //->where('created_at', '<=', $from)
            ->get();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->initializePDF($pdf,"Berd Vaye - Owed","P");

        PDF::setFooterCallback(function($pdf){
            // Position at 15 mm from bottom
            $pdf->SetY(-15);
            // Set font
            $pdf->SetFont('helvetica', 'I', 8);
                // Page number
            $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        });

        $pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-10, PDF_MARGIN_RIGHT);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM-10);
        $pdf::setPrintHeader(true);
        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::setPrintHeader(false);
        $borderRight='';$borderLeft="border-left: 1px solid #d0d0d0;";
        $borderTop=''; $borderBottom='border-bottom: 1px solid #d0d0d0';
        $borderColor = "border: 1px solid #ddd;color:#fff";

        ob_start();
        ?>
        <h2>Past Due Invoices / Memos</h2>
        <table id="invoices" cellpadding="2" width="100%">
        <thead>
            <tr style="background-color: #ccc; font-weight: bold">
                <th width="50" style="border: 1px solid #ccccc">Id</th>
                <th width="80" style="border: 1px solid #ccccc">Invoice</th>
                <th width="220" style="border: 1px solid #ccccc">Company</th>
                <th style="border: 1px solid #ccccc">PO</th>
                <th width="100" style="border: 1px solid #ccccc">Past Due</th>
                <th width="80" style="border: 1px solid #ccccc">Amount</th>
            </tr>
        </thead>
        <tbody>

        <?php $total=0; ?>
        <?php foreach ($invoices as $order) { ?>
            <tr>
                <td width="50" style="text-align:right" ><?= $order->id ?></td>
                <td width="80" ><?php if ($order->method=='On Memo') { ?>
On Memo
                <?php } elseif ($order->method=='Invoice') { ?>
Invoiced
                <?php  } else { ?>
                <span style="color: red">At Repair</span>
                <?php } ?>
                </td>
                <td width="220"><?= $order->b_company?></td>
                <td><?= $order->po ?></td>
                <td width="100">
                    <?php
                        $to = date('Y-m-d',time());

                        $dStart = new \DateTime($to);
                        $dEnd  = new \DateTime($order->created_at);
                        $dDiff = $dStart->diff($dEnd);

                    ?>

                    <?php if ($dDiff->days>365) { ?>
<?=$dDiff->y ?> years
                    <?php } elseif ($dDiff->days > 31) { ?>
<?=$dDiff->m ?> months
                    <?php } else { ?>
<?=$dDiff->days ?> days
                    <?php } ?>
                </td>
                <?php $subtotal = $order->total - $order->payments->sum('amount') ?>

                <?php foreach($order->orderReturns as $returns) { ?>
                    <?php $subtotal -= $returns->pivot->amount*$returns->pivot->qty; ?>
                <?php  } ?>

                <?php $total += $subtotal ?>
                <td width="80" style="text-align: right"><?= number_format($subtotal,2) ?></td>
            </tr>
            <?php  } ?>
            <tfoot >
                <tr style="background-color: #ccc; font-weight: bold">
                    <td width="556" colspan="5">Total Owned</td>
                    <td style="text-align: right">$<?= number_format($total,2) ?></td>
                </tr>
            </tfoot>
        </tbody>
    </table>

    </div> <?php

        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        //Close and output PDF document
        PDF::Output('sales.pdf', 'I');
    }

    public function print($order,$output='',$fromPage='',$comments='') {

        //$order=Order::find($id);
        $method = '';

        $placedMethod = class_basename($order);
        if ($placedMethod=='Order') {
            if ($order->status==1) {
                $payment = "Paid";
                foreach ($order->payments as $payments)
                    $method .= $payments->ref.'<br>';

                $method = substr($method,0,strlen($method)-4);
            } else {
                $payment = $method == 'Memo' ? 'Memo' : PaymentsOptions()->get($order->payment_options);
                $method = $order->method;
            }
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


        PDF::setHeaderCallback(function($pdf){
            // Logo
            $image_file = public_path('/images/berdvaye-logo-pdf.jpg');
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
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-10, PDF_MARGIN_RIGHT);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM-10);
        $pdf::setPrintHeader(true);
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
        $pdf::setPrintHeader(false);
        $orderStatus = '';
        $nonComm = 6; $tableWidths = [80,160,80,45];
        $borderRight='';$borderLeft="border-left: 1px solid #d0d0d0;";
        $borderTop=''; $borderBottom='border-bottom: 1px solid #d0d0d0';
        $borderColor = "border: 1px solid #ddd;color:#fff";

        if ($placedMethod=='Order') {

            if ($order->method == 'On Memo'){
                $orderStatus = "Memo";
                $pdf::setXY($pdf::getPageWidth()-55,20);

            } elseif ($output == 'commercial') {
                $nonComm = 3;
                $orderStatus = "Commercial Invoice";
                $pdf::setXY($pdf::getPageWidth()-85,20);
            } elseif ($output == 'packing_slip') {
                $orderStatus = "Packing Slip";
                $tableWidths = [80,360,150,45];
                $borderRight = "border-right: 1px solid #d0d0d0;";
            } elseif ($order->method == 'Repair') {
                $orderStatus = "Repair";
                $nonComm = 3;
                $pdf::setXY($pdf::getPageWidth()-85,20);
            } else {
                $orderStatus = "Invoice";
                $pdf::setXY($pdf::getPageWidth()-55,20);
            }
        } else {
            if ($fromPage=='cart')
                $orderStatus = "Invoice";
            else $orderStatus = "Proforma";
            $pdf::setXY($pdf::getPageWidth()-55,20);
        }

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

        //$pdf::writeHTMLCell(40, 10, $pdf::getPageWidth()-46, 23, '<div style="font-size:25px;color:#6b8dcb;font-weight:bold">'.$orderStatus.'</div>', 0, 0, 0, false, 'L', false);
        $pdf::SetFont('helvetica', '', 10);
        //$pdf::setXY($pdf::getPageWidth()-50,33);
        //$pdf::Write(0, date('F d, Y',time()), '', 0, 'L', true, 0, false, false, 0);
        $pdf::setY(24);
        $pdf::WriteHTML('610 Fifth Ave 2222<br>New York, NY 10020<br>833.237.3829<br>www.berdvaye.com', true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $countries = new \App\Libs\Countries;

        $country_b = $countries->getCountry($order->b_country);
        $country_s = $countries->getCountry($order->s_country);

        $state_b = $countries->getStateCodeFromCountry($order->b_state);
        $state_s = $countries->getStateCodeFromCountry($order->s_state);

        ob_start();
        ?>
            <table cellpadding="1">
                <tr>
                    <td style="width: 43%;background-color:#111;color:#fff">
                        <b>Bill To</b>:
                    </td>
                    <td style="width: 80px"></td>
                    <td style="width: 43%;background-color:#111;color:#fff">
                        <b>Ship To</b>:
                    </td>
                </tr>
                <tr>
                    <td style="width: 43%;">
                    <?php if ($placedMethod == 'Estimate' && $order->b_company == 'Website' ) { ?>
                        <?= $order->s_firstname . ' ' . $order->s_lastname ?><br>
                        <?= !empty($order->s_company) ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city : '' ?>
                        <?= !empty($state_s) && !empty($order->s_city) ?', ' . $state_s : $state_s ?>
                        <?= !empty($order->s_zip) ? ' ' . $order->s_zip.'<br>' : '' ?>
                        <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                    <?php } else { ?>
                        <?= $order->b_firstname . ' ' . $order->b_lastname ?><br>
                        <?= !empty($order->b_company) ? $order->b_company . '<br>' : '' ?>
                        <?= !empty($order->b_address1) ? $order->b_address1 .'<br>' : ''?>
                        <?= !empty($order->b_address2) ? $order->b_address2 .'<br>' : '' ?>
                        <?= !empty($order->b_city) ? $order->b_city : '' ?>
                        <?= !empty($state_b) && !empty($order->b_city) ? ', ' . $state_b : $state_b ?>
                        <?= !empty($order->b_zip) ? ' ' . $order->b_zip.'<br>' : '' ?>
                        <?= !empty($country_b) ? $country_b.'<br>' : '' ?>
                        <?= !empty($order->b_phone) ? $order->b_phone . '<br>' : '' ?>
                    <?php } ?>
                    </td>
                    <td style="width: 80px"></td>
                    <td style="width: 43%;">
                        <?= $order->s_firstname . ' ' . $order->s_lastname ?><br>
                        <?= !empty($order->s_company) ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city : '' ?>
                        <?= !empty($state_s) && !empty($order->s_city) ?', ' . $state_s : $state_s ?>
                        <?= !empty($order->s_zip) ? ' ' . $order->s_zip.'<br>' : '' ?>
                        <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                    </td>
                </tr>
            </table>

            <?php if ($placedMethod == 'Estimate') { ?>
                <table cellpadding="5">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="<?= $borderColor ?>">Proforma Number</th>
                        <th style="<?= $borderColor ?>">PO Number</th>
                        <th style="<?= $borderColor ?>">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= 'PR'.$order->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->po ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } elseif ($output == 'commercial') { ?>
            <table cellpadding="8">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="<?= $borderColor ?>"><?= $orderStatus ?></th>
                        <th style="<?= $borderColor ?>">Payment Method</th>
                        <th style="<?= $borderColor ?>">Terms</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= $order->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->method ?></td>
                        <td style="border: 1px solid #ddd"><?= $payment ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } elseif ($output != 'packing_slip') { ?>
                <table cellpadding="8">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="<?= $borderColor ?>"><?= $order->po ? "PO" : $orderStatus ?> #</th>
                        <th style="<?= $borderColor ?>">Order Date</th>
                        <th style="<?= $borderColor ?>">Payment Method</th>
                        <th style="<?= $borderColor ?>">Terms</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= $order->po ? strtoupper($order->po) : $order->id ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                        <td style="border: 1px solid #ddd"><?= $order->method ?></td>
                        <td style="border: 1px solid #ddd"><?= $payment ?></td>
                    </tr>
                </tbody>
            </table>
            <?php } ?>


            <?php
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
                ob_start();

                if ($placedMethod != 'Estimate') {
                    $grandTotal = 0; $totalPaid=0;$creditPaid=0;
                    $grandTotal = $order->total;

                    foreach($order->payments->all() as $payment) {
                        if ($payment->paid_method=="P")
                            $totalPaid += $payment->amount;
                        else $creditPaid += $payment->amount;

                        $grandTotal -= $payment->amount;
                    }

                }

            ?>

            <table cellpadding="2" style="border-collapse: collapse;">
            <?php if ($placedMethod != 'Estimate') { ?>
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <?php if ($output != 'commercial') { ?>
                            <?php if ($orderStatus!='Repair') { ?>
                                <th width="<?=$tableWidths[0]?>" style="<?= $borderColor ?>">Image</th>
                                <th width="<?=$tableWidths[1]?>" style="<?= $borderColor ?>">Product Name</th>
                                <th width="<?=$tableWidths[2]?>" style="<?= $borderColor ?>">Serial #</th>
                                <th width="<?=$tableWidths[3]?>" style="<?= $borderColor ?>">Qty</th>
                                <?php if ($output != 'packing_slip') { ?>
                                <th style="<?= $borderColor ?>">Retail</th>
                                <th style="<?= $borderColor ?>">Unit Price</th>
                                <th style="<?= $borderColor ?>">Total Price</th>
                                <?php } ?>
                            <?php } else { ?>
                                <th width="80" style="<?= $borderColor ?>">Image</th>
                                <th width="427" style="<?= $borderColor ?>">Product Name</th>
                                <th width="80" style="<?= $borderColor ?>">Serial #</th>
                                <th width="50" style="<?= $borderColor ?>">Qty</th>
                            <?php } ?>
                        <?php } else { ?>
                            <th width="340" style="<?= $borderColor ?>">Product Name</th>
                            <th width="90" style="<?= $borderColor ?>">Model</th>
                            <th width="50" style="<?= $borderColor ?>">Qty</th>
                            <th style="<?= $borderColor ?>">Unit Price</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->products as $product) { ?>
                    <?php
                    $foundProduct = 0;
                    $title = $product->pivot->product_name ? $product->pivot->product_name : $product->title();
                    if ($product->returns) {
                        foreach ($product->returns as $return) {
                            if ($order->id == $return->order_id) {
                                $foundProduct= $return->returns_id;
                                $grandTotal -= $return->amount;
                            }
                        }
                    }
                    ?>
                    <tr nobr="true">
                        <?php if ($output != 'commercial') { ?>
                            <?php if ($orderStatus!='Repair') { ?>
                                <td width="<?=$tableWidths[0]?>" style="<?=$borderLeft . $borderBottom?>;color:#fff;text-align:center">
                                <img style="width: 55px" src="<?= $product->image() ? $product->image() : 'images/no-image.jpg' ?>" />
                                </td>
                                <td style="<?=$borderLeft . $borderBottom?>;" width="<?=$tableWidths[1]?>">
                                    <?= $title?>
                                    <?php if ($foundProduct) { ?>
                                        <br><br><span style="color:red">(Returned)</span>
                                    <?php } ?>
                                    <?php if (!empty($product->pivot->memo_id)) { ?>
                                        <br><br><span style="color:red">(From Memo # <?= $product->pivot->memo_id ?>)</span>
                                    <?php } ?>
                                </td>
                                <td style="<?=$borderLeft . $borderBottom?>" width="<?=$tableWidths[2]?>">
                                    <?php if ($product->pivot->serial=='NONE' || $product->pivot->serial=='N/A')
                                            echo 'None';
                                        else
                                            echo $product->pivot->serial && $product->pivot->serial !='Backorder' ? $product->pivot->serial . ' / ' . $product->retail->heighest_serial : 'Backorder' ;
                                    ?>
                                </td>
                                <td style="<?=$borderRight . $borderLeft . $borderBottom?>;text-align:center" width="45"><?= $foundProduct ? '-' : '' ?><?=$product->pivot->qty ?></td>
                                <?php if ($output != 'packing_slip') { ?>
                                <td style="<?=$borderLeft . $borderBottom?>; text-align: right"><?= $product->pivot->retail ? number_format($product->pivot->retail,2) : number_format($product->retailvalue(),2)?></td>
                                <td style="<?=$borderLeft . $borderBottom?>; text-align: right"><?= $foundProduct ? '-' : '' ?><?= number_format($product->pivot->price,2)?></td>
                                <td style="<?=$borderRight . $borderLeft. $borderBottom?>; text-align: right;background-color:#eee"><?= number_format($product->pivot->qty*$product->pivot->price,2)?></td>
                                <?php } ?>
                            <?php } else { ?>
                                <td width="80" style="<?=$borderLeft . $borderBottom?>;color:#fff;text-align:center">
                                <img style="width: 50px" src="<?= $product->image() ? $product->image() : 'images/no-image.jpg' ?>" />
                                </td>
                                <td style="<?=$borderLeft . $borderBottom?>;" width="427"><?= $title?><?= $foundProduct ? '<br><br><span style="color:red">(Returned)</span>' : '' ?></td>
                                <td style="<?=$borderLeft . $borderBottom?>" width="80">
                                    <?php if ($product->pivot->serial=='NONE' || $product->pivot->serial=='N/A')
                                            echo 'None';
                                        else
                                            echo $product->pivot->serial ? $product->pivot->serial . ' / ' . $product->retail->heighest_serial : '' ;
                                    ?>
                                </td>
                                <td style="<?=$borderRight . $borderLeft . $borderBottom?>;text-align:center" width="50"><?= $foundProduct ? '-' : '' ?><?=$product->pivot->qty ?></td>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if ($product->p_model != "MISC") {?>
                            <td style="<?=$borderLeft. $borderBottom?>;line-height: 30px;" width="340"><?= strtolower($product->pivot->product_name) ?></td>
                            <?php } else { ?>
                            <td style="<?=$borderLeft. $borderBottom?>;line-height: 30px;" width="340"><?= $product->pivot->product_name ?></td>
                            <?php } ?>
                            <td style="<?=$borderLeft . $borderBottom?>;line-height: 30px;" width="90"><?= $product->p_model ?></td>
                            <td style="<?=$borderLeft . $borderBottom?>;line-height: 30px;" width="50"><?= $product->pivot->qty ?></td>
                            <td style="<?=$borderRight . $borderLeft . $borderBottom?>;line-height: 30px; text-align: right;background-color:#eee"><?= number_format($product->pivot->price,2)?></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>

                <?php if ($output!='packing_slip') { ?>
                <tfoot>
                    <tr>
                        <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Sub Total</b></td>
                        <td style="text-align: right"><?= number_format($order->subtotal,2)?></td>
                    </tr>
                    <?php if ( $order->discount>0 ) {?>
                    <tr>
                        <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Discount</b></td>
                        <td style="text-align: right;color:red">(<?= number_format($order->discount,2)?>)</td>
                    </tr>
                    <?php } ?>
                    <?php if ($output!='commercial') { ?>
                    <tr>
                        <?php if ( $order->customers()->first()->cgroup==1) {?>
                            <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Freight</b>
                            <?php if ($order->ship_method) { ?>
                                (<?= $order->ship_method ?>)
                            <?php } ?>
                            </td>
                            <td style="text-align: right"><?=  number_format($order->freight,2)?></td>
                        <?php } else {?>
                            <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Tax</b></td>
                            <td style="text-align: right"><?=  number_format($order->subtotal*($order->taxable/100),2)?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Freight</b>
                            <?php if ($order->ship_method) { ?>
                                (<?= $order->ship_method ?>)
                            <?php } ?>
                            </td>
                            <td style="text-align: right"><?=  number_format($order->freight,2)?></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                    <?php if  ($creditPaid) { ?>
                    <tr>
                    <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Credit Applied</b></td>
                        <td style="text-align: right;color:green">-<?= number_format($creditPaid,2) ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($totalPaid != 0 && $totalPaid != $order->total ) { ?>
                    <tr>
                        <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Partial Payment</b></td>
                        <td style="text-align: right;color:green">-<?= number_format($totalPaid,2) ?></td>
                    </tr>
                    <?php } elseif ($output != 'commercial') { ?>
                        <tr>
                        <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Total</b></td>
                        <td style="text-align: right">$<?= number_format($grandTotal,2)?></td>
                    </tr>
                    <?php } ?>

                    <?php if ($totalPaid != 0) { ?>
                    <tr>
                        <td style="text-align: right" colspan="<?= $nonComm ?>"><b>Grand Total</b></td>
                        <td style="text-align: right;color:red">$ <?= number_format($grandTotal,2) ?></td>
                    </tr>
                    <?php } ?>
                </tfoot>
                <?php } ?>
            <?php } else { ?>
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th width="100" style="<?= $borderColor ?>">Image</th>
                        <th width="200" style="<?= $borderColor ?>">Product Name</th>
                        <th width="75" style="<?= $borderColor ?>">Model</th>
                        <th width="50" style="<?= $borderColor ?>">Qty</th>
                        <th style="<?= $borderColor ?>">Retail</th>
                        <th style="<?= $borderColor ?>">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->products as $product) { ?>

                    <tr nobr="true">
                        <td width="100" valign="middle" style="text-align: center;<?=$borderLeft?><?=$borderBottom?>;color:#fff">
                        <img style="width: 50px" src="<?= $product->image() ? $product->image() : 'images/no-image.jpg' ?>" />
                        </td>
                        <td style="<?=$borderLeft?><?=$borderBottom?>" width="200"><?= $product->product_name  ?> </td>
                        <td style="<?=$borderLeft?><?=$borderBottom?>;vertical-align: bottom;" width="75"><?= $product->retail->p_model ?></td>
                        <td style="<?=$borderLeft?><?=$borderBottom?>" width="50"><?= $product->qty ?></td>
                        <td style="<?=$borderLeft?><?=$borderBottom?>; text-align: right"><?= number_format($product->retailvalue(),2)?></td>
                        <td style="border-right: 1px solid #d0d0d0;<?=$borderLeft?><?=$borderBottom?>; text-align: right;background-color:#eee"><?= number_format($product->price,2)?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>

                    <tr>
                        <td style="text-align: right" colspan="5"><b>Sub Total</b></td>
                        <td style="text-align: right"><?= number_format($order->subtotal,2)?></td>
                    </tr>
                    <?php if ($output!='commercial') { ?>
                        <tr>
                            <td style="text-align: right" colspan="5"><b>Freight</b></td>
                            <td style="text-align: right"><?=  number_format($order->freight,2)?></td>
                        </tr>
                        <?php if ($order->taxable) { ?>
                        <tr>
                            <td style="text-align: right" colspan="5"><b>Tax</b></td>
                            <td style="text-align: right"><?=  number_format(($order->taxable/100)*$order->subtotal,2)?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td style="text-align: right" colspan="5"><b>Grand Total</b></td>
                            <td style="text-align: right">$<?= number_format($order->total,2)?></td>
                        </tr>
                    <?php } ?>
                </tfoot>

            <?php } ?>

            </table>

            <?php if ($order->add_info ) { ?>
                <?= $order->add_info ?>
            <?php } ?>
            <?php if (!empty($order->getRelations()['payments']) && $order->payments->count()) { ?>
                Payments<br><br>
                <table cellpadding="4" style="border-collapse: collapse;">
                <tr><th style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;">Reference</th>
                <th style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;">Amount</th>
                <th style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;">Date</th></tr>
                <?php foreach ($order->payments as $payment)  {
                    echo '<tr><td>'.$payment->ref.'</td>'.
                        '<td style="text-align: right">$'.number_format($payment->amount,2). " (" . $payment->paid_method . ')</td>'.
                        '<td style="text-align: right">'.$payment->created_at->format('m-d-Y').'</td></tr>';
                    }
                ?>

                </table>
            <?php } ?>

        <?php


        if ($order->status == 1) {
            $pdf::SetAlpha(.1);
            $pdf::StartTransform();
            $pdf::Rotate(20, 70, 110);
            $pdf::Image('assets/paid-in-full-1.png', 30, 120, 120, 50, '', '', '', false, 300, '', false, false, 0);
            $pdf::StopTransform();
            $pdf::SetAlpha(1);
        }

        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        if ($output == 'commercial') {
            $image_file = 'images/signature.jpg';

            $pageHeight = PDF::getPageHeight()-38;
            // if (PDF::getY() > $pageHeight)
            //     $pdf::AddPage();

            $pdf::Image($image_file, 20, $pageHeight, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

            $pageHeight += 10;
            $pdf::Line(15, $pageHeight, 80, $pageHeight, $style);
            $pdf::SetXY(40, $pageHeight);
            $pdf::writeHTML("Manager", true, false, false, false, '');
        }

        $pdf::Ln();
        //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);
        if ($output != 'commercial') {
            $pdf::Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
            $pdf::WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');
        }

        $filename = str_replace([' ','/'],'-',$order->b_company).'-'.$orderStatus.'-'.$order->id;

        if ($output == 'email') {

            if ($order->email=='' && !$comments) {
                // Session::flash('message', "Email was not specified. Please enter email and  try again!");
                return ['', $order];
            }

            $data = array(
                'to' => $order->email,
                'company' => $order->b_company,
                'order_id' => $order->id,
                'filename'=>$filename.'.pdf',
                'template' => 'emails.invoice',
                'subject' => 'Thank you for your order!',
                'from_name' => 'Berdvaye Inc. '. $orderStatus,
                'orderStatus' => $orderStatus,
                'comments' => $comments
            );

            PDF::Output(public_path().'/uploads/'.$filename.'.pdf', 'F');

            // $img = new Imagick();
            // $img->setResolution(288,288);
            // $img->readImage(public_path().'/uploads/'.$filename.'.pdf');
            // $img->setImageFormat( "jpg" );
            // $img->setImageCompression(imagick::COMPRESSION_JPEG);
            // $img->setImageCompressionQuality(70);

            // $img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);
            // $img->writeImage(public_path().'/uploads/'.$filename.'.jpg');
            // $img->clear();
            // $img->destroy();

            //Mail::to($order->email)->queue(new EmailCustomer($data));

            $gmail = new GMailer($data);
            $gmail->send();

            Session::flash('message', "Successfully emailed invoice!");
            unlink(public_path().'/uploads/'.$filename.'.pdf');
            //unlink(public_path().'/uploads/'.$filename.'.jpg');
            return [public_path().'/uploads/'.$filename.'.pdf',$order];
        } elseif ($output == "sendText") {
            \Log::info('Sending text message for order: ' . $order->id);
            PDF::Output(public_path().'/uploads/'.$filename.'.pdf', 'F');
            return [public_path().'/uploads/'.$filename.'.pdf',$order];

        } elseif ($output=='Invoice_'.$order->id) {
            //Close and output PDF document
            PDF::Output(public_path('/'.$filename.'.pdf', 'F'));
            $img = new Imagick();
            $img->setResolution(288,288);
            $img->readImage(public_path('/'.$filename.'.pdf'));
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

            $img->setImageFormat( "jpg" );
            header('Content-Type: image/jpeg');
            echo $img;
        } else
            //Close and output PDF document
            PDF::Output($filename.'.pdf', 'I');
    }

}