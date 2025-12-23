<?php

namespace App\Libs;

use App\Models\Product;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Http;
use setasign\Fpdi\PdfParser\StreamReader;
use App\Services\RotatableFpdi;
use Illuminate\Support\Str;
use App\Mail\GMailer;
use Imagick;
use Session;
use PDF;

// Swissmade
class PrintOrder {
    private function Header(&$pdf) {
        // Get the current page break margin
        $bMargin = $pdf::getBreakMargin();

        // Get current auto-page-break mode
        //$auto_page_break = $pdf::AutoPageBreak;

        // Disable auto-page-break
        $pdf::SetAutoPageBreak(false, 0);

        // Define the path to the image that you want to use as watermark.
        $img_file = 'assets/paid-in-full.png';

        // Render the image
        $pdf::Image($img_file, 0, 0, 223, 280, '', '', '', false, 300, '', false, false, 0);

        // Restore the auto-page-break status
        $pdf::SetAutoPageBreak(true, $bMargin);

        // Set the starting point for the page content
        //$pdf::setPageMark();


    }

    public function printLabel($pdfFile) {

        // Set the source file
        // 1. Determine the final URL
        $baseUrl = 'https://lilvp.com/images/fedexlabels/202512/';

        if (filter_var($pdfFile, FILTER_VALIDATE_URL)) {
            // It is already a full URL (e.g., https://...)
            $url = $pdfFile;
        } else {
            // It is just a tracking number.
            // Ensure it ends in .pdf
            if (!str_ends_with($pdfFile, '.pdf')) {
                $pdfFile .= '.pdf';
            }
            $url = $baseUrl . $pdfFile;
        }
        // 1. Download the file content into a variable

        $response = Http::get($url);

        if ($response->failed()) {
            abort(404, "Could not fetch label from URL");
        }

        $pdfContent = $response->body();

        // 2. Initialize FPDI
        $pdf = new RotatableFpdi();

        // 3. Create a StreamReader from the string content
        $stream = StreamReader::createByString($pdfContent);

        // 1. Initialize with 4x6 inch dimensions (approx 101.6mm x 152.4mm)
        $pdf = new RotatableFpdi();

        // DISABLE defaults that draw lines
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Get page count
        $pageCount = $pdf->setSourceFile($stream);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // 2. Add a Portrait Page (4in x 6in)
            // We force the size to standard 4x6 label (101.6mm x 152.4mm)
            $pdf->AddPage('P', [101.6, 152.4]);

            // 3. Rotate the "Canvas" 90 degrees around the center of the page
            // Center of 4x6 is roughly (50.8, 76.2)
            $pdf->Rotate(90, 50.8, 76.2);

            // 4. Place the template
            // Because we rotated the canvas, X and Y coordinates can be tricky.
            // Usually, centering the template on the rotated canvas works best.
            // The following logic centers the imported landscape label onto the portrait page.

            $pdf->useTemplate($templateId, -74, 15, 210);

            // EXPLANATION OF COORDINATES:
            // We are placing a 6-inch wide label into a 4-inch wide box that has been spun 90 degrees.
            // You may need to tweak the X/Y (-25.4, 25.4) slightly depending on your specific label margins.

            // 5. Reset Rotation for the next page
            $pdf->Rotate(0);
        }

        $pdf->Output($pdfFile, 'I');

