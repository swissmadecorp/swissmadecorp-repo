<?php

namespace App\Jobs;

use DB;
use App\Libs\eBayMain;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use App\Libs\eBayHelper;
use App\Models\EbaySettings;
use App\Models\EbayListing;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutomateEbayPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $uniqueFor = 3600;
    public $tries = 5;
    protected $productIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->productIds = $request['ids'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('AutomateEbayPost execution started.', [
            'product_ids' => $this->productIds,
            'revision' => $this->isRevision(),
        ]);

        try {
            $this->eBayItemPostingDispatcher();
        } catch (\Throwable $exception) {
            \Log::error('AutomateEbayPost execution failed.', [
                'product_ids' => $this->productIds,
                'revision' => $this->isRevision(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function getMyEbaySellingInfo() {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $xmlRequest = <<<EOT
        <?xml version="1.0" encoding="utf-8"?>
        <GetMyeBaySellingRequest xmlns="urn:ebay:apis:eBLBaseComponents">
          <RequesterCredentials>
            <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
          </RequesterCredentials>
          <DetailLevel>ReturnAll</DetailLevel>
        </GetMyeBaySellingRequest>
        EOT;

        $response = $ebayMain->sendHeaders($xmlRequest,'GetMyeBaySelling');
    }

    private function InputToSpan($descriptionTemplate) {
        $descriptionTemplate = str_replace(['"""',"\r","\n","\t"],"",$descriptionTemplate );

        $DOM = new \DOMDocument();
        $DOM->loadHTML($descriptionTemplate);
        $rows = $DOM->getElementsByTagName("tr");
        $tables = "";

        for ($i = 0; $i < $rows->length; $i++) {
            $cols = $rows->item($i)->getElementsbyTagName("td");
            $inputs = $cols->item(0)->getElementsbyTagName("input");
            if ($inputs->length>0) {
                $categoryName = $inputs->item(0)->getAttribute("value");

                $inputs = $cols->item(1)->getElementsbyTagName("input");
                $categoryValue = $inputs->item(0)->getAttribute("value");
                $colSpan = "";

                if ($categoryValue == "x") {
                    $colSpan = ' colspan="2" class="head"';
                    $categoryValue = "";
                }


                if ($categoryValue != "$0.00" && strpos($categoryValue,"%") === false )
                    $tables .= "<tr><td$colSpan>".$categoryName."</td><td>".$categoryValue."</td></tr>";

            }

            // else
            // {
            //     dd($tables);
            //     for ($j = 0; $j < $cols->length; $j++) {
            //         echo $cols->item($j)->nodeValue, "\t";
            //         // you can also use DOMElement::textContent
            //         // echo $cols->item($j)->textContent, "\t";
            //     }
            // }
            // echo "\n";
        }

        return $tables;
    }

    protected function isRevision(): bool
    {
        return false;
    }

    protected function eBayItemPostingDispatcher() {
        $products = Product::whereIn('id', $this->productIds)->get();


        foreach ($products as $product) {
            $product_id = $product->id;
            $ebayListing = null;
            $isRevision = $this->isRevision();

            if ($isRevision) {
                $ebayListing = EbayListing::where('product_id', $product_id)->first();

                if (!$ebayListing || empty($ebayListing->listitem)) {
                    \Log::info("Product ID# {$product_id} has no stored eBay ItemID; revising by SKU.");
                }
            }

            $itemSpecifics = [];
            // $title = $product->p_longdescription;

            $title = $product->title;
            if ( strpos ( $title, "&" ) ) $title = str_replace ("&", "and", $title);
            if (strlen($title)>80) {
                $title = str_replace (["Yellow","Steel", "Rose", "Men's","Women's"], "", $title);
                $title = str_replace ("  ", " ", $title);
                if (strlen($title)>80) {
                    $title = str_replace (["Pre-owned","New"], " ", $title);
                }
            }
            //\Log::debug("title - ".strlen($title));

            // $box = $product->p_box;
            // $papers = $product->p_papers;

            // if ($box && $papers)
            //     $box = 'comes with box and papers';
            // elseif (!$box && !$papers)
            //     $box = 'does not come with box and papers';
            // elseif ($box && !$papers)
            //     $box = 'comes with box and no papers';
            // elseif (!$box && $papers)
            //     $box = 'does not come with box but comes with papers';

            $warranty = ($product->p_condition == 1 || $product->p_condition == 1) ? ' three years' : ' one year';
            $warrantyText = "<p>Swiss Made Corp. proudly offers a comprehensive $warranty ";
            $warrantyText .= "warranty on all mechanical aspects of our exceptional watches. This warranty underscores our commitment to precision  ";
            $warrantyText .= "craftsmanship and uncompromising quality. In the unlikely event of any mechanical issues within the first $warranty of ownership, ";
            $warrantyText .= "customers can trust that Swiss Made Corp. will provide swift and reliable assistance, ensuring our timepiece continues to deliver  ";
            $warrantyText .= "exceptional performance and lasting satisfaction. With Swiss Made Corp.'s dedication to excellence, customers can enjoy peace of mind ";
            $warrantyText .= "knowing their investment is protected by a warranty that reflects the brand's renowned Swiss watchmaking heritage.<p>";

            $productTemp = eBayHelper::getTemplate($product,"Watches");
            $productReturnInfo = '<p>'.$product->p_longdescription .'</p>'. $warrantyText;

            $content = view("/admin/ebay/templates/template");

            $product_content = $productTemp['content'];
            $table = $this->InputToSpan($product_content);
            //dd('asdf');
            $descriptionTemplate = str_replace("<!-- prodtitle -->", $product->title, $content);
            $descriptionTemplate = str_replace("<!-- production -->",$table, $descriptionTemplate);
            $content = str_replace("<!-- description -->",$productReturnInfo,$descriptionTemplate);

            $description = str_replace("%mainImage%",$productTemp['image'],$content);

            //print_r($description);die;
            //return "";
            if ($product->p_newprice < 1000)
                $price = round($product->p_newprice+($product->p_newprice*0.15)+500);
            elseif ($product->p_newprice > 1000 && $product->p_newprice < 7500)
                $price = round($product->p_newprice+($product->p_newprice*0.065)+400);
            else
                $price = round($product->p_newprice+($product->p_newprice*0.03)+300);

            $price = number_format($price, 2, '.', '');

            $CatId = 31387; // Wristwatches category
            $specifics = DB::table('ebay_specifics')
            ->select(DB::raw('*, (SELECT COUNT(0) FROM specifics WHERE specifics.id=ebay_specifics.id) t'))
            ->where('CategoryID',$CatId)
            ->get();

            //$specifics ='';
            foreach($specifics as $item) {
                $catName =  $item->CategoryName;
                //$catName = strlen($catName)>25 ? substr($catName,0,25).'...' : $catName;
                $txtValue = "";

                $linkedCat = $item->LinkedCategory;
                if ($linkedCat) {
                    if (strpos($linkedCat,'%')!==false) {
                        $linkedCat = str_replace('%','',$linkedCat);
                        switch ($linkedCat) {
                            case 'brand_name':
                                $itemSpecifics[]= [$catName, str_replace("&","and",$product->categories->category_name)];
                                break;
                            case 'p_material':
                                if ($product->group_id == 1)
                                    $itemSpecifics[]= [$catName, MetalMaterial()->get($product->p_material)];
                                else {
                                    $txtValue=Materials()->get($product->p_material);
                                    if (strpos($txtValue,'18K') !== false ) {
                                        $txtValue = str_replace("18K ","",$txtValue);
                                        $metalPurity = "18k";
                                    }

                                    $itemSpecifics[]= [$catName, $txtValue];
                                }
                                break;
                            case 'p_strap':
                                $itemSpecifics[]= [$catName, Strap()->get($product->p_strap)];
                                break;
                            case 'p_box':
                                if ($product->p_box == 1)
                                    $txtValue='Yes';
                                else $txtValue='No';
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'p_dial_style':
                                if ($product->p_dial_style > 0) {
                                    $txtValue = DialStyle()->get($product->p_dial_style);
                                    if ($product->p_dial_style < 5)
                                        $itemSpecifics[]= [$catName, $txtValue];
                                    else $itemSpecifics[]= ["Display", $txtValue];
                                }
                                break;
                            case 'water_resistance':
                                $meters = substr($product->water_resistance,0,strpos($product->water_resistance, " "));
                                if ($meters)
                                    $txtValue = $meters . " m " . "(". substr($meters,0,strlen($meters)-1) ." ATM)";
                                //\Log::debug($meters);

                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'p_bezelmaterial':
                                if (strpos(BezelMaterials()->get($product->p_bezelmaterial),'18K') !== false ) {
                                    $txtValue = str_replace("18K ","",Materials()->get($product->p_bezelmaterial));
                                }
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'p_papers':
                                if ($product->p_papers == 1)
                                    $txtValue='Yes';
                                else $txtValue='No';
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'movement':
                                $txtValue = Movement()->get($product->movement);
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'p_clasp':
                                $txtValue = Clasps()->get($product->p_clasp);
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                            case 'p_bezelmaterial':
                                $txtValue = BezelMaterials()->get($product->p_bezelmaterial);
                                $itemSpecifics[]= [$catName, $txtValue];
                                break;
                        default:
                            $txtValue=$product->$linkedCat;
                            $itemSpecifics[]= [$catName, $txtValue];
                            break;
                        }
                    } else {
                        $txtValue=$item->LinkedCategory;
                        $itemSpecifics[]= [$catName, $txtValue];
                    }
            } else $txtValue='';
            }

            $specifics = "";
            //dd($itemSpecifics);
            foreach ($itemSpecifics as $value) {
                if ($value[1]) {
                    $specifics .= "<NameValueList>";
                    $specifics .= "<Name>".str_replace("_"," ",$value[0])."</Name>";
                    if (strpos($value[1],',') !== false) {
                        $values = array();

                        $values = explode(',',$value[1]);
                        foreach ($values as $v) $specifics .= "<Value>$v</Value>";

                    } else $specifics .= "<Value>$value[1]</Value>";
                    $specifics .= "</NameValueList>";
                }
            }

            $specifics = str_replace(" & ", " &amp; ", $specifics);

            $ebayMain = new eBayMain;
            $AUTH_TOKEN = $ebayMain->getToken();

            if (empty($AUTH_TOKEN))
                die ("Your eBay Account is not linked with eBay Tool. You must authorize eBay Tool to be linked to your eBay account. For more information, please contact the software developer.");

            // Create unique id for adding item to prevent duplicate adds
            $condition = $product->condition;
            if ($condition == "Unworn" || $condition == "New") {
                $itemCondition = 1500;
                $conditionDescription = "New and unworn watch. Please see description";
            } else {
                $itemCondition = 3000;
                $conditionDescription = "Pre-owned watch. Please see description";
            }

            $conditionDescription = "We guarantee that every watch we sell is 100% authentic and that ";
            $conditionDescription .= "the serial number on each watch has never been altered or modified ";
            $conditionDescription .= "in any way. Every watch manufacturer imprints a serial number on each ";
            $conditionDescription .= "of their watches for identification purposes. We guarantee that each ";
            $conditionDescription .= "watch we sell bears the original manufacturer's serial number intact and ";
            $conditionDescription .= "without any alterations. Since we are not an authorized dealer for the brands ";
            $conditionDescription .= " we carry, unless otherwise stated, we can offer substantially greater discounts ";
            $conditionDescription .= "than authorized retailers. We only source our watches from authorized dealers ";
            $conditionDescription .= "and the most trusted suppliers in the industry. We do not source our watches ";
            $conditionDescription .= "through unreliable or questionable sources. ";

            $uuid = md5(uniqid());
            $addCatID = $CatId;
            $listingType = "FixedPriceItem";
            $duration = "GTC";
            $quantity = 1;
            $imageURLs = '';
            $storeCategoryID = '';
            $condition = $itemCondition;
            $conditionDescription = $conditionDescription;
            if ($isRevision)
                $requestType = $listingType == 'FixedPriceItem' ? "ReviseFixedPriceItem" : "ReviseItem";
            elseif ($listingType == 'FixedPriceItem')
                $requestType = "AddFixedPriceItem";
            else
                $requestType = "AddItem";

            $ebaySettings = EbaySettings::first();
            if ($ebaySettings) {
                $settings = array(
                    "ground" => $ebaySettings->ground,
                    "twoday" => $ebaySettings->twoday,
                    "overnight" => $ebaySettings->overnight,
                    "return_details" => $ebaySettings->return_details,
                    "return_days" => $ebaySettings->return_days,
                    "restocking_fee" =>$ebaySettings->restocking_fee,
                    "handle_time" => $ebaySettings->handle_time,
                    "paypal_email" => $ebaySettings->paypal_email,
                    "sales_tax" => $ebaySettings->sales_tax,
                    "state_sales_tax" => $ebaySettings->state_sales_tax,
                    "has_store" => $ebaySettings->has_store
                );
            }

            if (empty($settings['ground']) || empty($settings['return_details']) || empty($settings['return_days']) || empty($settings['handle_time']) || empty($settings['paypal_email'])) {
                throw new \RuntimeException(
                    'One or more eBay settings are missing. Please complete the required eBay settings and try again.'
                );
            }

            //If ( isset($request['storeCatId']) && $request['storeCatId'] )
            $storeCategoryID = 0;
            $storeCategory = DB::table('ebay_store_categories')->where("CategoryName", $product->categories->category_name)->first();
            if ($storeCategory)
                $storeCategoryID = "<Storefront><StoreCategoryID>" . $storeCategory->CategoryID . "</StoreCategoryID></Storefront>";

            $product=Product::find($product_id);
            $retail = $product->p_retail;

            $productImages = [];

            foreach ($product->images as $image) {
                $productImages[] = \URL::to('/') . '/images/'.$image->location;
            }

            $images = eBayHelper::UploadPictures($productImages);
            // dd($images);
            if ($images['error'] == "Error") {
                $message = is_scalar($images['response'])
                    ? (string) $images['response']
                    : json_encode($images['response']);
                EbayListing::updateOrCreate(
                    ['product_id' => $product_id],
                    ['errors' => $message]
                );
                throw new \RuntimeException("eBay picture upload failed for product ID# {$product_id}: {$message}");
            }

            foreach ($images['response'] as $image) {
                $imageURLs .=  "<PictureURL>" . $image . "</PictureURL>";
            }
            $revisedItem = '';

            if ($isRevision) {
                if ($ebayListing && !empty($ebayListing->listitem)) {
                    $revisedItem = "<ItemID>" . $ebayListing->listitem . "</ItemID>";
                } else {
                    $revisedItem = "<SKU>{$product_id}</SKU>";
                    $revisedItem .= "<InventoryTrackingMethod>SKU</InventoryTrackingMethod>";
                }
            }

            // create the XML request
            $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            $xmlRequest .= "<".$requestType."Request xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
            $xmlRequest .= "<RequesterCredentials>";
            $xmlRequest .= "<eBayAuthToken>" . $AUTH_TOKEN . "</eBayAuthToken>";
            $xmlRequest .= "</RequesterCredentials>";
            if (!$isRevision)
                $xmlRequest .= "<ErrorHandling>AllOrNothing</ErrorHandling>";
            $xmlRequest .= "<ErrorLanguage>en_US</ErrorLanguage>";
            $xmlRequest .= "<Item>";
            $xmlRequest .= $revisedItem;
            if (!$isRevision) {
                $xmlRequest .= "<SellerProfiles>";
                $xmlRequest .= "<SellerPaymentProfile>";
                $xmlRequest .= "<PaymentProfileID>124845636022</PaymentProfileID>";
                $xmlRequest .= "</SellerPaymentProfile>";
                $xmlRequest .= "<SellerReturnProfile>";
                $xmlRequest .= "<ReturnProfileID>139615777022</ReturnProfileID>";
                $xmlRequest .= "</SellerReturnProfile>";
                $xmlRequest .= "<SellerShippingProfile>";
                $xmlRequest .= "<ShippingProfileID>124847801022</ShippingProfileID>";
                $xmlRequest .= "</SellerShippingProfile>";
                $xmlRequest .= "</SellerProfiles>";
            }
            if ($specifics) {
                $xmlRequest .= "<ItemSpecifics>";
                $xmlRequest .= $specifics;
                $xmlRequest .= "</ItemSpecifics>";
            }
            if (!$isRevision)
                $xmlRequest .= "<InventoryTrackingMethod>SKU</InventoryTrackingMethod>";
            $xmlRequest .= "<Title>" . $title . "</Title>";
            //$xmlRequest .= "<BuyerRequirementDetails><ShipToRegistrationCountry>true</ShipToRegistrationCountry></BuyerRequirementDetails>";
            if ($storeCategoryID)
                $xmlRequest .= $storeCategoryID;

            $xmlRequest .= "<Description><![CDATA[" . stripcslashes($description). "]]></Description>";
            if (!$isRevision) {
                $xmlRequest .= "<PrimaryCategory>";
                $xmlRequest .= "<CategoryID>" . $addCatID . "</CategoryID>";
                $xmlRequest .= "</PrimaryCategory>";
                $xmlRequest .= "<PrivateListing>true</PrivateListing>";
            }
            //$xmlRequest .= "<AutoPay>true</AutoPay>";
            if ($listingType != 'Chinese') { // && $request["offer"] == "true"
                $xmlRequest .= "<BestOfferDetails>";
                $xmlRequest .= "<BestOfferEnabled>true</BestOfferEnabled>";
                $xmlRequest .= "</BestOfferDetails>";
            }
            if ($product->categories->category_name == 'Rolex')
                $bestOffer = $price - 200;
            else
                $bestOffer = $price - 500;

            $bestOffer = number_format($bestOffer, 2, '.', '');
            // $xmlRequest .= "<ConditionDescription>". $conditionDescription."</ConditionDescription>";
            $xmlRequest .= "<ListingDetails><MinimumBestOfferPrice>". $bestOffer . "</MinimumBestOfferPrice></ListingDetails>";
            $xmlRequest .= "<StartPrice>" . $price . "</StartPrice>";
            //$xmlRequest .= $reservedPrice;
            if (!$isRevision) {
                $xmlRequest .= "<ConditionID>".$condition."</ConditionID>";
                $xmlRequest .= "<CategoryMappingAllowed>true</CategoryMappingAllowed>";
                $xmlRequest .= "<Country>US</Country>";
                $xmlRequest .= "<Currency>USD</Currency>";
            }
            $xmlRequest .= "<DispatchTimeMax>".$settings['handle_time']."</DispatchTimeMax>";
            if (!$isRevision) {
                $xmlRequest .= "<ListingDuration>".$duration."</ListingDuration>";
                $xmlRequest .= "<ListingType>".$listingType."</ListingType>";
            }
            // $xmlRequest .= "<PaymentMethods>PayPal</PaymentMethods>";
            // $xmlRequest .= "<PayPalEmailAddress>".$settings['paypal_email']."</PayPalEmailAddress>";
            $xmlRequest .= "<PictureDetails>";
            $xmlRequest .= "<GalleryType>Gallery</GalleryType>".$imageURLs;
            $xmlRequest .= "</PictureDetails>";
            if (!$isRevision)
                $xmlRequest .= "<PostalCode>10036</PostalCode>";
            // if ($retail>0) {
            //     $xmlRequest .= "<DiscountPriceInfo>";
            //     $xmlRequest .= "<MinimumAdvertisedPrice>".$retail."</MinimumAdvertisedPrice>";
            //     $xmlRequest .= "<MinimumAdvertisedPriceExposure>PreCheckout</MinimumAdvertisedPriceExposure>";
            //     $xmlRequest .= "</DiscountPriceInfo>";
            // }
            $xmlRequest .= "<Quantity>".$quantity."</Quantity>";
            //$xmlRequest .= "<HitCounter>BasicStyle</HitCounter>";
            // $xmlRequest .= "<CrossBorderTrade>UK</CrossBorderTrade>";
            $xmlRequest .= "<ReturnPolicy>";
            $xmlRequest .= "<RefundOption>MoneyBackOrExchange</RefundOption>";
            $xmlRequest .= "<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>";
            $xmlRequest .= "<ReturnsWithinOption>Days_".$settings['return_days']."</ReturnsWithinOption>";
            $xmlRequest .= "<RestockingFeeValueOption>".$settings['restocking_fee']."</RestockingFeeValueOption>";
            $xmlRequest .= "<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>";
            $xmlRequest .= "</ReturnPolicy>";
            //$xmlRequest .= $ScheduleTime;
            $xmlRequest .= "<ShippingDetails>";
            $xmlRequest .= "<SalesTax><SalesTaxPercent>" .$settings['sales_tax']. "</SalesTaxPercent>";
            $xmlRequest .= "<SalesTaxState>".$settings['state_sales_tax']."</SalesTaxState>";
            $xmlRequest .= "<ShippingIncludedInTax>False</ShippingIncludedInTax></SalesTax>";
            $xmlRequest .= "<ShippingType>Flat</ShippingType>";
            $xmlRequest .= "<ShippingServiceOptions>";
            $xmlRequest .= "<ShippingServicePriority>1</ShippingServicePriority>";
            $xmlRequest .= "<ShippingService>FedExStandardOvernight</ShippingService>";
            $xmlRequest .= "<ShippingServiceCost>".$settings['overnight']."</ShippingServiceCost>";
            $xmlRequest .= "</ShippingServiceOptions>";
            $xmlRequest .= "<InternationalShippingServiceOption>";
            $xmlRequest .= "<ShippingServicePriority>1</ShippingServicePriority>";
            $xmlRequest .= "<ShipToLocation>UK</ShipToLocation>";
            $xmlRequest .= "<ShipToLocation>CA</ShipToLocation>";
            $xmlRequest .= "<ShipToLocation>CN</ShipToLocation>";
            $xmlRequest .= "<ShipToLocation>JP</ShipToLocation>";
            $xmlRequest .= "<ShipToLocation>AU</ShipToLocation>";
            $xmlRequest .= "<ShipToLocation>Europe</ShipToLocation>";
            $xmlRequest .= "<ShippingServiceCost>120</ShippingServiceCost>";
            $xmlRequest .= "<ShippingService>StandardInternational</ShippingService>";
            $xmlRequest .= "</InternationalShippingServiceOption>";
            // $xmlRequest .= "<ExcludeShipToLocation>ZA</ExcludeShipToLocation>";
            // $xmlRequest .= "<ExcludeShipToLocation>CN</ExcludeShipToLocation>";
            // $xmlRequest .= "<ExcludeShipToLocation>RU</ExcludeShipToLocation>";
            $xmlRequest .= "</ShippingDetails>";
            if (!$isRevision) {
                $xmlRequest .= "<SKU>$product_id</SKU>";
                $xmlRequest .= "<Site>US</Site>";
                $xmlRequest .= "<UUID>" . $uuid . "</UUID>";
            }
            $xmlRequest .= "</Item>";
            $xmlRequest .= "<WarningLevel>High</WarningLevel>";
            $xmlRequest .= "</".$requestType."Request>";
            // \Log::info($xmlRequest);
            $response = $ebayMain->sendHeaders($xmlRequest,$requestType);
            $xmlResponse = null;

            if ($response instanceof \SimpleXMLElement) {
                $xmlResponse = $response;
            } elseif (!is_array($response)) {
                $response = simplexml_load_string($response);

                if ($response === false) {
                    throw new \UnexpectedValueException('eBay returned an invalid XML response.');
                }

                $xmlResponse = $response;
            }

            if ($xmlResponse instanceof \SimpleXMLElement) {
                $xmlResponse->registerXPathNamespace('e', 'urn:ebay:apis:eBLBaseComponents');
                $errorMessages = $xmlResponse->xpath('//e:Errors/e:LongMessage') ?: [];
                $longMessage = implode(' ', array_map('strval', $errorMessages));
                $response = $xmlResponse;
            } else {
                $longMessage = $response['ErrorMessage'];
            }

            if (is_array($response)) {
                if (! $isRevision
                    && ($response['ErrorCode'] ?? null) === '21919067'
                    && ! empty($response['ItemId'])) {
                    EbayListing::updateOrCreate(
                        ['product_id' => $product_id],
                        [
                            'listitem' => $response['ItemId'],
                            'status' => 'active',
                            'errors' => null,
                        ]
                    );

                    $product->updateQuietly(['platform' => 1]);

                    \Log::warning('Duplicate eBay SKU detected; converting add to update.', [
                        'product_id' => $product_id,
                        'item_id' => $response['ItemId'],
                    ]);

                    // Run the revision in the current process. Dispatching it to
                    // another queue would leave the correction dependent on a worker.
                    (new AutomateEbayUpdate(['ids' => [$product_id]]))->handle();

                    continue;
                }

                if (! $isRevision && EbayListing::errorIndicatesExistingListing($longMessage)) {
                    EbayListing::updateOrCreate(
                        ['product_id' => $product_id],
                        ['status' => 'active', 'errors' => $longMessage]
                    );

                    $product->updateQuietly(['platform' => 1]);

                    \Log::warning('Active eBay SKU detected; converting add to SKU-based update.', [
                        'product_id' => $product_id,
                        'error' => $longMessage,
                    ]);

                    (new AutomateEbayUpdate(['ids' => [$product_id]]))->handle();

                    continue;
                }

                $ebayListing = $ebayListing ?: EbayListing::where('product_id',$product_id)->first();
                if ($ebayListing) {
                    $listingUpdate = ["errors" => $longMessage];

                    if (!$isRevision && !empty($response['ItemId'])) {
                        $listingUpdate['listitem'] = $response['ItemId'];
                    }

                    $ebayListing->update($listingUpdate);
                    \Log::error("eBay {$requestType} failed for product ID# {$product_id}: {$longMessage}");
                } else {

                    EbayListing::create([
                        "product_id" => $product_id,
                        "listitem" => $response['ItemId'],
                        "errors" => $longMessage,
                    ]);
                }

                throw new \RuntimeException("eBay {$requestType} failed for product ID# {$product_id}: {$longMessage}");
            } else {
                $ebayListing = $ebayListing ?: EbayListing::where('product_id',$product_id)->first();
                $Ack = (string) $response->Ack;

                if (! $isRevision
                    && $Ack !== 'Success'
                    && $Ack !== 'Warning'
                    && EbayListing::errorIndicatesExistingListing($longMessage)) {
                    EbayListing::updateOrCreate(
                        ['product_id' => $product_id],
                        ['status' => 'active', 'errors' => $longMessage]
                    );

                    $product->updateQuietly(['platform' => 1]);

                    \Log::warning('Active eBay SKU detected; converting add to SKU-based update.', [
                        'product_id' => $product_id,
                        'error' => $longMessage,
                    ]);

                    (new AutomateEbayUpdate(['ids' => [$product_id]]))->handle();

                    continue;
                }

                if ($Ack == 'Success' || $Ack == 'Warning')  {
                    $itemId = (string)$response->ItemID;
                    if ($itemId) {

                        if ($ebayListing) {
                            // \Log::info('update');
                            $listingUpdate = [
                                "product_id" => $product_id,
                                "listitem" => $itemId,
                                "listprice" => $price,
                                "status" => 'active',
                                "errors" => null,
                            ];

                            if ((string) $response->EndTime !== '') {
                                $listingUpdate['endtime'] = date('Y-m-d H:i:s', strtotime((string) $response->EndTime));
                            }

                            $ebayListing->update($listingUpdate);
                        } else {
                            // \Log::info('update-1');
                            EbayListing::create([
                                "product_id" => $product_id,
                                "listitem" => $itemId,
                                "listprice" => $price,
                                "status" => 'active',
                                'endtime' => date('Y-m-d H:i:s',date("U",strtotime((string)$response->EndTime)))
                            ]);
                        }

                        // Queue workers do not have an authenticated user. Avoid firing
                        // ProductObserver, which records the current web username.
                        $product->updateQuietly(['platform' => 1]);
                        $action = $isRevision ? 'Updated' : 'Created';
                        $msg = 'Product ID# ' . $product_id . ". {$action} item#: ".$itemId.".";
                        \Log::info($msg);
                    }
                        // \Log::debug($msg);
                } else {
                    // \Log::info('3');
                    // \Log::debug('Product ID# ' . $product_id . '. Error Code: '.$response->ErrorCode. ' ' .  $response->LongMessage);
                    if ($ebayListing) {
                        $ebayListing->update(["errors" => $longMessage]);
                    } else {
                        EbayListing::create([
                            "product_id" => $product_id,
                            "errors" => $longMessage
                        ]);
                    }
                    \Log::error("eBay {$requestType} failed for product ID# {$product_id}: {$longMessage}");
                    throw new \RuntimeException("eBay {$requestType} failed for product ID# {$product_id}: {$longMessage}");
                }
            }
            //sleep (5);
        }
    }
}
