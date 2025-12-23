<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Models\EbaySettings;
use App\Models\EbayCategory;
use App\Models\EbayCondition;
use App\Models\EbaySpecifics;
use App\Models\EbayListing;
use App\Models\Specifics;
use App\Models\Product;
use App\Jobs\AutomateEbayPost;
use App\Libs\eBaySession;
use App\Libs\eBayHelper;
use App\Libs\eBayMain;
use DB;

class EbayController extends Controller
{
    private function getAttributesFromEbay($url,$isFromFile = false) {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);  //hides errors from invalid tags
        $dom->strictErrorChecking = false;
        $dom->preserveWhiteSpace = false;
        $dom->recover = true;

        // $dom->loadHTMLFile($url);
        $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $html = file_get_contents($url,false,$context);

        $dom->loadHTML($html);

        $DOMxpath = new \DOMXPath($dom);
        $DivContent = $DOMxpath->query('//div[contains(@class,"itemAttr")]/div[@class="section"]/table');
        if ($DivContent->length==2)
            $DivContent = $DOMxpath->query('//div[contains(@class,"itemAttr")]/div[@class="section"]/table[2]/tr');
        else
            $DivContent = $DOMxpath->query('//div[contains(@class,"itemAttr")]/div[@class="section"]/table/tr');

        $result = array();$result2 = array();

        foreach ($DivContent as $entry) {
            $node = $DOMxpath->query("td[@class='attrLabels']", $entry);
            foreach ($node as $entry2){
                $nodeValue=trim(preg_replace('/\s\s+/', ' ', $entry2->nodeValue));
                $result[] =  substr($nodeValue,0,strpos($nodeValue,':'));
            }


            $node = $DOMxpath->query("td/*", $entry);
            foreach ($node as $entry2){
                $nodeValue=trim(preg_replace('/\s\s+/', ' ', $entry2->nodeValue));
                $result2[] = $nodeValue;
            }

        }