        // return response()->download($outputFile);

    }

    public function printProductTag($ids) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set auto page breaks
        //$pdf::SetAutoPageBreak(TRUE, 30);

        // set image scale factor
        //$pdf::setImageScale(1);

        // define barcode style
        $style = array(
            'position' => '',
            'align' => '',
            //'stretch' => true,
            //'fitwidth' => false,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            //'fgcolor' => array(0,0,128),
            //'bgcolor' => array(255,255,128),
            //'text' => true,
            //'label' => 'CUSTOM LABEL',
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 0
        );

        // set font
        //$page_format = array();//'Rotate' => 270);
        // add a page


        $pdf::SetFont('helvetica', '', 7);

        $user_agent = getenv("HTTP_USER_AGENT");


            $os = "Mac";


        $products = Product::whereIn('id',explode(',',$ids))->get();

        if ($products->isEmpty()) {
            return;
        } else {

            foreach ($products as $product) {
                $pdf::AddPage();
                if ($product->p_comments!='Temp') {

                    $pdf::write2DBarcode("$product->id", 'QRCODE,L', 5.8, 2, 12, 12, $style, 'N');
                    $pdf::setXY(16.5,3);

                    $id = $product->id;
                } else {
                    $pdf::setXY(0,11);
                    $id = $product->p_reference;
                }

                ob_start();
                ?>
                <table>
                    <tr>
                        <td><div><?= $id ?></div></td>
                    </tr>
                    <tr>
                        <td><?= substr($product->p_serial,0,7) ?></td>
                    </tr>
                    <tr>
                        <td><?= 'B'.($product->p_box==1 ? 'Y' :'N') . 'P' . ($product->p_papers==1 ? 'Y' :'N') ?></td>
                    </tr>
                </table>

                <?php
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
                ob_start();

                $pdf::setXY(31,3);

                ?>
                <table>
                    <tr>
                        <td><div><?= priceToLetters($product->p_price); ?></div></td>
                    </tr>
                    <tr>
                        <td>$<?= number_format($product->p_retail,2) ?></td>
                    </tr>
                    <tr>
                        <td><?= $product->p_reference ?></td>
                    </tr>
                </table>

                <?php

                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
            }
        }

        $pdf::StopTransform();
        // $pdf::IncludeJS("print();");
        $pdfContent = $pdf::Output('example_048.pdf', 'I');

    }

    function printAppraisal($order,$output='') {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf::SetFont('helvetica', '', 12, '', true);
        // set header and footer fonts
        $pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT,true);
        $pdf::SetHeaderMargin(0);
        $pdf::SetFooterMargin(0);

        // remove default footer
        $pdf::setPrintFooter(false);
        $pdf::SetPrintFooter(false);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        // ---------------------------------------------------------
        // add a page
        $pdf::AddPage();

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf::setPrintHeader(false);

        // get the current page break margin
        $bMargin = $pdf::getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf::getAutoPageBreak();
        // disable auto-page-break
        $pdf::SetAutoPageBreak(false, 0);

        // set bacground image
        $img_file = 'assets/appraisal-template.jpg';
        $pdf::Image($img_file, 0, 0, 210, 300, '', '', '', false, 300, '', false, true, 0);
        // restore auto-page-break status
        $pdf::SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf::setPageMark();

        $pdf::SetXY(160, 65);
        $pdf::writeHTML($order->created_at->format('m-d-Y'), true, false, false, false, '');

        $fullname = $order->s_firstname . ' ' . $order->s_lastname;
        $pdf::SetXY(25, 102);
        $pdf::writeHTML($fullname , true, false, false, false, '');

        $countries = new \App\Libs\Countries;
        $country_s = ''; $country_b = '';
        $state_b = ''; $state_s = '';

        if ($order->b_country) {
            $country_b = $countries->getCountry($order->b_country);
        }

        if ($order->b_country) {
            if (!is_numeric($order->b_country)) {
                $country_b = $countries->getCountryBySortName($order->b_country);
            } else $country_b = $countries->getCountry($order->b_country);
        }
        if ($order->b_state) {
            $state_b = $countries->getStateCodeFromCountry($order->b_state);
        }

        $address = !empty($order->b_address1) ? $order->b_address1 . ', ' : '';
        $address .= !empty($order->b_address2) ? $order->b_address2 .', ' : '';
        $address .= !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.', ': $state_b . ' ' . $order->b_zip .', ';
        $address .= !empty($country_b) ? $country_b.' ' : '';

        $pdf::SetXY(35, 108);
        $pdf::writeHTML($address, true, false, false, false, '');

        $image_file = 'assets/logo-swissmade.jpg';
        $pdf::Image($image_file, 81, 17, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0);
        $pdf::SetXY(35, 108);
        $pdf::writeHTML($address, true, false, false, false, '');

        $phone = '212-840-8463';

        $pdf::SetXY(73, 33);
        $pdf::SetFont('helvetica', '', 10, '', true);

        $txt = "15 W 47th Street, Ste # 503<br>New York, NY 10036<br>$phone";
        $pdf::MultiCell(58, 55, $txt, 0, 'C', 0, 1, '', '', true,false,true);

        $pdf::SetFont('helvetica', '', 12, '', true);
        $noImage = 'images/no-image.jpg';
        $y = 138; $totalAllowed = 0;
        foreach ($order->products as $product) {
            $p_image = $product->images->toArray();

            if (!empty($p_image)) {
                if (file_exists(public_path().'/images/thumbs/'.$p_image[0]['location']))
                    $image='images/thumbs/'.$p_image[0]['location'];
                else $image = $noImage;
            } else $image = $noImage;


            $pdf::Image($image, 23, $y+1, 25, '', 'JPG', '', 'T', false, 300, '', false, false, 0);
            $pdf::SetFillColor(255, 255, 255);
            $pdf::MultiCell(111, 25, $product->title."<br><br>Reference: ".$product->p_reference."<br>Serial: ".$product->p_serial, 0, 'L', 0, 0, 56, $y, true, 0, TRUE, true, 26, "M", false);

            $retail = "$".number_format($product->p_retail,2);
            $pdf::MultiCell(26, 15, $retail, 0, 'L', 0, 0, 169, $y, true, 0, false, true, 30, "M", false);
            $pdf::setDrawColor(220,220,220);
            $pdf::Line(20,$y+30, 192, $y+30);
            $y += 32;
            $totalAllowed++;
            if ($totalAllowed == 3)
                break;
        }

        $filename = Str::slug("appraisal-$order->s_firstname-$order->s_lastname");
        PDF::Output(public_path().'/uploads/'.$filename.'.pdf', 'F');

        PDF::Output(public_path('/'.$filename.'.pdf', 'F'));
        // dd(public_path('/'.$filename.'.jpg'));
        $img = new Imagick();
        $img->setResolution(288,288);
        $img->readImage(public_path('/uploads/'.$filename.'.pdf'));
        $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        $img->setImageFormat( "jpg" );
        $img->writeImages(public_path('/uploads/'.$filename.'.jpg'));
        // header('Content-Type: image/jpeg');
        // echo $img;

        $img->clear();
        $img->destroy();
    }

    function print($orders,$output='') {

        // set document information
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        if (isset($orders['id']) == false) {
            $icount = 0;

            foreach ($orders as $order){
                $icount ++;
                //dd($orders->count());
                $this->_print($order,$pdf,$output,$icount,$orders);

            }
        } else
            return $this->_print($orders,$pdf,$output);
    }

    public function _print($order,$pdf=null,$output,$icount=0,$orders=null) {

        if ($pdf==null)
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $placedMethod = class_basename($order);
        // \Log::debug($placedMethod);

        $orderStatus='';$columns=0;
        $purchasedFrom = $order->purchased_from;

        PDF::setHeaderCallback(function($pdf) use($purchasedFrom){
            // Logo
            $pdf->SetFont('helvetica', 'T', 10);

            if ($purchasedFrom==0 || !$purchasedFrom) {
                $image_file = 'assets/logo-swissmade.jpg';

                $pdf->Image($image_file, 14, 10, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0);
            } else {
                $image_file = 'assets/logo-signaturetime.jpg';

                $pdf->Image($image_file, 14, 10, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0);
                //$pdf::SetY(17);
                //$pdf::SetFont('helvetica', 'T', 13);
                //$pdf::WriteHTML("<b><i>SIGNATURE TIME</i></b>", false, false, false, false, 'L');
            }
        });

        PDF::setFooterCallback(function($pdf) use ($orderStatus){
            $pdf->SetFont('helvetica', 'I', 8);

            // $pdf::Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
            // $pdf::WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');

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

        if ($placedMethod=='Order') {
            if ($order->method == 'On Memo') {
                $orderStatus = "Memo";
                $pdf::setXY($pdf::getPageWidth()-55,20);
            } elseif ($output == 'commercial') {
                $orderStatus = "Commercial Invoice";
                $pdf::setXY($pdf::getPageWidth()-85,30);
            } elseif ($output == 'packingslip') {
                $orderStatus = "Packing Slip";
                $pdf::setXY($pdf::getPageWidth()-70,20);
            } else {
                $orderStatus = "Invoice";
                $pdf::setXY($pdf::getPageWidth()-55,20);
            }
        } else
            $orderStatus = "Proforma";

        ob_start();
        if ($output != 'commercial') { ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold"><?= $order->status == 2 ? "Return" : $orderStatus?></div></td>
            </tr>
            <?php if ($output != 'packingslip') { ?>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $orderStatus . " No: $order->id " ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <table cellpadding="3">
                <tr>
                    <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold"><?= $orderStatus?></div></td>
                </tr>
                <tr>
                    <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= $order->created_at->format('F d, Y') ?></td>
                </tr>
            </table>
        <?php }

        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        $pdf::SetFont('helvetica', '', 10);

        $noImage = 'images/no-image.jpg';
        $phone = '212-840-8463';
        $email = 'info@swissmadecorp.com';
        if ($purchasedFrom==1) {
            $pdf::setY(22);
            $noImage = 'images/no-image-st.jpg';
            $email = 'signtimeny@gmail.com';
            $phone = '917-699-0831';
        } else {
            $noImage = 'images/no-image.jpg';
            $pdf::setY(25);
        }

        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>$phone<br>$email", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();
        $countries = new \App\Libs\Countries;
        $country_s = ''; $country_b = '';
        $state_b = ''; $state_s = '';

        if ($order->b_country) {
            if (!is_numeric($order->b_country)) {
                $country_s = $countries->getCountryBySortName($order->b_country);
            } else $country_b = $countries->getCountry($order->b_country);
        }

        if ($order->s_country) {
            if (!is_numeric($order->s_country)) {
                $country_s = $countries->getCountryBySortName($order->s_country);
            } else $country_s = $countries->getCountry($order->s_country);
        }
        if ($order->b_state) {
            $state_b = $countries->getStateCodeFromCountry($order->b_state);
        }
        if ($order->s_state) {
            $state_s = $countries->getStateCodeFromCountry($order->s_state);
        }

//        dd($state_s);
        $method='';

        if ($placedMethod=='Order') {
            if ($order->status==1) {
                $payment = "Paid";
                foreach ($order->payments as $payments)
                    $method .= $payments->ref.'<br>';

                //$method = substr($method,0,strlen($method)-4);
                $method = $order->method;
            } else {
                $payment = $orderStatus=='Memo' ? 'Memo' : PaymentsOptions()->get($order->payment_options);
                $method = $order->method;
            }
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
                <?php $channels = ['eBay','Chrono24','Website'] ?>
                <td style="width: 43%;">
                    <?php if ($output != 'commercial') { ?>
                    <?php if ( $order->customers()->first()->cgroup==1 || in_array($order->b_company,$channels)) {?>
                        <?php $s_fullname = $order->s_firstname . ' ' . $order->s_lastname ?>
                        <?= $s_fullname ?><br>
                        <?= !empty($order->s_company) && $s_fullname != $order->s_company ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': $state_s . ' ' . $order->s_zip .'<br>'?>
                        <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                    <?php } else { ?>
                    <?php $b_fullname = $order->b_firstname . ' ' . $order->b_lastname ?>
                    <?= $b_fullname ?><br>
                    <?= !empty($order->b_company) && $b_fullname != $order->b_company? $order->b_company . '<br>' : '' ?>
                    <?= !empty($order->b_address1) ? $order->b_address1 .'<br>' : ''?>
                    <?= !empty($order->b_address2) ? $order->b_address2 .'<br>' : '' ?>
                    <?= !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': $state_b . ' ' . $order->s_zip .'<br>'?>
                    <?= !empty($country_b) ? $country_b.'<br>' : '' ?>
                    <?= !empty($order->b_phone) ? $order->b_phone . '<br>' : '' ?>
                    <?php } ?>
                    <?php } else { ?>
                        <?php $s_fullname = $order->s_firstname . ' ' . $order->s_lastname ?>
                        <?= $s_fullname ?><br>
                        <?= !empty($order->s_company) && $s_fullname != $order->s_company ? $order->s_company . '<br>' : '' ?>
                        <?= !empty($order->s_address1) ? $order->s_address1 .'<br>' : ''?>
                        <?= !empty($order->s_address2) ? $order->s_address2 .'<br>' : '' ?>
                        <?= !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': $state_s . ' ' . $order->s_zip .'<br>'?>
                        <?= !empty($country_s) ? $country_s.'<br>' : '' ?>
                        <?= !empty($order->s_phone) ? $order->s_phone . '<br>' : '' ?>
                    <?php } ?>
                </td>
                <td style="width: 80px"></td>
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
            </tr>
        </table>

        <?php $choices = ["Chrono24","Website", "eBay"] ?>
        <?php if ($output != 'packingslip') { ?>
            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <?php if ($placedMethod=='Order') { ?>
                            <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> #</th>
                            <?php if ($output != 'commercial') { ?>
                            <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> Date</th>
                            <?php } ?>
                            <th style="border: 1px solid #ddd;color:#fff">Payment Method</th>
                            <th style="border: 1px solid #ddd;color:#fff">Terms</th>
                        <?php } else { ?>
                            <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> #</th>
                            <th style="border: 1px solid #ddd;color:#fff">PO Number</th>
                            <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> Date</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php if ($placedMethod=='Order') { ?>
                            <td style="border: 1px solid #ddd"><?= $order->po ? strtoupper($order->po) : $order->id ?></td>
                            <?php if ($output != 'commercial') { ?>
                                <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                            <?php } ?>
                            <td style="border: 1px solid #ddd"><?= $method ?></td>
                            <?php if ($output != 'commercial') { ?>
                                <?php if (in_array($order->b_company, $choices)) { ?>
                                    <td style="border: 1px solid #ddd">Paid</td>
                                <?php } else { ?>
                                    <td style="border: 1px solid #ddd"><?= $payment ?></td>
                                <?php } ?>
                            <?php } else { ?>
                                <td style="border: 1px solid #ddd">None</td>
                            <?php } ?>
                        <?php } else { ?>
                            <td style="border: 1px solid #ddd"><?= $order->id ?></td>
                            <td style="border: 1px solid #ddd"><?= $order->po ? strtoupper($order->po) : '' ?></td>
                            <td style="border: 1px solid #ddd"><?= $order->created_at->format('m-d-Y') ?></td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        <?php } ?>

        <?php

            if ($order->status == 1 || $order->status == 2) {
                $pdf::SetAlpha(.1);
                $pdf::StartTransform();
                $pdf::Rotate(20, 70, 110);
                if ($order->status == 1)
                    $pdf::Image('assets/paid-in-full-1.png', 30, 120, 120, 50, '', '', '', false, 300, '', false, false, 0);
                else
                    $pdf::Image('assets/return-in-full.png', 30, 120, 120, 50, '', '', '', false, 300, '', false, false, 0);
                $pdf::StopTransform();
                $pdf::SetAlpha(1);
            }

            $pdf::Ln();
            $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
            ob_start();
        ?>

            <table cellpadding="4" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <?php if ($placedMethod == 'Estimate') { ?>
                            <th width="100" style="border: 1px solid #ddd;color:#fff">Image</th>
                            <th width="170" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                            <th width="105" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                            <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                            <th style="border: 1px solid #ddd;color:#fff">Retail</th>
                            <th style="border: 1px solid #ddd;color:#fff">Price</th>
                        <?php } elseif ($output == 'packingslip') { ?>
                            <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                            <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                            <th width="500" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <?php } elseif ($output != 'commercial') { ?>
                            <th width="25" style="border: 1px solid #ddd;color:#fff">#</th>
                            <th width="69" style="border: 1px solid #ddd;color:#fff">Image</th>
                            <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                            <th width="185" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                            <th width="75" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                            <th width="30" style="border: 1px solid #ddd;color:#fff">Qty</th>
                            <th width="67" style="border: 1px solid #ddd;color:#fff">Retail</th>
                            <th width="67" style="border: 1px solid #ddd;color:#fff">Unit Price</th>
                            <th width="67" style="border: 1px solid #ddd;color:#fff">Price</th>
                        <?php }  else { ?>
                            <th width="330" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                            <th width="160" style="border: 1px solid #ddd;color:#fff">Model</th>
                            <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                            <th width="100" style="border: 1px solid #ddd;color:#fff">Price</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($order->products as $index => $product) {
                            if ($product->id == 1)
                                $condition = '';
                            else $condition = Conditions()->get($product->p_condition);
                            $p_image = $product->images->toArray();
                            if (!empty($p_image)) {
                                if (file_exists(public_path().'/images/thumbs/'.$p_image[0]['location']))
                                    $image='images/thumbs/'.$p_image[0]['location'];
                                else $image = $noImage;
                            } else $image = $noImage;
                        $box = 0;
                        if ($product->p_box)
                            $box = 1;

                            $style1="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0";
                        ?>
                    <tr nobr="true">
                        <?php if ($placedMethod == 'Order') { ?>
                            <?php $columns=6 ?>
                            <?php if ($output == 'packingslip') { ?>
                                <td width="90" style="<?= $style1 ?>;color:#fff;text-align: center">
                                    <img style="height: 50px" src="<?=$image  ?>" />
                                </td>
                                <td style="<?= $style1 ?>;" width="50"><?= ($product->p_status==4 ? '' : $product->id==1) ? 'Misc.' : $product->id ?></td>
                                <td style="border-left: 1px solid #d0d0d0;border-right: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="500"><?= !$product->pivot->product_name ? $product->title : $product->pivot->product_name ?> </td>

                            <?php } elseif ($output != 'commercial') { ?>
                                <td style="<?= $style1 ?>; text-align: center" width="25"><?=$index+1 ?></td>
                                <td width="69" style="<?= $style1 ?>;color:#fff;text-align: center">
                                    <img style="height: 50px" src="<?=$image ?>" />
                                </td>
                                <td style="<?= $style1 ?>;" width="50"><?= ($product->p_status==4 ? '' : $product->id==1) ? 'Misc.' : $product->id ?></td>
                                <td style="<?= $style1 ?>;" width="185"><?= $condition.' ' ?><?= !$product->pivot->product_name ? $product->title : $product->pivot->product_name ?> </td>
                                <td style="<?= $style1 ?>" width="75"><?= $product->pivot->serial ?></td>
                                <td style="<?= $style1 ?>" width="30"><?= $product->pivot->qty ?></td>
                                <td style="<?= $style1 ?>; text-align: right" width="67" ><?= $product->p_status==4 ? '' : number_format($product->p_retail,2)?></td>
                                <td style="border-right: 1px solid #d0d0d0;<?= $style1 ?>; text-align: right;background-color:#eee" width="67"><?= number_format($product->pivot->price,2)?></td>
                                <td style="border-right: 1px solid #d0d0d0;<?= $style1 ?>; text-align: right;background-color:#eee" width="67"><?= number_format($product->pivot->price*$product->pivot->qty,2)?></td>
                            <?php } else { ?>
                                <?php if (strtolower(Strap()->get($product->p_strap)) == 'leather') {
                                    $strap = '';
                                } else {
                                    $strap = ' with ' . strtolower(Strap()->get($product->p_strap)) . ' strap ';
                                }
                                ?>

                                <td style="<?= $style1 ?>;" width="330"><?= $product->pivot->product_name ?></td>
                                <td style="<?= $style1 ?>" width="160"><?= $product->p_reference ?></td>
                                <td style="<?= $style1 ?>" width="50"><?= $product->pivot->qty ?></td>
                                <td style="border-right: 1px solid #d0d0d0;<?= $style1 ?>; text-align: right;background-color:#eee" width="100"><?= number_format($product->pivot->price*$product->pivot->qty,2)?></td>
                            <?php } ?>
                        <?php } else { ?>
                                <?php $columns=5 ?>
                                <td width="100" style="<?= $style1 ?>;color:#fff;text-align: center">
                                    <img style="height: 50px" src="<?=$image ?>" />
                                </td>
                                <td style="<?= $style1 ?>;" width="170"><?= !$product->pivot->product_name ? $product->title : $product->pivot->product_name ?> </td>
                                <td style="<?= $style1 ?>" width="105"><?= ($product->p_status==4 || $product->id==1 ? '' : $placedMethod == 'Estimate') ? "Not disclosed" : $product->p_serial ?></td>
                                <td style="<?= $style1 ?>" width="50"><?= $product->pivot->qty ?></td>
                                <td style="<?= $style1 ?>; text-align: right" ><?= $product->p_status==4 ? '' : number_format($product->p_retail,2)?></td>
                                <td style="border-right: 1px solid #d0d0d0;<?= $style1 ?>; text-align: right;background-color:#eee"><?= number_format($product->pivot->price*$product->pivot->qty,2)?></td>
                        <?php } ?>
                    </tr>
                    <?php } ?>
                </tbody>

                <?php
                $total = $order->total;$is_partial=0;$partial = 0;
                if ($placedMethod=='Order') {
                    $is_partial = count($order->payments);

                    if ($order->payments) {
                        foreach ($order->payments as $payment) {
                            $total -= $payment->amount;
                            $partial += $payment->amount;
                        }
                    }
                }

                if ($output != 'packingslip') { ?>
                <tfoot>
                    <?php if ($output != 'commercial') { ?>
                        <tr>
                            <td style="text-align: right" colspan="<?= $columns ?>"><b>Sub Total</b></td>
                            <td colspan="3" style="text-align: right"><?= number_format($order->subtotal,2)?></td>
                        </tr>
                        <?php if ( $order->discount>0 ) {?>
                        <tr>
                            <td style="text-align: right" colspan="<?= $columns ?>"><b>Discount</b></td>
                            <td colspan="3" style="text-align: right;color:red">(<?= number_format($order->discount,2)?>)</td>
                        </tr>
                        <?php } ?>

                        <?php if ( $order->additional_fee!=0) {?>
                            <tr>
                                <td style="text-align: right" colspan="<?= $columns ?>"><b>Additional Fee</b></td>
                                <td colspan="3" style="text-align: right"><?=  number_format($order->additional_fee,2)?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <?php if ( $order->customers()->first()->cgroup==0) {?>
                                <td style="text-align: right" colspan="<?= $columns ?>"><b>Freight</b></td>
                                <td colspan="3" style="text-align: right"><?=  number_format($order->freight,2)?></td>
                            <?php } else {?>
                                <td style="text-align: right" colspan="<?= $columns ?>"><b>Tax</b></td>
                                <td colspan="3" style="text-align: right"><?=  number_format($order->taxable,3)?></td>
                            </tr>
                            <tr>
                                <td style="text-align: right" colspan="<?= $columns ?>"><b>Freight</b></td>
                                <td colspan="3" style="text-align: right"><?=  number_format($order->freight,2)?></td>
                            <?php } ?>
                        </tr>
                        <?php if($is_partial) { ?>
                            <tr>
                                <td style="text-align: right" colspan="<?= $columns ?>"><b>Paid Amount</b></td>
                                <td colspan="3" style="text-align: right;color:red">-<?= number_format($partial,2)?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td style="text-align: right" colspan="<?= $columns ?>"><b>Grand Total</b></td>
                            <td colspan="3" style="text-align: right">$<?= number_format($total,2)?></td>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td style="text-align: right" colspan="3"><b>Grand Total</b></td>
                            <td style="text-align: right;font-weight: bold">$<?= number_format($order->subtotal,2)?></td>
                        </tr>
                    <?php } ?>
                </tfoot>
                <?php } ?>
            </table>

            <?php if ($placedMethod != 'Estimate') { ?>
            <?php if ($order->payments->count()) { ?>
            Payments<br><br>
            <table cellpadding="4" style="border-collapse: collapse;">
            <tr><th style="<?= $style1 ?>;">Reference</th>
            <th style="<?= $style1 ?>;">Amount</th>
            <th style="<?= $style1 ?>;">Date</th></tr>
            <?php foreach ($order->payments as $payment)  {
                echo '<tr><td>'.$payment->ref.'</td>'.
                    '<td style="text-align: right">$'.number_format($payment->amount,2).'</td>'.
                    '<td style="text-align: right">'.$payment->created_at->format('m-d-Y').'</td></tr>';
                }
            ?>

            </table>
            <?php } ?>
            <?php } ?>

            <?php if ($placedMethod == 'Estimate') { ?>
            <br>
            <?php if ($order->comments) { ?>
                <b>Comments: <?= $order->comments ?></b>
            <?php } ?>
            <br>
            <br>
            To avoid unessessery fees the price quoted above is only if you pay via the wire transfer. <br>&nbsp;&nbsp;If you prefer to pay via the credit card, there will be a 3.5% credit card charge.
            <br><br>
            Our wire transfer information is:<br><br>

            <b>SWISS MADE CORP</b><br>
            15 West 47th Street<br>
            Suite 503<br>
            New York, NY 10036<br><br>

            <b>Bank of America</b><br>
            550 5th Avenue<br>
            New York, NY 10036<br>
            Routing #: 021000322<br>
            Account#: 483082594737<br>
            US Wire Code: 026009593<br>
            International Swift Code (IN US DOLLARS):  BOFAUS3N<br>
            <?php } ?>

        <?php

        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
            //$pdf::Ln();
            //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);

        if ($output == 'commercial') {
            $image_file = 'images/signature.jpg';
            $pdf::Image($image_file, 20, 200, 45, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $style = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

            $pdf::Line(15, 210, 80, 210, $style);
            $pdf::SetXY(40, 210);
            $pdf::writeHTML("Manager", true, false, false, false, '');
        }

        if($placedMethod == 'Order') {
            if ($output != 'packingslip') {
                $pdf::setY(255);
                $pdf::Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
                $pdf::WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');
            }
        } else {
            $pdf::Ln();
            $pdf::Write(0, "If you have any questions regarding this order, please contact us.", '', 0, 'C', true, 0, false, false, 0);
        }

        //Close and output PDF document
        if ($order->b_company != "Website") {
            $company = $order->b_company;
            $filename = str_replace([' ','/',"'","+"],'-',$order->b_company).'-'.$orderStatus.'-'.$order->id.'.pdf';
        } else {
            $company = $order->s_company;
            $filename = str_replace([' ','/',"'","+"],'-',$order->s_company).'-'.$orderStatus.'-'.$order->id.'.pdf';
        }

        if ($output=='emailmultiple') {
            PDF::Output(public_path().'/uploads/'.$filename, 'F');
            PDF::reset();
            return array($filename,$order,$purchasedFrom);
        }

        if ($output == 'email') {
            if ($order->email=='') {
                Session::flash('message', "Email was not specified. Please enter email and  try again!");
                return "admin/orders";
            }

            $data = array(
                'to' => $order->email,
                'company' => $company,
                'order_id' => $order->id,
                'filename'=>$filename,
                'purchasedFrom' => $purchasedFrom,
                'template' => 'emails.invoice',
                'subject' => 'Swiss Made Corp.',
                'from' => $email
            );

            PDF::Output(public_path().'/uploads/'.$filename, 'F');
            try {
                //Mail::to($order->email)->queue(new EmailCustomer($data));
                $gmailer = new GMailer($data);
                $gmailer->send();

                $order->emailed=1;
                $order->update();
                Session::flash('message', "Successfully emailed invoice!");
                //return "admin/orders";
                return array($filename,$order,$purchasedFrom);
            } catch (\Exception $e) {
                Session::flash('message', 'Caught exception: '. $e->getMessage());
                return "admin/orders";
            }
            //Mail::to('edba1970@yahoo.com')->queue(new EmailCustomer($data));


        } else {
            if ($orders) {
                if ($orders->count() == $icount) {
                    PDF::Output($filename, 'I');
                } else {
                // $pdf::AddPage();
                }
            } else {
                PDF::Output($filename, 'I');
            }
        }
    }
}