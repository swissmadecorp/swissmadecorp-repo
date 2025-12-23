<?php 
	
namespace App\Libs;

use App\AmazonListings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Product;

class WatchFacts
{	
    protected $sellerId;

    public function __construct($sellerId) {
        $this->sellerId = $sellerId;
    }

    public function removeProduct($ids) {
        $uri = "https://wfda.watchfacts.com/listings/S19WM2KU4D2SRP";
        $products = Product::with('categories')
            ->whereIn('id',$ids)->get();

            
        foreach ($products as $product) {
            $items[] = array('sku'=>$product->id);
        }

        $arr = array(
            "category"=> "watches",
            "requestId"=> "S19WM2KU4D2SRP",
            "requestType"=> "inventory",
            "sellerId"=> $this->sellerId,
            "items"=> $items
        );

        $d = json_encode($arr);
            
        $response = \Httpful\Request::delete($uri)
            ->sendsJson()
            ->body($d)
            ->sendsType(\Httpful\Mime::FORM)    
            ->send();
        
        $uri = "https://wfda.watchfacts.com/listings/S19WM2KU4D2SRP/".$response->body->submissionId;
        $response = \Httpful\Request::get($uri)->send();
            
        $debug = json_decode(json_encode($response->body),true);
        return $debug;
    }

