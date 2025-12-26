<?php

namespace App\Http\Controllers;

use App\Models\AmazonListings;
use Session;
use App\Mail\EmailCustomer;
use Illuminate\Support\Facades\Mail;
use App\Jobs\AmazonSubmitProductQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Image;
use App\Libs\WatchFacts;
use DB;
use Carbon\Carbon;
use KeithBrink\AmazonMws\AmazonFeed;
use KeithBrink\AmazonMws\AmazonFeedResult;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use KeithBrink\AmazonMws\AmazonProductSearch;
use GuzzleHttp\Client;

class AmazonController extends Controller
{
    protected $config = [
        'merchantId' => '',
        'marketplaceId' => '',
        'keyId' => '',
        'secretKey' => '',
        'amazonServiceUrl' => 'https://mws.amazonservices.com/',
    ];

    public function amazonListings() {
        return view('admin.amazonlistings',['pagename' => 'Create New Amazon Listings']);
    }

    public function amazonEndListings() {
        return view('admin.amazonendlisting',['pagename' => 'Amazon Active Listings']);
    }

    public function index()
    {
        return view('admin.amazon',['pagename' => 'Amazon Page']);
    }

    public function displayListItems() {
        $listings = Product::with('images')
            ->selectRaw("products.*,amazon_listings.listprice")
            ->join('amazon_listings','products.id','=','product_id')
            ->get();

        foreach ($listings as $listing) {
            $product_id = $listing->id;
            $img = $listing->images->first();

            if ($img) {
                $image = $listing->images->first();
                $path = '/images/thumbs/'.$image->location;
            } else {
                $path="/images/no-image.jpg";
                //$path = "<a href='/$listing->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
            }

            $data[] = array(
                "<img style='width: 80px' src='$path'>",
                "<a target='_blank' href='/admin/products/$product_id/edit'>$product_id</a>",
                $listing->title,
                '$'.number_format($listing->listprice,2)
            );
        }

        return response()->json(array('data'=>$data));
    }

    private function endAmazonItem() {
        $listings = AmazonListings::whereIn('product_id',$product_ids)->get();
        $amazon_listings = $listings->get();
        if ($amazon_listings) {
            foreach ($amazon_listing as $id) {
                dispatch(new AmazonSubmitProductQueue($id,0,'USA'));
            }
            $listings->delete();
        }
    }