        $result = array_combine($result,$result2);
        $results[] = $result;
        return $results;
    }

    public function Synchronize(Request $request) {
        $Ids = $request['ids'];

        EbayListing::truncate();

        for ($i=0; $i < count($Ids); $i++) {
            EbayListing::create([
                'product_id' => $Ids[$i][0],
                'listitem' => $Ids[$i][1],
                'listprice' => $Ids[$i][2],
                'status' => 'active'
            ]);
        }
    }

    private function getAttributesFromURL($url,$isFromFile = false) {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);  //hides errors from invalid tags
        $dom->strictErrorChecking = false;
        $dom->preserveWhiteSpace = false;
        $dom->recover = true;

        if ($isFromFile==false) {
            $opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
            $context = stream_context_create($opts);
            $html = file_get_contents($url,false,$context);

            $dom->loadHTML($html);
        } else {
            $dom->loadHTMLFile($url);
        }

        $DOMxpath = new \DOMXPath($dom);
        $DivContent = $DOMxpath->query('//div[contains(@class,"attribute-group")]/ul[last()]');

        foreach ($DivContent as $i => $entry) {
            $node = $DOMxpath->query("li/label[@class='label']", $entry);
            $result = array();$result2 = array();
            foreach ($node as $entry2){
                $node2 = $DOMxpath->query("li/label[@class='label']", $entry2);
                $result[] =  substr($entry2->nodeValue,0,strpos($entry2->nodeValue,' :'));
            }

            $node = $DOMxpath->query("li/div/span[@class='data']", $entry);
            foreach ($node as $entry2){
                $string = substr(trim(preg_replace('/\s\s+/', ' ', $entry2->nodeValue)),0);
                $result2[] = $string;
            }

            $result = array_combine($result,$result2);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$externalAttributes = $this->getAttributesFromEbay('https://www.ebay.com/itm/132672513108'); //"http://swissmadecorp.com/public/ebay.html");
        //dd($externalAttributes);
        //\Log::channel('slack')->info('Something happened!');
        //\Log::stack(['single', 'slack'])->info('Something happened!');
        //\Log::info('User failed to login.', ['id' => 5]);
        return view('admin.ebay',['pagename' => 'Ebay Page','includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function ebayListings() {
        $ebaySettings = EbaySettings::first();
        return view('admin.listings',['pagename' => 'Create New eBay Listings','ebaySettings' => $ebaySettings,'includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function ebayEndListings() {
        return view('admin.endlisting',['pagename' => 'eBay Listings','includeDataTableCss'=>'1','includeDataTableJs'=>'1']);
    }

    public function show() {
        //
    }

    public function getSettings (){
        $ebaySettings = EbaySettings::first();
        $arr = array(
            "shipping" => $ebaySettings->ground,
            "shipping1" => $ebaySettings->twoday,
            "shipping2" => $ebaySettings->overnight,
            "return_details" => $ebaySettings->return_details,
            "return_days" => $ebaySettings->return_days,
            "restocking_fee" =>$ebaySettings->restocking_fee,
            "handle_time" => $ebaySettings->handle_time,
            "paypal_email" => $ebaySettings->paypal_email,
            "sales_tax" => $ebaySettings->sales_tax,
            "state_sales_tax" => $ebaySettings->state_sales_tax,
            "has_store" => $ebaySettings->has_store

        );

        return response()->json($arr);
    }

    function saveSettings (Request $request){
        if ($request->ajax()) {
            $ebaySettings = EbaySettings::where('id',0);

            //return response()->json($ebaySettings);
            parse_str ($request['datainfo'],$output);

            if (!isset($output['has_store'])) $output['has_store']='off';

            $shipping = $output['shipping'] ? $output['shipping'] : 0; // Ground
            $shipping1 = $output['shipping1'] ? $output['shipping1'] : 0; //2nd Day
            $shipping2 = $output['shipping2'] ? $output['shipping2'] : 0; //overnight
            $return_details = empty($output['return_details']) ? "Returns are acceptable within 15 days of purchase." : $output['return_details'];
            $return_days = empty($output['return_days']) ? 15 : $output['return_days'];
            $restocking_fee = $output['restocking_fee'];
            $handle_time = empty($output['handle_time']) ? 3 : $output['handle_time'];
            $paypal_email = $output['paypal_email'];
            $sales_tax = $output['sales_tax'] ? $output['sales_tax'] : 0;
            $state_sales_tax = $output['state_sales_tax'];
            $has_store = $output['has_store'] == 'on' ? 1 : 0;

            $arr = array(
                "ground" => $shipping,
                "twoday" => $shipping1,
                "overnight" => $shipping2,
                "return_details" => $return_details,
                "return_days" => $return_days,
                "restocking_fee" => $restocking_fee,
                "handle_time" => $handle_time,
                "paypal_email" => $paypal_email,
                "sales_tax" => $sales_tax,
                "state_sales_tax" => $state_sales_tax,
                "has_store" => $has_store
            );

            if ($ebaySettings) {
                $ebaySettings->update ($arr);
            } else {
                EbaySettings::create($arr);
            }

            return response()->json('Successfully updated');
        }
    }

    public function Notify(Request $request) {
        //$settings = Config::get('ebay.settings');
        /*Notification delivery failed with HTTP status code 405 from https://swissmadecorp.com/ebay/notify.
            Please ensure that the marketplace account deletion notification endpoint is ready to receive notifications.*/
        \Log::debug('asdf');
        return view('notify');
    }

    public function SetStoreCategories(Request $request) {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                    <SetStoreCategoriesRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                        <RequesterCredentials>
                            <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                        </RequesterCredentials>
                        <Action>Add</Action>
                        <StoreCategories>
                            <CustomCategory>
                                <Name>" . $request['category_name'] . "</Name>
                            </CustomCategory>
                        </StoreCategories>
                    </SetStoreCategoriesRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'SetStoreCategories',725);
        if ($response->Ack == 'Success')  {
            return "Successfully created a store category.";
        } else 'Fail';
    }

    public function GetItemSpecifics (Request $request) {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        //return response()->json($request);
        $CatId = $request['catId'];

        $txt = '';
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <GetCategorySpecificsRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                          <CategorySpecific>
                            <CategoryID>$CatId</CategoryID>
                          </CategorySpecific>
                          <MaxValuesPerName>1000</MaxValuesPerName>
                          <RequesterCredentials>
                            <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                            </RequesterCredentials>
                        </GetCategorySpecificsRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetCategorySpecifics',725);
        //return response()->json($response);

        if ($response->SeverityCode=="Error")
            return array('error'=>$response->ErrorCode. '<br>' . $response->ShortMessage . '<br>' . $response->LongMessage);

        $txt = "<ul class='topLevel'>";
        foreach ($response->Recommendations->NameRecommendation as $value) {
            $csName = str_replace(" ", "_", $value->Name);
            $csName = str_replace("(", "", $csName);
            $csName = str_replace(")", "", $csName);

            $data = [
                'CategoryID' => $CatId,
                'CategoryName' => $value->Name
            ];

            $ebaySpecifics = EbaySpecifics::updateOrCreate(['CategoryName'=>$value->Name],$data);

            $txt .= "<li><span>$value->Name</span><br /><input type='text' class='editCombo'/><div class='dropDown' ><ul>";
            foreach ($value->ValueRecommendation as $value2) {
                $txt .= "<li><a class='comboOption' href=''>$value2->Value</a></li>";
                $this->saveItemSpecificsToDB($ebaySpecifics->id,$value2->Value);
            }
            $txt .= "</ul>";
        }

        $txt .= "<li id='custom_field'><div class='customspecificitem'><input type='text' style='border: 0' placeholder='Custom Field Name' class='form-control custom_field'/>
                    <button id='save_field'><i class='fas fa-save'></i></button></div>
                    <input type='text' placeholder='Custom Field Value' class='form-control'/></li>";
                    $txt .= "</ul></div>";
        $txt .= "</ul>";
        return $txt;
        //return response()->json ($txt);
    }

    private function findKeyByCategory($key,$externalAttributes) {
        foreach ($externalAttributes as $attrib) {
            $m = array_search($key,array_keys($attrib));
            if ($m>-1) return $attrib[$key];
        }

        return '';
    }

    public function getSpecificsFromURL(Request $request) {
        if ($request->ajax()) {
            $CatId = $request['catId'];
            $url = $request['url'];

            $specifics = DB::table('ebay_specifics')
            ->select(DB::raw('*, (SELECT COUNT(0) FROM specifics WHERE specifics.id=ebay_specifics.id) t'))
            ->where('CategoryID',$CatId)
            ->get();

            $txt = "<ul class='topLevel'>";$txtValue='';
            if(strpos($url,'.com')==0) {
                $externalAttributes = $this->getAttributesFromEbay('https://www.ebay.com/itm/'.$url);
                foreach($specifics as $item) {
                    $catName =  $item->CategoryName;
                    if ($item->t== 2) {
                        $txt .= "<li><span>$catName</span><br />";
                        $txt .= "<input class='form-control' type='text' /></li>";
                    } else {
                        if (array_key_exists($catName,$externalAttributes[0])) {
                            $txtValue=$externalAttributes[0][$catName];
                            $txt .= '<li><span>'.$catName.'</span><br /><input type="text" value="'.$txtValue.'" class="editCombo form-control"/><div class="dropDown"><ul>';
                        }else {
                            $txtValue='';
                            $txt .= '<li><span>'.$catName.'</span><br /><input type="text" class="editCombo form-control"/><div class="dropDown"><ul>';
                        }
                        //\Log::info($catName);
                        $itemSpecifics = Specifics::where('id',$item->id)->get();
                        foreach ($itemSpecifics as $specific) {
                            $txt .= "<li><a class='comboOption'>$specific->title</a></li>";
                        }
                        $txt .= "</ul></div>";
                    }
                }
                $catName=strlen($catName)>25 ? substr($catName,0,25).'...' :  $catName;
            } else {
                $externalAttributes = $this->getAttributesFromURL($url);

                foreach($specifics as $item) {
                    $catName =  $item->CategoryName;
                    if ($item->t== 2) {
                        $txt .= "<li><span>$catName</span><br />";
                        $txt .= "<input class='form-control' type='text' /></li>";
                    } else {

                        switch ($catName) {
                            case "Brand":
                            case "Model":
                            case "MPN":
                            case "Gender":
                            case "Case Sizes":
                            case "Case Material":
                            case "Band Material":
                            case "Face Color":
                            case "Movement":
                            case "Water Resistance":
                            case "Style":
                            case "Features":
                            case "Watch Shape":
                            case "Band Type":
                                $txtValue=$this->findKeyByCategory($catName,$externalAttributes);
                                break;
                        }

                        if ($catName=='Age Group')
                            $txtValue='Adult';
                        elseif ($catName=='Year of Manufacture')
                            $txtValue='2010-Now';
                        elseif ($catName=='Display')
                            $txtValue='Analog';
                        elseif ($catName=='Band Color') {
                            if (array_key_exists("'Band Color(s)'",$externalAttributes[3]))
                                $txtValue=str_replace(' Tone','',$externalAttributes[3]['Band Color(s)']);
                            else $txtValue='';
                        }

                        $catName=strlen($catName)>24 ? substr($catName,0,25).'...' :  $catName;
                        $txt .= '<li><span>'.$catName.'</span><br /><input type="text" value="'.$txtValue.'" class="editCombo form-control"/><div class="dropDown"><ul>';

                        $itemSpecifics = Specifics::where('id',$item->id)->get();
                        foreach ($itemSpecifics as $specific) {
                            $txt .= "<li><a class='comboOption'>$specific->title</a></li>";
                        }
                        $txt .= "</ul></div>";
                    }
                }
            }

            $txt .= "<li id='custom_field'><div class='customspecificitem'><input type='text' style='border: 0' placeholder='Custom Field Name' class='form-control custom_field'/>
                    <button id='save_field'><i class='fas fa-save'></i></button></div>
                    <input type='text' placeholder='Custom Field Value' class='form-control'/></li>";
            $txt .= "</ul>";

            return response()->json ($txt);
        }
    }

    protected function loadItemSpecificsFromDB(Request $request) {
        if ($request->ajax()) {
            $itemId='';
            $CatId = $request['catId'];
            $product_id = $request['product_id'];

            if (isset($request['itemId']))
                $itemId = $request['itemId'];

            if ($itemId) {
                $response = $this->getItem($itemId);
                $CatId = (string)$response->Item->PrimaryCategory->CategoryID;

                $specifics = DB::table('ebay_specifics')
                    ->select(DB::raw('*, (SELECT COUNT(0) FROM specifics WHERE specifics.id=ebay_specifics.id) t'))
                    ->where('CategoryID',$CatId)
                    ->get();

                $table = (string)$response->Item->Description;
                preg_match_all('/(<table[^>]*>(?:.|\n)*(?=<\/table>))/', $table, $aMatches);

                $txt = "<ul class='topLevel'>";
                foreach ($response->Item->ItemSpecifics->NameValueList as $itemSpecific) {
                    $valuePair[] = array('Name'=>(string)$itemSpecific->Name,'Value'=>(string)$itemSpecific->Value);
                }

                foreach($specifics as $item) {

                    $find = array_search($item->CategoryName,array_column($valuePair,'Name'));
                    $catName =  strlen($item->CategoryName)>25 ? substr($item->CategoryName,0,25).'...' : $item->CategoryName;
                    if ($item->t== 2) {
                        $txt .= "<li><span>$catName</span><br />";
                        $txt .= "<input class='form-control' type='text' /></li>";
                    } else {
                        if ($find>-1) {
                            $txtValue=$valuePair[$find]['Value'];

                            $itemSpecifics = Specifics::where('id',$item->id)->get();
                            $txt .= '<li><span>'.$catName.'</span><br /><input type="text" value="'.$txtValue.'" class="editCombo form-control"/><div class="dropDown"><ul>';

                            foreach ($itemSpecifics as $specific) {
                                $txt .= "<li><a class='comboOption'>$specific->title</a></li>";
                            }
                            $txt .= "</ul></div>";
                        } else {
                            $txt .= '<li><span>'.$catName.'</span><br /><div><input type="text" class="editCombo form-control"/><button>...</button></div><div class="dropDown"><ul>';

                            foreach ($itemSpecifics as $specific) {
                                $txt .= "<li><a class='comboOption'>$specific->title</a></li>";
                            }
                            $txt .= "</ul></div>";
                        }
                    }
                }

                $txt .= "<li id='custom_field'><div class='customspecificitem'><input type='text' style='border: 0' placeholder='Custom Field Name' class='form-control custom_field'/>
                    <button id='save_field'><i class='fas fa-save'></i></button></div>
                    <input type='text' placeholder='Custom Field Value' class='form-control'/></li>";
                $txt .= "</ul>";
                $eBayItem = $response->Item;
                //return response()->json($eBayItem->Storefront->StoreCategoryID);
                $m = array(
                    'specifics'=>$txt,
                    'title'=>(string)$eBayItem->Title,
                    'price'=>(string)$eBayItem->StartPrice,
                    'condition'=>(string)$eBayItem->ConditionID,
                    'StoreCategoryID'=>(string)$eBayItem->Storefront->StoreCategoryID,
                    'description'=>$aMatches[0][0]
                );

                return response()->json ($m);
            } else {
                $txt = "<ul class='topLevel'>";

                $specifics = DB::table('ebay_specifics')
                    ->select(DB::raw('*, (SELECT COUNT(0) FROM specifics WHERE specifics.id=ebay_specifics.id) t'))
                    ->where('CategoryID',$CatId)
                    ->get();

                if (!$specifics->count()) {
                    $request->request->add(['catId' => $CatId]);
                    return response()->json ($request);
                }

                $product = Product::find($product_id);
                $metalPurity = '';
                foreach($specifics as $item) {
                    $catName =  $item->CategoryName;
                    //$catName = strlen($catName)>25 ? substr($catName,0,25).'...' : $catName;

                    $linkedCat = $item->LinkedCategory;
                    if ($linkedCat) {
                        if (strpos($linkedCat,'%')!==false) {
                            $linkedCat = str_replace('%','',$linkedCat);
                            switch ($linkedCat) {
                                case 'brand_name':
                                    $txtValue=$product->categories->category_name;
                                    break;
                                case 'p_material':
                                    if ($product->group_id == 1)
                                        $txtValue = MetalMaterial()->get($product->p_material);
                                    else {
                                        $txtValue=Materials()->get($product->p_material);
                                        if (strpos($txtValue,'18K') !== false ) {
                                            $txtValue = str_replace("18K ","",$txtValue);
                                            $metalPurity = "18k";
                                        }
                                    }
                                    break;
                                case 'p_strap':
                                    $txtValue=Strap()->get($product->p_strap);
                                    break;
                                case 'p_dial_style':
                                    if ($product->p_dial_style > 0) {
                                        $txtValue = DialStyle()->get($product->p_dial_style);
                                        //if ($product->p_dial_style > 4)

                                    }
                                    break;
                                case 'p_box':
                                    if ($product->p_box == 1)
                                        $txtValue='Yes';
                                    else $txtValue='No';
                                    break;
                                case 'water_resistance':
                                    $meters = substr($product->water_resistance,0,strpos($product->water_resistance, " "));
                                    if ($meters)
                                        $txtValue = $meters . " m " . "(". substr($meters,0,strlen($meters)-1) ." ATM)";
                                    //\Log::debug($meters);
                                    break;
                                case 'p_bezelmaterial':
                                    if (strpos(BezelMaterials()->get($product->p_bezelmaterial),'18K') !== false ) {
                                        $txtValue = str_replace("18K ","",Materials()->get($product->p_bezelmaterial));
                                    }
                                    break;
                                case 'p_papers':
                                    if ($product->p_papers == 1)
                                        $txtValue='Yes';
                                    else $txtValue='No';
                                    break;
                                case 'movement':
                                    $txtValue = Movement()->get($product->movement);
                                    break;
                                case 'p_clasp':
                                    $txtValue = Clasps()->get($product->p_clasp);
                                    break;
                                case 'p_bezelmaterial':
                                    $txtValue = BezelMaterials()->get($product->p_bezelmaterial);
                                    break;
                             default:
                                 $txtValue=$product->$linkedCat;
                                 break;
                            }
                        } else {
                            $txtValue=$item->LinkedCategory;
                        }
                    } else $txtValue='';

                    if ($catName == "Metal Purity" && Materials()->get($product->p_material)) {
                        $txtValue = $metalPurity;
                    }

                    $txt .= '<li><span data-text="'.$item->CategoryName.'">'.$catName.'</span><br /><div class="txtContainer"><input type="text" value="'.$txtValue.'" data-id="'.$item->id.'" class="editCombo form-control"/><button>...</button></div>';
                }

                $txt .= "<li id='custom_field'><div class='customspecificitem'><input type='text' style='border: 0' placeholder='Custom Field Name' class='form-control custom_field'/>
                    <button id='save_field'><i class='fas fa-save'></i></button></div>
                    <input type='text' placeholder='Custom Field Value' class='form-control'/></li>";
                $txt .= "</ul>";

                $item='';
                foreach ($product->getAttributes() as $key => $action) {
                    if ($key == 'category_id')
                        $item .= "<div class='dditem'>brand_name</div>";
                    else
                        $item .= "<div class='dditem'>$key</div>";
                }

                $ddcategory = "<div id='ddcategory'>$item</div>";

                return response()->json (array('error'=>null,'specifics'=>$txt,'ddcategory'=>$ddcategory));
            }
        }
    }

    public function getSpecificForCategory(Request $request) {
        $itemSpecifics = Specifics::where('id',$request['id'])->get();
        $txt = '<ul>';

        foreach ($itemSpecifics as $specific) {
            $txt .= "<li><a class='comboOption'>$specific->title</a></li>";
        }
        $txt .= "</ul>";

        return response()->json($txt);
    }

    public function itemLoadTemplate (Request $request) {
        $ebaySpecifics = EbaySpecifics::where('CategoryID',$request['catId'])->pluck('LinkedCategory','id')->all();
        return response()->json($ebaySpecifics);
    }

    public function linkSpecifics(Request $request) {
        $els = $request['els'];
        foreach ($els as $el) {
            //if (strpos($el[0],'%')!==false) {
                $data = [
                    'LinkedCategory'=>$el[0]
                ];
                $ebaySpecifics = EbaySpecifics::updateOrCreate(['id'=>$el[1]],$data);
            //}
        }

        return response()->json($request);
    }

    protected function serverName() {
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
            $protocol = "https";
        } else {
            $protocol = "http";
        }

        if ($_SERVER['SERVER_NAME'] == 'localhost')
            $homepath = "";
        else $homepath = $protocol.'://'.$_SERVER['HTTP_HOST'];

        return $homepath;
    }

    public function saveSpecificField(Request $request) {
        if ($request->ajax()) {
            $field_name = $request['field_name'];
            $catId = $request['catId'];

            EbaySpecifics::create([
                'CategoryName'=>$field_name,
                'CategoryID'=>$catId
            ]);
            $new_field = "<li id='custom_field'><div class='customspecificitem'><input type='text' style='border: 0' placeholder='Custom Field Name' class='form-control custom_field'/>
                    <button id='save_field'><i class='fas fa-save'></i></button></div>
                    <input type='text' placeholder='Custom Field Value' class='form-control'/></li>";

            return response()->json (array('new_field'=>$new_field,'alert'=>'New field has been created'));
        }
    }

    protected function saveItemSpecificsToDB($id, $value) {
        $data = [
            'id' => $id,
            'title' => $value,
        ];

        $specifics = Specifics::where('id',$id)->where('title',$value)->get();
        if ($specifics->isEmpty())
            $specifics = Specifics::create($data);
    }

    function getCategoryNode(Request $request) {
        $txt = '';

        $CategoryID = $request["catId"];
        if (isset( $request["catId"] )) {
            $cats = EbayCategory::whereRaw("CategoryParentID=$CategoryID AND CategoryID<>$CategoryID")->get();

            $i = 0;

            if (!$cats->isEmpty()) {
                $txt = "<select size=\"8\" id=\"category_$CategoryID\" multiple=\"multiple\" class=\"primary_category\">";
                foreach ($cats as $cat) {
                    $txt .= "<option value='".$cat->CategoryID."'>$cat->CategoryName</option>";
                    $i++;
                }
                $txt .= '</select>';
            }

        }
        if ($txt == '') {
            //$sql="SELECT * FROM conditions WHERE CategoryID = $CategoryID";

            if ($CategoryID) {
                $conditions = EbayCondition::where('CategoryID',$CategoryID)->get();

                if (count($conditions)) {
                    foreach ($conditions as $condition) {
                        $txt .= "<option value='".$condition->ConditionValue."'>$condition->DisplayName</option>";
                        $i++;
                    }

                } else {
                    $returnCategories = $this->getItemConditions ($CategoryID);

                    $arr = array();
                    $conditions = $returnCategories->Category->ConditionValues->Condition;
                    foreach ($conditions as $category)  {
                        $arr[] = array('CategoryID'=>$CategoryID,'ConditionValue'=>(string)$category->ID,'DisplayName'=>(string)$category->DisplayName);

                        $txt .= "<option value='$category->ID'>$category->DisplayName</option>";
                        $i++;
                    }

                    EbayCondition::insert($arr);
                }
            }

            $arr = array("conditions" => $txt);
            if ($txt) return response()->json($arr);
        }

        return response()->json($txt);
    }

    protected function getAllFeatures () {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <GetCategoryFeaturesRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                             <RequesterCredentials>
                                <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                            </RequesterCredentials>
                            <DetailLevel>ReturnAll</DetailLevel>
                            <CategoryID>31387</CategoryID>
                            <AllFeaturesForCategory>true</AllFeaturesForCategory>
                        </GetCategoryFeaturesRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetCategoryFeatures');
        return $response;
    }

    protected function getItemConditions ($CategoryId) {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <GetCategoryFeaturesRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                             <RequesterCredentials>
                        <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                        </RequesterCredentials>
                        <DetailLevel>ReturnAll</DetailLevel>
                        <ViewAllNodes>true</ViewAllNodes>
                        <CategoryID>$CategoryId</CategoryID>
                        <FeatureID>ConditionEnabled</FeatureID>
                        <FeatureID>ConditionValues</FeatureID>
                    </GetCategoryFeaturesRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetCategoryFeatures');
        return $response;
    }

    public function getStoreCategories (Request $request) {
        if ($request['refresh']==0) {
            $content = $this->loadStoreCategories();
            if ($content) die ($content);
        }

        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                            <GetStoreRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                                <CategoryStructureOnly>True</CategoryStructureOnly>
                                <RequesterCredentials>
                                    <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                                </RequesterCredentials>
                            </GetStoreRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetStore');
        //return response()->json ($response);
        ob_start();

        //DB::table('ebay_store_categories')->truncate();
        foreach ($response->Store->CustomCategories->CustomCategory  as $value) {
            if ( $value->ChildCategory ) {
                //$value->Name;
            ?>
                <option value="<?php echo $value->CategoryID ?>"><?php echo $value->Name ?></option>
            <?php
                $this->storeCategoriesToTable ($value,0);
                $this->childCategory($value,5,0);
            } else {
            ?>
                <option value="<?php echo $value->CategoryID ?>"><?php echo $value->Name ?></option>
            <?php
                $this->storeCategoriesToTable ($value,0);
            }
        }
        $content=ob_get_clean();
        return response()->json ($content);
    }

    protected function storeCategoriesToTable($category,$level) {

        DB::table('ebay_store_categories')->insert([
            'CategoryID'=>$category->CategoryID,
            'CategoryName'=>$category->Name,
            'CategoryLevel'=>$level
            ]
        );

        //echo $category->CategoryID;
    }

    protected function loadStoreCategories () {

        $storeCategorories = DB::table('ebay_store_categories')->get();
        if (!$storeCategorories->isEmpty()) {
            ob_start();
            foreach ($storeCategorories as $category) {
                if ( $category->CategoryLevel == 1 ) {
                ?>
                    <option value="<?php echo $category->CategoryID ?>"><?php echo str_repeat("&nbsp;", 5); echo $category->CategoryName ?></option>
                <?php
                } elseif ( $category->CategoryLevel == 2 ) { ?>
                    <option value="<?php echo $category->CategoryID ?>"><?php  echo str_repeat("&nbsp;", 10); echo $category->CategoryName ?></option>
                <?php
                } else {
                ?>
                    <option value="<?php echo $category->CategoryID ?>"><?php echo $category->CategoryName ?></option>
                <?php

                }
            }
        }
        $content=ob_get_clean();
        return $content;
    }

    protected function childCategory($child, $multiplier,$level) {
        foreach ($child->ChildCategory as $child) {
        ?>
            <option value="<?php echo $child->CategoryID ?>"><?php echo str_repeat("&nbsp;", $multiplier) ?><?php echo $child->Name ?></option>
        <?php
            $level++;
            $this->storeCategoriesToTable ($child,$level);
            $this->childCategory ($child,$multiplier+5,$level);
        }
    }

    public function loadTemplate(Request $request) {
        if ($request->ajax()) {
            $product_id = $request['product_id'];
            $template_name = $request['template_name'];

            $productTemp = Product::find($product_id);
            $product = eBayHelper::getTemplate($productTemp, $template_name);

            $response = array(
                'content'=>$product['content'],
                'image'=>$product['image'],
                'price'=>$product['price'],
                'cost'=>$product['cost'],
                'condition'=>$product['condition'],
                'StoreCategoryName'=> $product['StoreCategoryName']
            );

            return response()->json($response);
        }
    }

    protected function eBayItems($listType, $pageNumber=1,$data=null,$totalEntries=0) {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        //$listType = 'ActiveList'; //$request['listType'];
        //$pagenumber ++; // $request['pagenumber'];

        switch ($listType) {
            case 'load_active_items':
                $listType = 'ActiveList';
                break;
            case 'load_inactive_items':
                $listType = 'UnsoldList';
                break;
            case 'load_sold_items':
                $listType = 'SoldList';
                break;
        }
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
            <GetMyeBaySellingRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                <RequesterCredentials>
                    <eBayAuthToken>". $AUTH_TOKEN ."</eBayAuthToken>
                </RequesterCredentials>
                <$listType>
                    <Pagination>
                        <EntriesPerPage>10</EntriesPerPage>
                        <PageNumber>$pageNumber</PageNumber>
                    </Pagination>
                </$listType>
                <DetailLevel>ReturnAll</DetailLevel>
            </GetMyeBaySellingRequest>";


        $response = $ebayMain->sendHeaders($xmlRequest,'GetMyeBaySelling', "XML");

        $responseDoc = new \DomDocument();
        $responseDoc->loadXML($response);
        $responses = $responseDoc->getElementsByTagName("GetMyeBaySellingResponse");

        $totalNumberOfEntries=0;
        $totalNumberOfPages =0;

        $settings = Config::get('ebay.settings');
        $activeListings = EbayListing::all();

        // return response($response, 200, [
        //     'Content-Type' => 'application/xml'
        // ]);

        foreach ($responses as $response) {

        if ($response->getElementsByTagName("Ack")->item(0)->nodeValue == 'Success')  {
            //return simplexml_load_string($response);

            if ($response->getElementsByTagName("TotalNumberOfEntries")->length) {
            $totalNumberOfEntries  = $response->getElementsByTagName("TotalNumberOfEntries");
            //\Log::info(print_r($totalNumberOfEntries->item(0),true));
            $totalNumberOfEntries  = $totalNumberOfEntries->item(0)->nodeValue;
            $totalNumberOfPages=$response->getElementsByTagName("TotalNumberOfPages")->item(0)->nodeValue;

            $items  = $response->getElementsByTagName("Item");

            $entries = $totalNumberOfEntries > 9 ? 10 : $totalNumberOfEntries;

            for($i=0; $i<$entries; $i++) {
                //if (!$items->item($i) instanceof DOMElement) break;

                $totalEntries++;
                if ($totalEntries>$totalNumberOfEntries) break;
                $itemId = $items->item($i)->getElementsByTagName('ItemID')->item(0)->nodeValue;
                if (!empty($items->item($i)->getElementsByTagName('SKU')->item(0)->nodeValue)) {
                    $product_id = $items->item($i)->getElementsByTagName('SKU')->item(0)->nodeValue;
                    $itemTitle = $items->item($i)->getElementsByTagName('Title')->item(0)->nodeValue;
                    $img = str_replace('http','https',$items->item($i)->getElementsByTagName('PictureDetails')->item(0)->nodeValue);
                    $listingType = $items->item($i)->getElementsByTagName('ListingType')->item(0)->nodeValue;
                    if (!empty($items->item($i)->getElementsByTagName('StartPrice')->item(0)->nodeValue))
                        $startPrice = $items->item($i)->getElementsByTagName('StartPrice')->item(0)->nodeValue;
                    else $startPrice = '';
                    $currentPrice = $items->item($i)->getElementsByTagName('CurrentPrice')->item(0)->nodeValue;

                    if ( $listType !='ActiveList' )
                        $extra = ' [<a href="" class="relist">relist</a>]';
                    elseif ($listType =='ActiveList')
                        $extra = ' <br><button class="relist btn-sm btn btn-primary mr-2 endlisting"><i class="fas fa-times"></i></button><button class="relist btn-sm btn btn-primary editlisting"><i class="fas fa-edit"></i></button>';

                    $product = Product::select('p_qty')->where('id',$product_id)->first();
                    $data[] = array(
                        "<img src='$img'>",
                        "<a target='_blank' href='http://www.ebay.com/itm/$itemId'>$itemId</a>". $extra,
                        "<a target='_blank' href='/admin/products/$product_id/edit'>$product_id</a>",
                        $itemTitle,
                        $listingType == "Chinese" ? 'Auction' : 'Buy Now',
                        1,
                        '$'.number_format($currentPrice,2)
                    );
                }
            }
          }
         }
        }


        if ($totalNumberOfPages-1>=$pageNumber) {
            $data = $this->eBayItems('load_active_items',$pageNumber+1,$data,$totalEntries);
        }
        return $data;
    }

    public function loadItems(Request $request) {
        $data = $this->eBayItems('load_active_items',1);

        $arr = array ("data"=>$data);
        return response()->json($arr);
    }

    public function saveTemplate(Request $request) {
        if ($request->ajax()) {

            $my_file = base_path().'/resources/views/admin/ebay/templates/'.$request['template_name'] . '.txt';
            $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);

            //parse_str($request['dateinfo'],$output);
            $data = $request['dateinfo'];
            fwrite($handle, stripcslashes($data));
            fclose($handle);

            return response()->json ('Successfully created a new Template.');
        }

        return response()->json('There was a problem saving file.');
    }

    public function loadOriginalTemplate(Request $request) {
        if ($request->ajax()) {

            $templates = glob(base_path().'/resources/views/admin/ebay/templates/*.txt');
            $txt = '';

            if ($templates) {
                foreach ($templates as $filename) {
                    if ( strpos($filename,$request['template_name']) > 0 ) {

                        $fh = fopen($filename,'r');

                        while ($line = fgets($fh)) {
                        $txt .= $line;
                        }
                        fclose($fh);
                        break;
                    }
                }
                return response()->json($txt);
            }

            return response()->json('There was a problem saving file.');
        }
    }

    public function getAjaxListing(Request $request) {
        if ($request->ajax()) {
            $total=0;$qty=0;

            //return response()->json($request);
            $listings = EbayListing::whereHas('products', function($query) {
                $query->where('p_qty', '>', 0);
                $query->whereIn('p_condition', array(1,2));
            })->get();

            $flag_production = Config::get('ebay.settings')['flag_production'];
            $location='';$editListingLink='';

            if (!$flag_production)
                $location = 'sandbox.';

            $data=[];
            foreach ($listings as $listing) {
                $product = $listing->products;
                $img = $product->images->first();
                if (count($product->images)) {
                    $image = $product->images->first();
                    $path = "/images/thumbs/".$image->location;
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' title='$image->title' alt='$image->title' src='$path'></a>";
                } else {
                    $image="/images/no-image.jpg";
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
                }
                $group = $product->group_id==0 ? $product->title . ' ' .$product->p_color.' Dial' : $product->title . ' ' .$product->p_color. ' Bezel';
                $group_id = $product->group_id;
                $product_id = $product->id;
                if (!empty($request['blade'])) {
                    $editPath = '<a href="/admin/ebay/'.$product_id.'/create?item='.$listing->listitem.'">'.$product_id.'</a>';
                } else {
                    $editPath = '<a href="/admin/ebay/'.$product_id.'/create">'.$product_id.'</a>';
                }

                if ($listing->status=='active' && empty($request['blade']))
                    $editListingLink='<a href="#" data-id="'.$listing->listitem.'" class="endlisting">End Listing</a> <span>|</span> ';
                else $editListingLink='';

                $data[]=array('',
                    $path,
                    $editPath,
                    $group,$listing->status,
                    $product->p_qty,
                    $editListingLink.
                    '<a href="http://www.'.$location.'ebay.com/itm/'.$listing->listitem.'" target="_blank"  data-id="'.$listing->listitem.'">View</a>'
                );

                if ($product->p_qty>0) {
                    $total +=$product->p_price*$product->p_qty;
                    $qty += $product->p_qty;
                }
            }

            return response()->json(array('data'=>$data,'total'=>'$'.number_format($total,2),'qty'=>$qty));
        }
    }

    public function getAjaxEbayProducts() {
        $total=0;$qty=0;

        $products = Product::with('categories')
            ->where('p_qty','>',0)
            ->where('p_status',0)
            ->where('p_price3P','>',0)
            // ->where('category_id','<>',1)
            //->whereIn('p_condition', array(1,2))
            ->orderBy('p_qty','desc')
            ->orderBy('id','desc')
            ->get();

            foreach ($products as $product) {
                $img = $product->images->first();
                if (count($product->images)) {
                    $image = $product->images->first();
                    $path = "/images/thumbs/".$image->location;
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' title='$image->title' alt='$image->title' src='$path'></a>";
                } else {
                    $image="/images/no-image.jpg";
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
                }
                $group = $product->group_id==0 ? $product->title . ' ' .$product->p_color.' Dial' : $product->title . ' ' .$product->p_color. ' Bezel';
                $group_id = $product->group_id;
                $product_id = $product->id;
                $editPath = '<a href="/admin/ebay/'.$product_id.'/create">'.$product_id.'</a>';
                $condition = Conditions()->get($product->p_condition);

                $data[]=array('',
                    $path,
                    '<a href="/admin/ebay/'.$product_id.'/create">'.$product_id.'</a>',
                    $condition,
                    $group,'$'.number_format($product->p_newprice,0),
                    $product->p_qty
                );

                if ($product->p_qty>0) {
                    $total +=$product->p_price*$product->p_qty;
                    $qty += $product->p_qty;
                }
            }

            return response()->json(array('data'=>$data,'total'=>'$'.number_format($total,2),'qty'=>$qty));
    }

    protected function getItem($item) {
        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
            <GetItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                <RequesterCredentials>
                    <eBayAuthToken>". $AUTH_TOKEN ."</eBayAuthToken>
                </RequesterCredentials>
          <ItemID>$item</ItemID>
          <IncludeItemSpecifics>true</IncludeItemSpecifics>
          <DetailLevel>ReturnAll</DetailLevel>
        </GetItemRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetItem',725);

        dd($response);
        if ($response->Ack == 'Success')  {
            return $response;
        }

        return '';

    }

    public function addItem(Request $request) {
        return response()->json($this->addOrReviseItem($request));
    }

    public function automateAddItem(Request $request) {
        //return response()->json($this->getAllFeatures());

        /* Sample XML Request Block for minimum AddItem request
        see ... for sample XML block given length*/

        AutomateEbayPost::dispatch($request);

    }

    private function addOrReviseItem($request, $itemId = '') {
        //return response()->json($this->getAllFeatures());

        /* Sample XML Request Block for minimum AddItem request
        see ... for sample XML block given length*/
        $product_id = $request['product_id'];
        $title = $request['title'];
        if ( strpos ( $title, "&" ) ) $title = str_replace ("&", "&amp;", $title);
        $description = $request['desc'];
        $description = str_replace("%mainImage%",$request['image'],$description);

        //return $description;
        $price = str_replace('$','', $request['price']);
        $product = Product::find($product_id);
        if ($product->p_newprice < 1000)
            $price = round($product->p_newprice+($product->p_newprice*0.15)+500);
        elseif ($product->p_newprice > 1000 && $product->p_newprice < 7500)
            $price = round($product->p_newprice+($product->p_newprice*0.065)+400);
        else $price = round($product->p_newprice+($product->p_newprice*0.03)+300);

        $reservedPrice = $request["reservedPrice"] ? "<ReservePrice>" . $request['reservedPrice'] . "</ReservePrice>" : "" ;

        $specifics ='';
        if (isset($request['specifics'])) {
            parse_str (implode('&',$request['specifics']), $output);
            foreach ($output as $key=>$value) {
                $specifics .= "<NameValueList>";
                $specifics .= "<Name>".str_replace("_"," ",$key)."</Name>";
                if (strpos($value,',') !== false) {
                    $values = array();

                    $values = explode(',',$value);
                    foreach ($values as $v) $specifics .= "<Value>$v</Value>";

                } else $specifics .= "<Value>$value</Value>";
                $specifics .= "</NameValueList>";
            }
        }

        $ebayMain = new eBayMain;
        $AUTH_TOKEN = $ebayMain->getToken();

        if (empty($AUTH_TOKEN))
            die ("Your eBay Account is not linked with eBay Tool. You must authorize eBay Tool to be linked to your eBay account. For more information, please contact the software developer.");

        // Create unique id for adding item to prevent duplicate adds
        $uuid = md5(uniqid());
        $addCatID = $request['catId'];
        $listingType = $request['listingType'];
        $duration = $request['duration'];
        $quantity = $request['quantity'] ? $request['quantity'] : 1;
        $imageURLs = '';
        $storeCategoryID = '';
        $condition = $request['condition'];
        $conditionDescription = $request['conditionDescription'];
        if ($listingType == 'FixedPriceItem')
            $requestType = "AddFixedPriceItem";
        else $requestType = "AddItem";

        $ScheduleTime = $request['schedule'];
        if ($ScheduleTime) {
            //$now = new DateTime($ScheduleTime, new DateTimeZone('America/New_York'));
            //$ScheduleTime = date('yyyy-MM-ddTHH:mm:00.000Z', strtotime($now));

            date_default_timezone_set('America/New_York');
            setlocale(LC_TIME, 'en_US');
            $ScheduleTime = date("m/d/Y H:i", strtotime($ScheduleTime));

            $datetime = explode(' ', $ScheduleTime);
            $date = explode("/",$datetime[0]);
            $time = explode(":",$datetime[1]);

            $ScheduleTime = gmstrftime("%Y-%m-%dT%H:%M:%S", mktime($time[0]+3,$time[1], 00, $date[0], $date[1], $date[2]));
            $ScheduleTime = "<ScheduleTime>" . $ScheduleTime . "</ScheduleTime>";
        }

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
            return 'One or more fields in your options is missing. Please click on Settings and fill all the necessary fields and try again.';
            }

        If ( isset($request['storeCatId']) && $request['storeCatId'] )
            $storeCategoryID = "<Storefront><StoreCategoryID>" . $request['storeCatId'] . "</StoreCategoryID></Storefront>";

        $product=Product::find($product_id);
        $retail = $product->p_retail;

        foreach ($product->images as $image) {
            $productImages[] = $this->serverName().'/images/'.$image->location;
        }


        $images = eBayHelper::UploadPictures($productImages);

        if ($images['error'] == "Error")
            return $images['response'];

        foreach ($images['response'] as $image) {
            $imageURLs .=  "<PictureURL>" . $image . "</PictureURL>";
        }

        $revisedItem='';

        if ($itemId)
            $revisedItem = "<ItemID>" . $itemId . "</ItemID>";

        // create the XML request
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<".$requestType."Request xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<RequesterCredentials>";
        $xmlRequest .= "<eBayAuthToken>" . $AUTH_TOKEN . "</eBayAuthToken>";
        $xmlRequest .= "</RequesterCredentials>";
        $xmlRequest .= "<ErrorLanguage>en_US</ErrorLanguage>";
        $xmlRequest .= "<WarningLevel>High</WarningLevel>";
        $xmlRequest .= "<Item>";
        $xmlRequest .= $revisedItem;
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
        if ($specifics) {
            $xmlRequest .= "<ItemSpecifics>";
            $xmlRequest .= $specifics;
            $xmlRequest .= "</ItemSpecifics>";
        }
        $xmlRequest .= "<Title>" . $title . "</Title>";
        //$xmlRequest .= "<BuyerRequirementDetails><ShipToRegistrationCountry>true</ShipToRegistrationCountry></BuyerRequirementDetails>";
        $xmlRequest .= $storeCategoryID;
        $xmlRequest .= "<Description><![CDATA[" . stripcslashes($description). "]]></Description>";
        $xmlRequest .= "<PrimaryCategory>";
        $xmlRequest .= "<CategoryID>" . $addCatID . "</CategoryID>";
        $xmlRequest .= "</PrimaryCategory>";
        $xmlRequest .= "<PrivateListing>true</PrivateListing>";
        //$xmlRequest .= "<AutoPay>true</AutoPay>";
        if ($listingType != 'Chinese' && $request["offer"] == "true") {
            $xmlRequest .= "<BestOfferDetails>";
            $xmlRequest .= "<BestOfferEnabled>true</BestOfferEnabled>";
            $xmlRequest .= "</BestOfferDetails>";
        }
        if ($product->categories->category_name=='Rolex')
            $bestOffer = $price - 200;
        else $bestOffer = $price-500;

        $xmlRequest .= "<ConditionDescription>". $conditionDescription."</ConditionDescription>";
        $xmlRequest .= "<ListingDetails><MinimumBestOfferPrice>". $bestOffer . "</MinimumBestOfferPrice></ListingDetails>";
        $xmlRequest .= "<StartPrice>" . $price . "</StartPrice>";
        $xmlRequest .= $reservedPrice;
        $xmlRequest .= "<ConditionID>".$condition."</ConditionID>";
        $xmlRequest .= "<CategoryMappingAllowed>true</CategoryMappingAllowed>";
        $xmlRequest .= "<Country>US</Country>";
        $xmlRequest .= "<Currency>USD</Currency>";
        $xmlRequest .= "<DispatchTimeMax>".$settings['handle_time']."</DispatchTimeMax>";
        $xmlRequest .= "<ListingDuration>".$duration."</ListingDuration>";
        $xmlRequest .= "<ListingType>".$listingType."</ListingType>";
        // $xmlRequest .= "<PaymentMethods>PayPal</PaymentMethods>";
        // $xmlRequest .= "<PayPalEmailAddress>".$settings['paypal_email']."</PayPalEmailAddress>";
        $xmlRequest .= "<PictureDetails>";
        $xmlRequest .= "<GalleryType>Gallery</GalleryType>".$imageURLs;
        $xmlRequest .= "</PictureDetails>";
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
        //      $xmlRequest .= "<Description>".$settings['return_details']."</Description>";
        $xmlRequest .= "<RefundOption>MoneyBackOrExchange</RefundOption>";
        $xmlRequest .= "<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>";
        $xmlRequest .= "<ReturnsWithinOption>Days_".$settings['return_days']."</ReturnsWithinOption>";
        $xmlRequest .= "<RestockingFeeValueOption>".$settings['restocking_fee']."</RestockingFeeValueOption>";
        $xmlRequest .= "<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>";
        $xmlRequest .= "</ReturnPolicy>";
        $xmlRequest .= $ScheduleTime;
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
        $xmlRequest .= "<SKU>$product_id</SKU>";
        $xmlRequest .= "<Site>US</Site>";
        $xmlRequest .= "<UUID>" . $uuid . "</UUID>";
        $xmlRequest .= "</Item>";
        $xmlRequest .= "<WarningLevel>High</WarningLevel>";
        $xmlRequest .= "</".$requestType."Request>";
        \Log::debug($xmlRequest);
        //return response()->json($xmlRequest);
        $response = $ebayMain->sendHeaders($xmlRequest,$requestType);
        $json = json_encode($response);
        // return $response;
        // $array = json_decode($json,TRUE);
        \Log::debug($json);

        if ($response->Ack == 'Success' || $response->Ack == 'Warning')  {
            if ($response->Errors) {
                //$msg = ($response->Errors->LongMessage);
                if ($response->ItemID) {
                    EbayListing::create([
                        "product_id" => $product_id,
                        "listitem" => $response->ItemID,
                        "listprice" => $price,
                        "status" => 'active',
                        'endtime' => date('Y-m-d H:i:s',date("U",strtotime($response->EndTime)))
                    ]);

                    $product->update([
                        'p_status'=>1
                    ]);
                    $msg = "\r\n\r\n"."A new item has been created Item #: <a href='http://www.ebay.com/itm/$response->ItemID' target='_blank'>$response->ItemID</a>";
                }
                return $msg;
            } else {
                EbayListing::create([
                    "product_id" => $product_id,
                    "listitem" => $response->ItemID,
                    "listprice" => $price,
                    "status" => 'active'
                ]);

                $product->update([
                    'p_status'=>1
                ]);
                return "A new item has been created Item #: <a href='http://www.ebay.com/itm/$response->ItemID' target='_blank'>$response->ItemID</a>";
            }
        } else return 'Error Code: '.$response->ErrorCode. '<br>' . $response->LongMessage;

    }

    static function EndItem($request) {
        if (isset($request['datainfo'])) {
            parse_str($request['datainfo'],$output);
            $ItemID = $output["itemID"];
        }

        if (!isset($output["reason"]))
            return response()->json(array('error'=>'1','message'=>'Please specify the reason to end the listing.'));

        $ebayMain = new eBayMain;
        $EndingReason = $output["reason"];
        $AUTH_TOKEN = $ebayMain->getToken();
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                        <EndItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
                             <RequesterCredentials>
                        <eBayAuthToken>$AUTH_TOKEN</eBayAuthToken>
                        </RequesterCredentials>
                        <EndingReason>$EndingReason</EndingReason>
                        <ItemID>$ItemID</ItemID>
                    </EndItemRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'EndItem');

        if ($response == 'Success')  {
            $listings = EbayListing::where('listitem',$ItemID);

            $item = $listings->first();
            if ($item) {
                Product::find($item->product_id)->update([
                    'p_status' => 0
                ]);
            }
            $listings->delete();
        }

        return $response;
    }

    public function EndOneItem(Request $request) {
        $response = $this->EndItem($request);

        if ($response->Ack == 'Success')
            return response()->json(array('error'=>'0','message'=>'You have successfully ended the item'));
        else
            return response()->json(array('error'=>'1','message'=>$response->ShortMessage));


    }

    public function RelistItem(Request $request) {
        return view('admin.relistitem',['pagename' => 'eBay Relist Item']);
    }

    public function edit($id,$ebayItemId,Request $request) {
        $product = Product::find($id);
        $ebaySettings = EbaySettings::first();
        $itemId = '';
        $txt = '';

        if (isset($request['item']))
            $itemId = $request['item'];

        $isListed = EbayListing::where('product_id', $id)->exists();
        $setup = Config::get('ebay.settings');

        $settings = array(
            'has_store'=>$ebaySettings->has_store,
            'flag_production'=>$setup['flag_production']
        );

        $response = $this->getItem($ebayItemId);

        $item = $response->Item;

        $doc = new \DOMDocument;
        $doc->loadHTML($item->Description);

        $xpath = new \DOMXPath($doc);
        $node = $xpath->query('//div[contains(@id,"description")]')->item(0);
        $shortDescription = trim(preg_replace('/\t\/n\"+/', '', $node->nodeValue));

        $match = array();
        preg_match_all('/<table.*?>(.*?)<\/table>/si', $item->Description, $match);
        $production = $match[1][0];

        $categories = explode(':',$item->PrimaryCategory->CategoryName);
        $i = 0;$txt='';$j=0;

        foreach ($categories as $category) {
            $cats = EbayCategory::where("CategoryName", $category)->first();
            if ($cats) {
                $CategoryID = $cats->CategoryID;
                $cats = EbayCategory::whereRaw("CategoryParentID=$CategoryID AND CategoryID<>$CategoryID")->get();
                if (!$cats->isEmpty()) {
                    $txt .= "<select size=\"8\" id=\"category_$CategoryID\"class=\"primary_category\">";

                    foreach ($cats as $cat) {
                        //echo $cat->CategoryName.' ' . $categories[$j+1] . '<br>';

                        if ($cat->CategoryName == $categories[$j+1])
                            $txt .= "<option selected value='".$cat->CategoryID."'>$cat->CategoryName</option>";
                        else $txt .= "<option value='".$cat->CategoryID."'>$cat->CategoryName</option>";
                        $i++;

                    }
                    $txt .= '</select>';
                    $j++;
                }
            }
        }

        // foreach ($item->ItemSpecifics as $specific) {

        // }

        return view('admin.ebay.edit',[
            'pagename' => 'Edit Ebay Listing',
            'product' => $product,
            'settings' => $settings,
            'categories'=>$txt,
            'item' => $item,
            'description'=>$shortDescription,
            'production'=>$production]
        );


    }

    public function create($id,Request $request) {
        $product = Product::find($id);
        $ebaySettings = EbaySettings::first();
        $itemId = '';
        $txt = '';

        if (isset($request['item']))
            $itemId = $request['item'];

        $isListed = EbayListing::where('product_id', $id)->exists();

        $setup = Config::get('ebay.settings');

        $settings = array(
            'has_store'=>$ebaySettings->has_store,
            'flag_production'=>$setup['flag_production']
        );

        return view('admin.ebay.create',['pagename' => 'New Ebay Listing','product' => $product,'itemId'=>$itemId,'settings' => $settings,'isListed'=>$isListed]);
    }

    public function accepturl(Request $request) {
        // $settings = Config::get('ebay.settings');
        // $runame = $settings['runame'];
        // $secret_code = '4d04a43f-fd2d-4545-b76a-d3d99af075a7';
        // $client_id = 'EdwardBa-dbe1-4a78-8848-5433a7bddb11';
        // $code = urldecode($request['code']);

        // $url='https://auth.ebay.com/oauth2/authorize?client_id='.$client_id.'&redirect_uri='.$runame.'&response_type=code&scope=https://api.ebay.com/oauth/api_scope/sell.account.readonly';

        //$url = 'https://signin.ebay.com/ws/eBayISAPI.dll?oAuthRequestAccessToken&client_id='.$client_id.'&redirect_uri='.$runame.'&client_secret='.$secret_code.'&code='.$code;
        $url = '';
        return view('admin.ebay.accepturl',['pagename' => 'EBAY Authentication','url'=>$url]);
    }

    public function fetchToken(Request $request) {
        $SessionID = $request['sessionid'];
        $runame = $request['runame'];

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<FetchTokenRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<SessionID>$SessionID</SessionID>";
        $xmlRequest .= "</FetchTokenRequest>";

        $ebayMain = new eBayMain;
        $response = $ebayMain->sendHeaders($xmlRequest,'FetchToken');

        if ($response->Ack == "Success" && $response->eBayAuthToken) {

            $date = date_parse($response->HardExpirationTime);

            $ebaySettings = EbaySettings::where('id',0);
            $ebaySettings->update ([
                'token' => $response->eBayAuthToken,
                'experation_date' => $date['year'].'-'.$date['month'].'-'.$date['day']
            ]);

            return response()->json (array('error'=>'','message'=>'The information was saved successfully.'));
        }

        return response()->json (array('error'=>'error','message'=>$response->ErrorCode. '<br>' . $response->ShortMessage . '<br>' . $response->LongMessage));
    }

    public function get_eBayToken () {
        /*$username = 'edba1970';

        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<GetRuNameRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<RequesterCredentials>";
        $xmlRequest .= "<Username>". $username ."</Username>";
        $xmlRequest .= "<Password>". $password ."</Password>";
        $xmlRequest .= "</RequesterCredentials>";
        $xmlRequest .= "</GetRuNameRequest>";

        $response = $ebayMain->sendHeaders($xmlRequest,'GetRuName');
        return response()->json($response);
        */
        //parse_str($_COOKIE['user_login'],$output);
        $settings = Config::get('ebay.settings');
        $runame = $settings['runame'];

        $SessionID = $this->SetReturnURL ($runame);

        $arr = array("runame" => $runame, "sessionid" => $SessionID);
       return response()->json($arr);
    }

    public function SetReturnURL ($RuName, $username='',$password='') {
        /*
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<SetReturnURLRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<Version>".API_COMPATIBILITY_LEVEL."</Version>";
        $xmlRequest .= "<RequesterCredentials>";
        $xmlRequest .= "<Username>$username</Username>";
        $xmlRequest .= "<Password>$password</Password>";
        $xmlRequest .= "</RequesterCredentials>";
        $xmlRequest .= "<AuthenticationEntry>";
        $xmlRequest .= "<AcceptURL>https://swissmadecorp.com/admin/ebay/accepturl</AcceptURL>";
        $xmlRequest .= "<RejectURL>https://swissmadecorp.com/admin/ebay/accepturl</RejectURL>";
        $xmlRequest .= "<RuName>$RuName</RuName>";
        $xmlRequest .= "<TokenReturnMethod>FetchToken</TokenReturnMethod>";
        $xmlRequest .= "<DisplayTitle>$username></DisplayTitle>";
        $xmlRequest .= "</AuthenticationEntry>";
        $xmlRequest .= "<Action>Add</Action>";
        $xmlRequest .= "</SetReturnURLRequest>";

        $response = sendHeaders($xmlRequest,'SetReturnURL');
        */
        return $this->GetSessionID ($RuName);
    }

    public function GetSessionID ($RuName) {
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xmlRequest .= "<GetSessionIDRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
        $xmlRequest .= "<RuName>$RuName</RuName>";
        $xmlRequest .= "</GetSessionIDRequest>";

        $ebayMain = new eBayMain;
        $response = $ebayMain->sendHeaders($xmlRequest,'GetSessionID');

        return $response->SessionID;

    }

    public function template() {
        return view('admin.ebay.templates.template');
    }
}
