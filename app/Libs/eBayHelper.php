<?php

namespace App\Libs;

use App\Libs\eBayMain;
use App\Libs\eBaySession;
use Illuminate\Support\Facades\Config;

class eBayHelper {

    static function getTemplate($productTemp, $template_name) {
        $product = $productTemp->toArray();

        $templates = glob(base_path().'/resources/views/admin/ebay/templates/*.txt');
        $txt = '';

        if ($templates) {
            foreach ($templates as $filename) {
                if ( strpos($filename,$template_name) > 0 ) {

                    $fh = fopen($filename,'r');

                    while ($line = fgets($fh)) {
                    $txt .= $line;
                    }
                    fclose($fh);
                    break;
                }
            }

            preg_match_all("/%.*?%/",$txt,$matches);

            foreach ($matches[0] as $index => $match) {
                $colName = str_replace('%','',$match);

                if($colName=='p_retail') {
                    $txt = str_replace($match,'$'.number_format($product['p_retail'],2),$txt);
                } elseif ($colName=='category_name') {
                    $txt = str_replace($match,$productTemp->categories->category_name,$txt);
                    $txt = str_replace(" & "," and ",$txt);
                } elseif ($colName=='p_condition') {
                    if ($product[$colName]!=0) {
                        $condition = Conditions()->get($product[$colName]);
                        $txt = str_replace($match,$condition,$txt);
                    }
                } elseif ($colName=='p_material') {
                    if ($product[$colName]!=0) {
                        if ($product[$colName] == 1)
                            $material = MetalMaterial()->get($product[$colName]);
                        else
                            $material = Materials()->get($product[$colName]);

                        $txt = str_replace($match,$material,$txt);
                    }
                } elseif ($colName=='p_strap') {
                    if ($product[$colName]!=0) {
                        $strap = Strap()->get($product[$colName]);
                        $txt = str_replace($match,$strap,$txt);
                    }
                } elseif ($colName=='p_bezelmaterial') {
                    if ($product[$colName]!=0) {
                        $bezelmaterial = BezelMaterials()->get($product[$colName]);
                        $txt = str_replace($match,$bezelmaterial,$txt);
                    }
                } elseif ($colName=='p_casesize') {
                    if ($product[$colName]!=0) {
                        $txt = str_replace($match,$product[$colName],$txt);
                    }
                } elseif ($colName=='p_box') {
                    $box = $product[$colName] == 1 ? 'Yes' : 'No';
                    $txt = str_replace($match,$box,$txt);
                } elseif ($colName=='p_papers') {
                    $papers = $product[$colName] == 1 ? 'Yes' : 'No';
                    $txt = str_replace($match,$papers,$txt);
                } else
                    $txt = str_replace($match,$product[$colName],$txt);
            }

            // if ($productTemp->p_newprice < 1000)
            //     $price = round($productTemp->p_newprice+($productTemp->p_newprice*0.15)+500);
            // elseif ($productTemp->p_newprice > 1000 && $productTemp->p_newprice < 7500)
            //     $price = round($productTemp->p_newprice+($productTemp->p_newprice*0.065)+400);
            // else $price = round($productTemp->p_newprice+($productTemp->p_newprice*0.03)+300);

            $price = round($productTemp->p_newprice+($productTemp->p_newprice*0.029)+500);
            if (!$price)
                $price = round($productTemp->p_newprice+($productTemp->p_newprice*0.07));

            $cost = 'Cost: '.number_format($productTemp->p_price,0,'',',').' Web Price: '.number_format($productTemp->p_newprice,0,'',',').' Retail: '. number_format($productTemp->p_retail,0,'',',');
            $condition = $productTemp->p_condition == 3 ? 3000 : 1500;

            return [
                'content'=>$txt,
                'image'=> \URL::to('/') . '/images/'.$productTemp->images->first()->location,
                'price'=>$price,
                'cost'=>$cost,
                'condition'=>$condition,
                'StoreCategoryName'=>str_replace("&","and",$productTemp->categories->category_name)
            ];
        }
    }

    static function UploadPictures($uploadedimages) {
        //the token representing the eBay user to assign the call with
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $verb    = 'UploadSiteHostedPictures';   // the call being made:
        $version = 517;                          // eBay API version

        //$images = glob('server/php/files/*.*');
        $imageArray = array();
        //return $uploadedimages;
        foreach ($uploadedimages as $filename) {
            if ( preg_match( "/\.(png|jpg|gif)/i", $filename ) != 1 ) continue;
            $file = pathinfo ($filename);
            $picNameIn = $file['filename'];

            //$dirpath = (dirname(__FILE__). '/' .MultiUserURL ($username). 'img');
            //$file      = $dirpath.'/'.urldecode($file['basename']);       // image file to read and upload
            $file      =  base_path() . '/public/images/'.urldecode($file['basename']);       // image file to read and upload

            if (file_exists($file)) {
                $handle = fopen($file,'r');         // do a binary read of image
                $multiPartImageData = fread($handle,filesize($file));
                fclose($handle);

                ///Build the request XML request which is first part of multi-part POST
                $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
                $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
                $xmlReq .= "<Version>$version</Version>\n";
                $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";
                $xmlReq .= "<RequesterCredentials><eBayAuthToken>$AUTH_TOKEN</eBayAuthToken></RequesterCredentials>\n";
                $xmlReq .= '</' . $verb . 'Request>';


                $boundary = "MIME_boundary";
                $CRLF = "\r\n";

                // The complete POST consists of an XML request plus the binary image separated by boundaries
                $firstPart   = '';
                $firstPart  .= "--" . $boundary . $CRLF;
                $firstPart  .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
                $firstPart  .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
                $firstPart  .= $xmlReq;
                $firstPart  .= $CRLF;

                $secondPart  = "--" . $boundary . $CRLF;
                $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
                $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
                $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
                $secondPart .= $multiPartImageData;
                $secondPart .= $CRLF;
                $secondPart .= "--" . $boundary . "--" . $CRLF;

                $fullPost = $firstPart . $secondPart;

                // Create a new eBay session (defined below)
// \Log::debug(config('ebay.api_dev_name'). ' ' . config('ebay.api_app_name'). ' ' .  config('ebay.api_cert_name'). ' ' .  !config('ebay.flag_production'). ' ' .  $version. ' ' .  config('ebay.site_id'));
                $session = new eBaySession($AUTH_TOKEN, config('ebay.api_dev_name'), config('ebay.api_app_name'), config('ebay.api_cert_name'), !config('ebay.flag_production'), $version, config('ebay.site_id'), $verb, $boundary);

                $respXmlStr = $session->sendHttpRequest($fullPost);   // send multi-part request and get string XML response

                if(stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '')
                    return response()->json('<P>Error sending request');

                $respXmlObj = simplexml_load_string($respXmlStr);     // create SimpleXML object from string for easier parsing
                if ($respXmlObj->Ack == "Failure")
                    return array('error'=>'Error','response'=>'Error Code: '.$respXmlObj->Errors->ErrorCode. '<br>' . $respXmlObj->Errors->ShortMessage . '<br>' . $respXmlObj->Errors->LongMessage);
                    // need SimpleXML library loaded for this
                $ack        = $respXmlObj->Ack;
                $picNameOut = $respXmlObj->SiteHostedPictureDetails->PictureName;
                $picURL     = $respXmlObj->SiteHostedPictureDetails->FullURL;

                $imageArray[] = $picURL;
            }
        }

        return array('error'=>'Success','response'=>$imageArray);
    }
}