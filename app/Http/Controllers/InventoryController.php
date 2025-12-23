<?php

namespace App\Http\Controllers;

use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use DB;
use PDF;
use App\Models\Product;
use PayU\ApplePay\ApplePayDecodingServiceFactory;
use PayU\ApplePay\ApplePayValidator;
use PayU\ApplePay\Exception\DecodingFailedException;
use PayU\ApplePay\Exception\InvalidFormatException;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $applePayDecodingServiceFactory = new ApplePayDecodingServiceFactory();

        // $privateKey = "MIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQgJx9VVzR9qhNfQ1+HD5d9VIbT0S4NDughj5foXOs4siOhRANCAARmzU0WzzR1xboEucLtgaKg3Vi59YyQ9rdsFCeJyaHsSWCheo6WvpwoU8AUXtOGfRKQlrGFZLF8Rzqglk9CrCAJ";
        
        // $appleId = 'merchant.com.swissmadecorp';
        
        // $paymentData = '{"data":"XBgJ7sUJEKSTsszCZdh5F2g+0XAzndCBXjAmVhduBJJE33Rie+XUVA1mqFQlWelpoZQ0MgUKqvVpgl+ONEQaYdPq\/zcQxK8TpzsLUHJHyEIr\/PUiPWozCzsp4cKrd5IK7Vg4nIqqoxlYbuy8KfBl05o6Xd9EEqFjAUC7PYAt61NkJ5aqdzzTnJef\/wLO9\/W1sSfgpU\/BQYDXKm9KQAcshp2Gg2LuY2c9tkbkLRbnwNYCWrqdWlwif6X0SACXP55cbawxeVTuwKC6QpJhOTMUW4gTmIfcDqfRsl8zY0W+alQZTLa5QYOLCCZhcNm5gkDW\/25QjOgkn4psZ5V2NNNVxoe1iPKVRezBMoIITDHit4fm3JwabMSC9i7y3GCKxeLcl7w7gYXBP4IaVQ7msonY","signature":"MIAGCSqGSIb3DQEHAqCAMIACAQExDzANBglghkgBZQMEAgEFADCABgkqhkiG9w0BBwEAAKCAMIID4zCCA4igAwIBAgIITDBBSVGdVDYwCgYIKoZIzj0EAwIwejEuMCwGA1UEAwwlQXBwbGUgQXBwbGljYXRpb24gSW50ZWdyYXRpb24gQ0EgLSBHMzEmMCQGA1UECwwdQXBwbGUgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMB4XDTE5MDUxODAxMzI1N1oXDTI0MDUxNjAxMzI1N1owXzElMCMGA1UEAwwcZWNjLXNtcC1icm9rZXItc2lnbl9VQzQtUFJPRDEUMBIGA1UECwwLaU9TIFN5c3RlbXMxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEwhV37evWx7Ihj2jdcJChIY3HsL1vLCg9hGCV2Ur0pUEbg0IO2BHzQH6DMx8cVMP36zIg1rrV1O\/0komJPnwPE6OCAhEwggINMAwGA1UdEwEB\/wQCMAAwHwYDVR0jBBgwFoAUI\/JJxE+T5O8n5sT2KGw\/orv9LkswRQYIKwYBBQUHAQEEOTA3MDUGCCsGAQUFBzABhilodHRwOi8vb2NzcC5hcHBsZS5jb20vb2NzcDA0LWFwcGxlYWljYTMwMjCCAR0GA1UdIASCARQwggEQMIIBDAYJKoZIhvdjZAUBMIH+MIHDBggrBgEFBQcCAjCBtgyBs1JlbGlhbmNlIG9uIHRoaXMgY2VydGlmaWNhdGUgYnkgYW55IHBhcnR5IGFzc3VtZXMgYWNjZXB0YW5jZSBvZiB0aGUgdGhlbiBhcHBsaWNhYmxlIHN0YW5kYXJkIHRlcm1zIGFuZCBjb25kaXRpb25zIG9mIHVzZSwgY2VydGlmaWNhdGUgcG9saWN5IGFuZCBjZXJ0aWZpY2F0aW9uIHByYWN0aWNlIHN0YXRlbWVudHMuMDYGCCsGAQUFBwIBFipodHRwOi8vd3d3LmFwcGxlLmNvbS9jZXJ0aWZpY2F0ZWF1dGhvcml0eS8wNAYDVR0fBC0wKzApoCegJYYjaHR0cDovL2NybC5hcHBsZS5jb20vYXBwbGVhaWNhMy5jcmwwHQYDVR0OBBYEFJRX22\/VdIGGiYl2L35XhQfnm1gkMA4GA1UdDwEB\/wQEAwIHgDAPBgkqhkiG92NkBh0EAgUAMAoGCCqGSM49BAMCA0kAMEYCIQC+CVcf5x4ec1tV5a+stMcv60RfMBhSIsclEAK2Hr1vVQIhANGLNQpd1t1usXRgNbEess6Hz6Pmr2y9g4CJDcgs3apjMIIC7jCCAnWgAwIBAgIISW0vvzqY2pcwCgYIKoZIzj0EAwIwZzEbMBkGA1UEAwwSQXBwbGUgUm9vdCBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwHhcNMTQwNTA2MjM0NjMwWhcNMjkwNTA2MjM0NjMwWjB6MS4wLAYDVQQDDCVBcHBsZSBBcHBsaWNhdGlvbiBJbnRlZ3JhdGlvbiBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATwFxGEGddkhdUaXiWBB3bogKLv3nuuTeCN\/EuT4TNW1WZbNa4i0Jd2DSJOe7oI\/XYXzojLdrtmcL7I6CmE\/1RFo4H3MIH0MEYGCCsGAQUFBwEBBDowODA2BggrBgEFBQcwAYYqaHR0cDovL29jc3AuYXBwbGUuY29tL29jc3AwNC1hcHBsZXJvb3RjYWczMB0GA1UdDgQWBBQj8knET5Pk7yfmxPYobD+iu\/0uSzAPBgNVHRMBAf8EBTADAQH\/MB8GA1UdIwQYMBaAFLuw3qFYM4iapIqZ3r6966\/ayySrMDcGA1UdHwQwMC4wLKAqoCiGJmh0dHA6Ly9jcmwuYXBwbGUuY29tL2FwcGxlcm9vdGNhZzMuY3JsMA4GA1UdDwEB\/wQEAwIBBjAQBgoqhkiG92NkBgIOBAIFADAKBggqhkjOPQQDAgNnADBkAjA6z3KDURaZsYb7NcNWymK\/9Bft2Q91TaKOvvGcgV5Ct4n4mPebWZ+Y1UENj53pwv4CMDIt1UQhsKMFd2xd8zg7kGf9F3wsIW2WT8ZyaYISb1T4en0bmcubCYkhYQaZDwmSHQAAMYIBjTCCAYkCAQEwgYYwejEuMCwGA1UEAwwlQXBwbGUgQXBwbGljYXRpb24gSW50ZWdyYXRpb24gQ0EgLSBHMzEmMCQGA1UECwwdQXBwbGUgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTAghMMEFJUZ1UNjANBglghkgBZQMEAgEFAKCBlTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0yMjA0MjYxODU0MTJaMCoGCSqGSIb3DQEJNDEdMBswDQYJYIZIAWUDBAIBBQChCgYIKoZIzj0EAwIwLwYJKoZIhvcNAQkEMSIEILPdK+Vyzc\/86VMzEHPZz6UARkOxVb+hsG4S9FjYMQEFMAoGCCqGSM49BAMCBEgwRgIhAJFQ6RdE\/JzKhOylQQu0NLeCi1G3IpqOYbg8eyO7sY\/sAiEAuvN\/hYRULhxE6Z8rvw3\/la+ezRVZVIsZXa8xyG41eVoAAAAAAAA=","header":{"publicKeyHash":"OBDV2AXWSelp9POvEdus5T7EdwbRha85ZCfDU4H8SpA=","ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE2nQPIdSeT0DAfTO7gv3QzXBi0OiJz40h492vbFUpTPL55pn+910QPr0zXI0KAcVlL\/VxgQPtjcc69gnuiv11CA==","transactionId":"2493db2ea7bc52514fc6e6e418d68128f234331fb921d7b1f06ef75dd8ac74e3"},"version":"EC_v1"}';
        // $expirationTime = 315360000; // It should be changed in production to a reasonable value (a couple of minutes)

        // $rootCertificatePath = __DIR__ . '/API/apple_certs/certPem.pem';

        // $applePayDecodingServiceFactory = new ApplePayDecodingServiceFactory();
        // $applePayDecodingService = $applePayDecodingServiceFactory->make();
        // $applePayValidator = new ApplePayValidator();
        
        // $paymentData = json_decode($paymentData, true);
        
        // try {
        //     $applePayValidator->validatePaymentDataStructure($paymentData);
        //     $decodedToken = $applePayDecodingService->decode($privateKey, $appleId, $paymentData, $rootCertificatePath, $expirationTime);
        //     echo 'Decoded token is: '.PHP_EOL.PHP_EOL;
        //     var_dump($decodedToken);
        // } catch(DecodingFailedException $exception) {
        //     echo 'Decoding failed: '.PHP_EOL.PHP_EOL;
        //     echo $exception->getMessage();
        // } catch(InvalidFormatException $exception) {
        //     echo 'Invalid format: '.PHP_EOL.PHP_EOL;
        //     echo $exception->getMessage();
        // }

        // die;
        $m = \Schema::hasTable('table_temp_a');
        if ($m == false) {
            $products = Product::where('p_qty', '>', 0)
                ->where('p_status',"<>",4)
                ->where('group_id',0)
                ->get();

            $createTempTables = DB::unprepared(
                "
                    CREATE TABLE table_temp_a 
                        AS 
                        SELECT id
                        FROM products 
                        WHERE p_qty > 0 AND p_status <> 4 AND group_id = 0
                ;"
            );
         } else {
            $products = Product::with('images')
                ->join('table_temp_a','table_temp_a.id','=','products.id')
                ->get();
        
        }

        // $products = Product::where('p_qty', '>', 0)
        //         ->where('group_id',0)->get();
                
        return view('admin.inventory',['pagename'=>'Inventory Count','products'=>$products]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function refreshInventory() {
        \Schema::dropIfExists('table_temp_a');
        $createTempTables = DB::unprepared(
            "
                CREATE TABLE table_temp_a 
                    AS (
                    SELECT id
                    FROM products 
                    WHERE p_qty > 0 AND p_status <> 4 AND group_id = 0 
            );"
        );
        
        return back();
    }

    public function ajaxRemoveProduct(Request $request) {
        if ($request->ajax()) {
            $id = $request['id'];
            $inventory = DB::table('table_temp_a')->where('id',$id);
            
            if(count($inventory->get())){
                $product=Product::join('table_temp_a','table_temp_a.id','=','products.id')
                ->where('table_temp_a.id',$id)->first();

                $inventory->delete();
                return response()->json(array('error'=>'','cost'=>$product->p_price,'qty'=>$product->p_qty));
            } else 
                return response()->json(array('error'=>'No product found in this collection'));
        }
    }

    public function print() {
        $products=Product::join('table_temp_a','table_temp_a.id','=','products.id')
            ->orderBy('table_temp_a.id','asc')
            ->groupBy('table_temp_a.id')
            ->get();

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
        ob_start();
        ?>
        <table cellpadding="3" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color: #111;color:#fff">
                    <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                    <td width="80" style="border: 1px solid #ddd;color:#fff">Id</td>
                    <th width="360" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                    <th width="100" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $i=0;
                    foreach ($products as $product) { 
                    $i++;
                    $img = $product->images->first();
                    if ($img)
                        $img = $img->location;
                    else $img = '';

                    ?>
                <tr nobr="true">
                    <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                    <?php if ($img) {?>
                    <img style="width: 70px" src="images/thumbs/<?= $img ?>">
                    <?php } else { ?>
                    <img style="width: 70px" src="images/no-image.jpg">
                    <?php } ?>
                    </td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="80"><?= $product->id ?></td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="360"><?= $product->title ?> </td>
                    <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="100"><?= $product->p_serial ?></td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: right" colspan="3"><b>Total Qty</b></td>
                    <td style="text-align: right"><?= $i ?></td>
                </tr>           
            </tfoot>
        </table>
                        
        <?php
        
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        PDF::Output('items.pdf', 'I');
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
