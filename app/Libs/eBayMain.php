<?php

namespace App\Libs;

use App\Models\EbaySettings;
use Illuminate\Support\Facades\Config;

class eBayMain
{
	private $requestToken;

	public function getToken() {
        $token_ID = '';
        //if ()
        //customer_id: 65225528 username: mrdiamondusa password mrdiamondusa123*

        $ebaySettings = EbaySettings::first();

        if ($ebaySettings->token) {
            $token_ID = $ebaySettings->token;
        } else {

            $txt = '';
            $fh = fopen(base_path().'/resources/views/admin/ebay/config/my.token','r');
            while ($line = fgets($fh)) {
              $txt .= $line;
            }
            fclose($fh);
            $token_ID = $txt;

        }

        return $token_ID;
	}

	public function sendHeaders ($xmlRequest,$API_CALL_NAME,$get_response="", $version=1349) {

        //ini_set('max_execution_time', 300);

        $headers = array(
        'X-EBAY-API-SITEID:'.config('ebay.site_id'),
        'X-EBAY-API-CALL-NAME:'.$API_CALL_NAME,
        'X-EBAY-API-SESSION-CERTIFICATE: '.config('ebay.api_dev_name').";".config('ebay.api_app_name').";".config('ebay.api_cert_name'),
        'X-EBAY-API-RESPONSE-ENCODING:XML',
        'X-EBAY-API-REQUEST-ENCODING:XML',
        'X-EBAY-API-COMPATIBILITY-LEVEL:' . $version,
        'X-EBAY-API-DEV-NAME:' . config('ebay.api_dev_name'),
        'X-EBAY-API-APP-NAME:' . config('ebay.api_app_name'),
        'X-EBAY-API-CERT-NAME:' . config('ebay.api_cert_name'),
        'Content-Type: text/xml;charset=utf-8'
        );

        // initialize our curl session

        $session = curl_init('https://api.'.(config('ebay.flag_production')==true ? '' : 'sandbox.').'ebay.com/ws/api.dll');

        // set our curl options with the XML request
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);

        // execute the curl request
        $responseXML = curl_exec($session);
        \Log::info($responseXML);

        // close the curl session
        curl_close($session);

        $xml = simplexml_load_string($responseXML, "SimpleXMLElement", LIBXML_NOCDATA);

        $namespaces = $xml->getNamespaces(true);

        // Navigate using the namespace
        $ack = (string) $xml->children($namespaces[''])->Ack;
        $errors = $xml->Errors;

        if ($ack == "Success") {
            return $xml;
        } elseif ($errors) {
            // Extract all <Errors> elements


            // Loop through errors and filter by ErrorCode
            $ErrorMessage = "";
            foreach ($errors as $error) {
                $errorCode = (string) $error->ErrorCode;

                // Check if the error code matches 21919067
                if ($errorCode === '21919067') {
                    $errorParameter = '';
                    foreach ($error->ErrorParameters as $param) {
                        $paramID = (string) $param['ParamID'];
                        if ($paramID == 1)
                            $errorParameter = (string) $param->Value;
                    }
                    return ["ErrorCode"=> '21919067', "ItemId" => $errorParameter, "ErrorMessage"=>"Listing violates the Duplicate Listing policy. It looks like this listing is for an item you already have on eBay"];
                } elseif ($errorCode === "21919136" || $errorCode === "21919137") {
                    return ["ErrorCode"=> '21919136', "ItemId" => null, "ErrorMessage"=>"Photo is too small or the resolution for provided picture(s) does not meet eBay's Picture Policy requirements. Must at least be 500x500"];
                }
            }
        } else {
            return $ack;
        }

        if ($get_response != "XML") {
            return $responseXML;
        }
    }
}