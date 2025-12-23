<?php 

namespace App\Libs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalmartClass
{
    private $contentType = 'multipart/form-data';
    private $method = 'POST';
    private $accept = 'application/xml';
    
    private function getWalmartToken($getToken=false) {
        
        if (session()->has('walmart_access_token') && !$getToken) {
            $token = session()->get('walmart_access_token');
            $Expired = (time() - $token['time'])/60;
            
            if ($Expired>15) {
                $token = $this->getWalmartToken(true);
            }
            return $token;
        } else {
            //1594657952
            //dd('1594657952');
            
            $token = $this->generateTokenID();
            $current_timestamp = time(); // Produces something like 1552296328
            session()->put('walmart_access_token',['token'=>$token,'time'=>time()]);
            return ['token'=>$token,'time'=>time()];
        }

    }

    public function getFeedStatus($feedId) {
        $this->method = "GET";
        $this->contentType = 'application/xml';
        $requestUrl = "https://marketplace.walmartapis.com/v3/feeds/$feedId";
        $response = $this->submitToWalmart($requestUrl);

        return $response;
    }

    private function generateTokenID() {
        $headers = array();
    
        $headers[] = "WM_SVC.NAME: Walmart Marketplace";
        $headers[] = "WM_QOS.CORRELATION_ID: " . mt_rand();
        $headers[] = "Authorization: Basic " . \base64_encode(config('walmart.client_id').':'.config('walmart.client_secret'));
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Accept: application/json"; 

        $body = http_build_query(array('grant_type' => 'client_credentials'));
        $requestUrl = 'https://marketplace.walmartapis.com/v3/token';

        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body );
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $token = json_decode($result,true);

        return $token['access_token'];
    }

    public function updateInventory($skus) {
        $header = "<InventoryFeed xmlns=\"http://walmart.com/\">
            <InventoryHeader>
            <version>1.4</version>
            </InventoryHeader>
            ";
        $items = '';

        foreach ($skus as $sku) {
          $items.="<inventory>
            <sku>$sku</sku>
              <quantity>
                  <unit>EACH</unit>
                  <amount>1</amount>
              </quantity>
              </inventory>";
          }  
        
        $items = $header.$items . "
        </InventoryFeed>";

        $requestUrl = "https://marketplace.walmartapis.com/v3/inventory?feedType=inventory";
        $this->submitToWalmart($requestUrl,$items);
        //Log::info($items);
    }
    
    public function getActiveItems () {
        $this->method="GET";
        $this->contentType = 'application/xml';
        $requestUrl = "https://marketplace.walmartapis.com/v3/items?lifecycleStatus=ACTIVE&limit=50&publishedStatus=PUBLISHED";
        $xml = $this->submitToWalmart($requestUrl);

        //$file = fopen(__DIR__. '/file.xml','r');
        //$xml = fread($file,\filesize(__DIR__. '/file.xml'));
        
        $xml = str_replace('ns2:','',$xml);
        
        print_r($xml);die;
        //fclose($file);
        $xml_data = simplexml_load_string ($xml);

        dd($xml_data->ItemResponse);
        foreach ($xml_data->ItemResponse as $response){
            $data[] = (string) $response->sku;
        }
        dd($data);
        return $data;
    }

    public function retireItem ($skus) {
        $this->accept = "application/json";

        $header = array(
            "RetireItemHeader" => array(
                "version"=> "1.0"
            )
        );

        foreach ($skus as $sku) {
            $item[] = array('sku' => $sku);
        }  

        $items = array(
            "RetireItem" => $item
        );

        $header= array_merge($header, $items);

        $requestUrl = "https://marketplace.walmartapis.com/v3/feeds?feedType=RETIRE_ITEM";
        $this->submitToWalmart($requestUrl,$header);
    }

    public function submitToWalmart($requestUrl,$str_body='') {
        $token = $this->getWalmartToken();
        $headers = array();
 
        $API = \base64_encode(config('walmart.client_id').':'.config('walmart.client_secret'));
        $headers[] = "WM_SVC.NAME: Walmart Marketplace";
        $headers[] = "WM_QOS.CORRELATION_ID: " . mt_rand();
        $headers[] = "Authorization: Basic " . $API;
        $headers[] = "WM_CONSUMER.CHANNEL.TYPE: 0f3e4dd4-0514-4346-b39d-af0e00ea066d";
        $headers[] = 'WM_SEC.ACCESS_TOKEN: ' . $token['token'];
        if ($this->contentType)
            $headers[] = "Content-Type: $this->contentType";

        $headers[] = "Accept: " . $this->accept; 
        //return (print_r($headers,1));
        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        if ($this->method!="GET") {
            if (is_array($str_body))
                $str_body = json_encode($str_body);

            curl_setopt($ch, CURLOPT_POSTFIELDS, ['name' => $str_body] );
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
    
 //       return $result;
        $token = json_decode($result,true);
    
        //return $token;
    }
    
}