	public function submitProductToWatchFacts($ids) {
        $products = Product::whereIn('id',$ids)->get();
        
        foreach ($products as $product) {
            
            $uri = "https://wfda.watchfacts.com/listings/S19WM2KU4D2SRP";
            $condition = Conditions()->get($product->p_condition);
            $conditionNote='';$inspectionComments='';

            if ($condition=='Unworn' || $condition=='New') {
                $condition = 'new';
                $externalWear = "like new - mint";
            } else {
                $condition = 'Pre-owned';
                $externalWear = "excellent - near mint";
                $conditionNote = 'Like new condition';
                $inspectionComments = "Fully Serviced, Polished, Time-Tested and Air/Water Pressure Tested with 1 Year Warranty Included. Carefully used, small scratches may or may not be visible. Bracelet/Strap ~80%.";
            }

            $listingTitle = $product->title;
            $website =  \URL::to('/')  . '/public/images/';$i=0;

            $image = $website . $product->images->first()->location;
            $images = array();

            $images[] = array("imagePath"=> $image,"type"=> "MAIN");
 
            if ($product->images->count()==1) {
                for ($i=0;$i<3;$i++) {
                    $images[] = array(
                        "imagePath"=> $image,
                        "type"=> "ADDITIONAL"
                    );
                }
            } else {
                $i = 0;
                foreach($product->images as $p_image) {
                    if ($i>0) {
                        $images[] = array(
                            "imagePath"=> $website . $p_image->location,
                            "type"=> "ADDITIONAL"
                        );
                    }
                    $i++;
                }
                if (count($images)<4) {
                    
                    for ($i;$i<4;$i++) {
                        $images[] = array(
                            "imagePath"=> $image,
                            "type"=> "ADDITIONAL"
                        );
                    }   
                }

            }

            //return $images;
            $box = $product->p_box; $papers = $product->p_papers;
            $box_papers = "";$waterResistance="";

            if ($box && $papers)
                $box_papers = "box-and-papers";
            elseif ($box && !$papers)
                $box_papers = "box-only";
            elseif (!$box && $papers) 
                $box_papers = "papers-only";
            else $box_papers = "none";

            if ($product->water_resistance=='')
                $product->water_resistance = '100 meters / 330 feet';

            $waterResistance = array(
                "depth"=> substr($product->water_resistance,0,strpos($product->water_resistance,' ')),
                "depthUnits"=> "mt"
            );
            
            $bandMaterial = Strap()->get($product->p_strap);
            $bandmaterialArray =  array('stainless-steel'=>'Stainless Steel', 
                'gold-tone-stainless-steel' => 'Gold Tone Stainless Steel',
                'two-tone-stainless-steel' => 'Two Tone Stainless Steel',
                'bronze' => 'Bronze',
                'yellow-gold' => 'Yellow Gold',
                'white-gold' => 'White Gold',
                'rose-gold' => 'Rose Gold',
                'gold-and-platinum' => 'Gold & Platinum',
                'ceramic' => 'Ceramic',
                'canvas' => 'Canvas',
                'synthetic-leather' => 'Synthetic Leather',
                'leather-alligator' => 'Leather Allligator',
                'leather-crocodile' => 'Leather Crocodile',
                'platinum' => 'Platinum',
                'polyurethane' => 'Polyurethane',
                'resin' => 'Resin',
                'rubber' => 'Rubber',
                'silicon' => 'Silicon');

            $bandMaterialValue = array_search($bandMaterial,$bandmaterialArray);
            if ($bandMaterialValue == false) $bandMaterialValue='';
            
            $casematerial = Materials()->get($product->p_material);
            if ($casematerial=="18K White Gold/Stainless Steel" 
                || $casematerial=="18K Rose Gold/Stainless Steel"
                || $casematerial=="18K Yellow Gold/Stainless Steel") {

                    $caseMaterialValue='steel and 18k gold';

            } elseif ($casematerial=="14K White Gold/Stainless Steel" 
                || $casematerial=="14K Rose Gold/Stainless Steel"
                || $casematerial=="14K Yellow Gold/Stainless Steel") {

                $caseMaterialValue='steel and 14k gold';
                
            } else {
                $casematerialArray = array(
                    "stainless steel"=>'Stainless Steel',
                    'Brass Plated Stainless Steel' => 'Bronze',
                    "ceramic"=>"Ceramic",
                    "gold and platinum"=>"Platinum/Stainless Steel",
                    "plastic"=>"Plastic","platinum"=>"Platinum",
                    "rose gold"=>"18K Rose Gold",
                    "rubber"=>"Rubber",
                    "stainless steel with pvd coating"=>"PVD",
                    "steel and 14k gold"=>"14K White Gold/Stainless Steel",
                    "steel and 18k gold"=>"18K White Gold/Stainless Steel",
                    "sterling silver"=>"Silver",
                    "titanium"=>"Titanium",
                    "white gold"=>"18K White Gold",
                    "yellow gold"=>"18K Yellow Gold");

                $caseMaterialValue = array_search($casematerial,$casematerialArray);
                if ($caseMaterialValue==false) $caseMaterialValue = '';
            }

            if ($bandMaterial=='Jubilee' || $bandMaterial=='Oyster' || $bandMaterial=='President') {
                
                if ($casematerial=="Stainless Steel")
                    $bandMaterialValue = 'stainless-steel';
                elseif ($casematerial=="18K White Gold/Stainless Steel" || $casematerial == 'Gold Tone Stainless Steel' || $casematerial=="18K Rose Gold/Stainless Steel")
                    $bandMaterialValue="gold-tone-stainless-steel";
            }

            switch ($product->movement) {
                case 'Automatic':
                    $movement = 'automatic self wind';
                    break;
                case 'Manual':
                    $movement = 'mechanical hand wind';
                    break;
                case 'Quartz':
                    $movement = 'quartz';
                    break;
                default:
                    $movement = 'automatic self wind';
            }

            $productionYear = $product->p_year;
            if ($productionYear=='') {
                if ($condition == 'new')
                    $productionYear=date('Y');
                else $productionYear=date("Y",strtotime("-2 year"));
            }

            if ($product->p_color == 'Rhodium')
                $product->p_color = 'Grey';

            $clasp = Clasps()->get($product->p_clasp);
            if ($clasp = 'Fold Clasp')
                $clasp = "fold over clasp";
                
            $category = $product->categories->category_name;
            if ($category == "Rolex")
                $returns = 'ReturnsNotAccepted';
            else $returns = 'ReturnsAccepted';

            $items[] = array(
                "sku"=> $product->id,
                "brand"=> $category,
                "model"=> $product->p_model,
                "referenceNumber"=> $product->p_reference,
                "serialNumber"=> $product->p_serial,
                "conciergeService"=>true,
                "makeCountry"=> "Switzerland",
                "makeYear"=> $productionYear,
                "dispatchMaxTime"=> 1,
                "availableQty"=> 1,
                "style"=> "Luxury Watch",
                "gender"=> $product->p_gender==0 ? 'mens' : 'womens',
                "condition"=> $condition,
                "conditionNote"=> $conditionNote,
                "listingTitle"=> $listingTitle,
                "description"=> $product->keyword_build,
                "calendar"=> strpos($listingTitle,'Date')===true ? 'date' : '',
                "movement"=> $movement,
                "powerReserve"=> "48 hours",
                "metalStamp"=> "none",
                "waterResistance"=>$waterResistance,
                "band"=> array(
                    "type"=> Strap()->get($product->p_strap),
                    "material"=> $bandMaterialValue,
                    // "color"=> $request['bandcolor']
                ),
                "casing"=> array(
                "material"=> $caseMaterialValue,
                "shape"=> "Round",
                "diameter"=> substr($product->p_casesize,0,2),
                "diameterUnits"=> "mm",
                ),
                "bezel"=> array(
                    //"type"=> "fixed",
                    "material"=> BezelMaterials()->get($product->p_bezelmaterial),
                    //"function"=> "unidirectional"
                ),
                "clasp"=> array(
                    "type"=> $clasp,
                    //"material"=> "stainless-steel",
                    //"code"=> "CD12"
                ),
                "dial"=> array(
                "type"=> 'analog',
                "color"=> $product->p_color,
                "crystal"=> "Sapphire Crystal"
                ),
                "functions"=> array(
                "hours",
                "minutes",
                "seconds"
                ),
                "pricing"=> array(
                    array(
                        "marketplace"=> "offline",
                        "price"=> $product->p_newprice+100+45 // for shipping charge
                    ),              
                    array(
                        "marketplace"=> "superdealer",
                        "price"=> $product->p_newprice+100+45 // for shipping charge
                    ),
                    array(
                        "marketplace"=> "ebay_us",
                        "price"=> $product->p_newprice+100+45 // for shipping charge
                    )
                ),
                "images"=> $images,
                "watchfactsReport"=> array(
                "authenticity"=> "all-original",
                "boxPapers"=> $box_papers,
                "externalWear"=> $externalWear,
                "functionality"=> "meets-specifications",
                "waterTesting"=> "meets-specifications",
                "inspectionComments"=> $inspectionComments
                ),
                "amazonData"=> array(
                "primeTemplate"=> "prime-next-day"
                ),
                "ebayData" => array (
                    "format"=>"FixedPrice",
                    "duration"=>"GTC",
                    "shippingServiceType"=>"Flat",
                    "shippingServiceOption"=>"FedExPriorityOvernight",
                    "shippingServiceCost"=>40,
                    "returnsAcceptedOption"=>$returns,
                    "refundOption"=>"MoneyBack",
                    "shippingCostPaidByOption"=>"Buyer",
                    "returnsWithinOption"=>"Days_10",
                    "restockingFeeValueOption"=>"Percent_10",
                    "internationalShippingServiceOption"=>"FedExInternationalPriority",
                    "internationalShippingServiceLocations"=>"[\"Worldwide\"]",
                    "internationalShippingServiceCost"=>150,
                    "mpn"=>$product->p_reference,
                    // "storeCategory"=>"255743013",
                    // "storeCategory2"=>"",
                    // "bestOfferEnabled"=>true,
                    "useTaxTable"=>true
                ),
            );
        }

        $arr = array(
            "category"=> "watches",
            "requestId"=> "S19WM2KU4D2SRP",
            "requestType"=> "listing",
            "sellerId"=> $this->sellerId,
            "items"=> $items
        );

        //return $arr;
            $d = json_encode($arr);
            
            $response = \Httpful\Request::post($uri)
                ->sendsJson()
                ->body($d)
                ->sendsType(\Httpful\Mime::FORM)    
                ->send();

            $uri = "https://wfda.watchfacts.com/listings/S19WM2KU4D2SRP/".$response->body->submissionId;
            $response = \Httpful\Request::get($uri)->send();
            $debug = json_decode(json_encode($response->body),true);

            Log::debug($debug);
            if (isset($debug['status']) && $debug['status'] == 'FAILED') {
                Log::debug($debug);
            } elseif (isset($debug['status']) && strpos($debug['status'],'COMPLETED')>=0) {
                $product->update([
                    'platform'=>2
                ]);
            }

            if (isset($debug['status']) && $debug['status']!="FAILED") {
                AmazonListings::updateOrCreate(['product_id'=>$product->id],[
                    'product_id' => $product->id,
                    'listprice' => $product->p_newprice+100+45,
                    'submissionId' => $response->body->submissionId
                ]);

                $product->update([
                    'platform'=>2
                ]);
            } else {
                Log::debug($debug);
            }

            //Log::debug($arr);

            //sleep(1);
        
        
    }
}