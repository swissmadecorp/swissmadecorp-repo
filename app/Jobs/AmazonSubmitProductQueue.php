<?php

namespace App\Jobs;

use App\Product;
use App\AmazonListings;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use KeithBrink\AmazonMws\AmazonFeed;
use KeithBrink\AmazonMws\AmazonFeedResult;

class AmazonSubmitProductQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $config = [
        'merchantId' => 'A3B398SMXTGHH8',
        'marketplaceId' => 'ATVPDKIKX0DER',
        'keyId' => 'AKIAI6XPH223AM2F7MLA',
        'secretKey' => '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',
        'amazonServiceUrl' => 'https://mws.amazonservices.com/',
    ];

    private $product_id;
    private $listprice;
    private $marketLocation;

    private function submitFeed($feedType, $feed) {
        $amz = new AmazonFeed("store1"); //store name matches the array key in the config file
        if ($this->marketLocation == "CA") {
            $this->config['marketplaceId'] = "A2EUQ1WTGCTBG2";
            $this->config['amazonServiceUrl'] = "https://mws.amazonservices.ca";
        }
        $amz->setConfig($this->config);
        $amz->setFeedType($feedType); //feed types listed in documentation
        $amz->setFeedContent($feed);

        $amz->submitFeed();
        
        $amz->getResponse();
    }

    private function submitInventory($sku,$qty) {
      
        $inventory = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
            <AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">
            <Header>
                <DocumentVersion>1.02</DocumentVersion>
                <MerchantIdentifier>A3B398SMXTGHH8</MerchantIdentifier>
            </Header>
            <MessageType>Inventory</MessageType>
            <Message>
                <MessageID>1</MessageID>
                <OperationType>Update</OperationType>
                <Inventory>
                    <SKU>".$sku."</SKU>
                    <Quantity>".$qty."</Quantity>
                    <FulfillmentLatency>1</FulfillmentLatency>
                </Inventory>
            </Message>
        </AmazonEnvelope>";
        
        $this->submitFeed("_POST_INVENTORY_AVAILABILITY_DATA_",$inventory);
    } 

    private function submitPrice($sku, $price) {
        $price = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
        <AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">
            <Header>
                <DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>A3B398SMXTGHH8</MerchantIdentifier>
            </Header>
            <MessageType>Price</MessageType>
            <PurgeAndReplace>false</PurgeAndReplace>
            <Message>
                <MessageID>1</MessageID>
                <OperationType>Update</OperationType>
                <Price>
                    <SKU>".$sku."</SKU>
                    <StandardPrice currency=\"USD\">".$price."</StandardPrice>
                </Price>
            </Message>
        </AmazonEnvelope>";
        
        $this->submitFeed("_POST_PRODUCT_PRICING_DATA_",$price);
    }

    private function submitImage($sku) {
        
        $productImage = \DB::table('product_image')
            ->join('images','images.id','image_id')
            ->where('product_id',"=",$sku)
            ->orderBy('position','asc')
            ->get();

        $addImage = ''; $i=0; $website =  \URL::to('/')  . '/public/images/';
        $firstImage='';

        if ($productImage->count() > 1) {
            foreach ($productImage as $image) {
                if ($i==0)
                    $firstImage = $website . $image->location;
                else $addImage .= "<ImageType>PT".$i."</ImageType>\n" .
                                  "<ImageLocation>".$website . $image->location."</ImageLocation>\n";

                $i++ ;
            }
        } else {
            foreach ($productImage as $image) {
                $firstImage = $website . $image->location;
            }
        }

        $imageXML = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
            <AmazonEnvelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"amzn-envelope.xsd\">
                <Header>
                    <DocumentVersion>1.01</DocumentVersion>
                    <MerchantIdentifier>A3B398SMXTGHH8</MerchantIdentifier>
                </Header>
                <MessageType>ProductImage</MessageType>
                <PurgeAndReplace>false</PurgeAndReplace>
                <Message>
                <MessageID>1</MessageID> 
                <OperationType>Update</OperationType> 
                <ProductImage>
                    <SKU>".$sku."</SKU> 
                    <ImageType>Main</ImageType> 
                    <ImageLocation>".$firstImage."</ImageLocation>\n"
                    . $addImage . "\n" .
                "</ProductImage>
                </Message>
            </AmazonEnvelope>";

        $this->submitFeed("_POST_PRODUCT_IMAGE_DATA_",$imageXML);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_id,$listprice,$marketLocation)
    {
        $this->product_id = $product_id;
        $this->listprice = $listprice;
        $this->marketLocation = $marketLocation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {     
        if ($this->marketLocation == "CA") {
            $this->config['marketplaceId'] = "A2EUQ1WTGCTBG2";
            $this->config['amazonServiceUrl'] = "https://mws.amazonservices.ca";
        }
        
        $qty = 0;
        if ($this->listprice != 0) {
            $amazon_listing = AmazonListings::latest()->first();
            $feedId = $amazon_listing->submissionId;
            $amz = new AmazonFeedResult('store1',$feedId);
            $amz->setFeedId($feedId);
            $amz->setConfig($this->config);
            
            $response = $amz->fetchFeedResult();

            $responseDoc = new \DomDocument();
            $responseDoc->loadXML($response);
            $responseXML = $responseDoc->getElementsByTagName("MessagesWithError");

            // Log::debug($responseXML->item(0)->nodeValue);

            if ($responseXML->item(0)->nodeValue==0) {
                $product=Product::find($this->product_id)->update([
                    'p_status'=>7 // Update product status to Amazon
                ]);
                
                Log::debug("Successfully sumbitted to Amazon");
                Log::debug($response);
            } else {
                $responseXML = $responseDoc->getElementsByTagName("ResultDescription");
                echo $responseXML->item(0)->nodeValue;
                Log::debug("There was some problem submitting to Amazon. Check below for information.");
                Log::debug($response);
            } 

        
            $this->submitPrice($this->product_id,$this->listprice);
            $this->submitImage($this->product_id);
            $qty = 1;
        }

        $this->submitInventory($this->product_id,$qty);
    }
}