    public function getAjaxAmazonProducts() {
        $total=0;$qty=0;

        $products = Product::with('categories')
            ->where('p_qty','>',0)
            //->where('p_status','<>',6)
            // ->where('category_id','<>',1)
            //->whereIn('p_condition', array(1,2))
            ->where('p_newprice','>',0)
            ->orderBy('p_qty','desc')
            ->orderBy('id','desc')
            ->get();

            foreach ($products as $product) {
                $img = $product->images->first();
                if (count($product->images)) {
                    $image = $product->images->first();
                    $path = '/images/thumbs/'.$image->location;
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' title='$image->title' alt='$image->title' src='$path'></a>";
                } else {
                    $image="/images/no-image.jpg";
                    $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
                }
                $group = Conditions()->get($product->p_condition) . ' ' .$product->title . ' ' .$product->p_color.' Dial';
                $group_id = $product->group_id;
                $product_id = $product->id;
                $editPath = '<a href="/admin/amazon/'.$product_id.'/create">'.$product_id.'</a>';
                $data[]=array('',
                    $path,
                    $product_id,
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

    public function create($id) {
        $product = Product::find($id);
        if (!$product)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view('admin.amazon.create',['pagename' => 'Create Amazon Product','product' => $product]);
    }

    private function submitFeed($feedType, $feed) {
        $amz = new AmazonFeed("store1"); //store name matches the array key in the config file
        $amz->setConfig($this->config);
        $amz->setFeedType($feedType); //feed types listed in documentation
        $amz->setFeedContent($feed);

        return $amz->submitFeed();

        //return $amz->getResponse();
    }

    public function getSimilarProductByName(Request $request) {
        if ($request['marketLocation'] == "CA") {
            $this->config['marketplaceId'] = "";
            $this->config['amazonServiceUrl'] = "https://mws.amazonservices.ca";
        }

        $amz = new AmazonProductSearch('store1');
        $amz->setConfig($this->config);
        $amz->setQuery($request['title']);

        $amz->setContextId('Watches');
        $amz->searchProducts();
        foreach ($amz->getProduct() as $product) {
            $description='';$image='';

            foreach ($product->data['AttributeSets'][0] as $category=>$item) {

                if (!is_array($item))
                    $description .= '<b>'.$category.':</b> '.$item.'<br>';
                elseif (isset($item['URL']))
                     $image = str_replace('SL75','SL250',$item['URL']);
            }
            $arr[] = array('ASIN'=>$product->data['Identifiers']['MarketplaceASIN']['ASIN'],'product'=>$description,'image' => $image);
        }

        return response()->json($arr);
    }

    private function receiveNotification() {
        $client = new SqsClient([
            'version'     => 'latest',
            'region'      => 'us-east-2',
            'credentials' => [
                'key'    => '',
                'secret' => '',
            ],
        ]);

        $queueUrl = '';

        try {
            $result = $client->receiveMessage(array(
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => 1,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $queueUrl, // REQUIRED
                'WaitTimeSeconds' => 0,
            ));
            if (count($result->get('Messages')) > 0) {
                var_dump($result->get('Messages')[0]);
                $result = $client->deleteMessage([
                    'QueueUrl' => $queueUrl, // REQUIRED
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle'] // REQUIRED
                ]);
            } else {
                echo "No messages in queue. \n";
            }
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }

    public function verify($submissionId) {
        if (!$submissionId)
            return response()->view('errors/admin-notfound',['submissionId'=>$submissionId],404);
        // $amazon_listing = AmazonListings::latest()->first();
        // $feedId = $amazon_listing->submissionId;
        // $amz = new AmazonFeedResult('store1',$feedId);
        // $amz->setFeedId($feedId);
        // $amz->setConfig($this->config);

        // $response = $amz->fetchFeedResult();

        $uri = "".$submissionId;
        $response = \Httpful\Request::get($uri)->send();

        return $response;
    }

    public function removeProduct(Request $request) {
        if ($request->ajax()) {
            if (!is_array($request['ids'])) return response()->json('Please select at least one item.');

            $sellerId = env('WATCHFACTS_SELLER_ID','');
            $watchFacts = new WatchFacts($sellerId);
            $watchFacts->removeProduct($request['ids']);

            return response()->json('Removed from WatchFacts successfully.');
        }
    }

    public function show(Request $request) {
        //$sellerId = config('watchfacts.sellerId');

    }

    public function submitProduct1(Request $request)  {

        if ($request->ajax()) {
            if (!is_array($request['ids'])) return response()->json('Please select at least one item.');

            $sellerId = config('watchfacts.sellerId');
            $watchFacts = new WatchFacts($sellerId);
            $watchFacts->submitProductToWatchFacts($request['ids']);

            return response()->json('Completed');
        }
    }

    public function submitProduct(Request $request) {
        $output = $request;
        // dispatch(new AmazonSubmitProductQueue(4467,2000,"","USA"));
        // dd('64546');
        if ($output['marketLocation'] == "CA") {
            $this->config['marketplaceId'] = "";
            $this->config['amazonServiceUrl'] = "https://mws.amazonservices.ca";
        }

        if (is_numeric($output['upc']))
            $cat = "UPC";
        else $cat = "ASIN";


        $products = Product::whereIn('id',$request['ids'])->get();
        foreach ($products as $product) {
            $category = $product->categories->category_name;
            $feed = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
            <AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">
                <Header>
                    <DocumentVersion>1.01</DocumentVersion>
                    <MerchantIdentifier>A3B398SMXTGHH8</MerchantIdentifier>
                </Header>
                <MessageType>Product</MessageType>
                <PurgeAndReplace>false</PurgeAndReplace>
                <Message>
                    <MessageID>1</MessageID>
                    <OperationType>Update</OperationType>
                    <Product>
                        <SKU>".$product->id."</SKU>
                        <StandardProductID>
                            <Type>".$cat."</Type>
                            <Value>782150702095</Value>
                        </StandardProductID>
                        <ProductTaxCode>A_GEN_TAX</ProductTaxCode>
                        <DescriptionData>
                            <Title>".$product->title."</Title>
                            <Brand>".$category."</Brand>
                            <Description>".$product->keyword_build."</Description>
                            <BulletPoint>Dial color: ".$product->dialcolor."</BulletPoint>
                            <BulletPoint>Band color: ".$product->bandcolor."</BulletPoint>
                            <BulletPoint>Movement: ".$product->movement."</BulletPoint>
                            <BulletPoint>100% Authentic ".$category."</BulletPoint>";
                            if (!empty($product->p_retail))
                                $feed .="
                                <MSRP currency=\"USD\">".$product->p_retail."</MSRP>";

                            $feed .= "<Manufacturer>".$category."</Manufacturer>
                            <MfrPartNumber>".$product->p_reference."</MfrPartNumber>
                            <ItemType>watches</ItemType>
                        </DescriptionData>
                        <ProductData>
                            <Jewelry>
                                <ProductType>
                                    <Watch>
                                        <BandColor>".$product->bandcolor."</BandColor>
                                        <BandMaterial>".$product->bandmaterial."</BandMaterial>";
                                        if (!empty($product->clasptype))
                                            $feed .="
                                            <ClaspType>".$product->clasptype."</ClaspType>";

                                        if (!empty($product->casematerial))
                                            $feed .="
                                            <CaseMaterial>".$product->casematerial."</CaseMaterial>";

                                        if (!empty($product->casediameter))
                                            $feed .='
                                            <CaseSizeDiameter unitOfMeasure="MM">'.$product->casediameter.'</CaseSizeDiameter>';

                                        $feed .="
                                        <DialColor>".$product->dialcolor."</DialColor>
                                        <BezelMaterial>".$product->bezelmaterial."</BezelMaterial>
                                        <Crystal>antireflective-sapphire</Crystal>
                                        <MovementType>".$product->movement."</MovementType>";

                                        if (!empty($product->waterproof))
                                            $feed .='
                                            <WaterResistantDepth unitOfMeasure="M">'.$product->waterproof.'</WaterResistantDepth>';

                                        $feed .="
                                        <ResaleType>nonauthorized</ResaleType>
                                        <WarrantyType>seller</WarrantyType>
                                        <ItemShape>".$product->itemshape."</ItemShape>
                                        <DisplayType>".$product->displaytype."</DisplayType>
                                        <TargetGender>male</TargetGender>
                                        <DepartmentName>men</DepartmentName>
                                    </Watch>
                                </ProductType>
                            </Jewelry>
                        </ProductData>
                    </Product>
                </Message>
            </AmazonEnvelope>";


            dispatch(new AmazonSubmitProductQueue($product->id,$product->price,$output['marketLocation']));

            // $result = (string) $this->submitFeed("_POST_PRODUCT_DATA_",$feed);
            AmazonListings::updateOrCreate(['product_id'=>$output['id']],[
                'product_id' => $output['id'],
                'listprice' => $output['price'],
                'submissionId' => $result
            ]);
        }
        return redirect('admin/ebay/'.$output['id'].'/create');
    }
}

