<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\Notifiable;
use App\Notifications\InvoicePaid;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use KeithBrink\AmazonMws\AmazonFeed;
use KeithBrink\AmazonMws\AmazonFeedResult;

class ImportController extends Controller
{
    use Notifiable;
    protected $file = '';

    public function index() {
        //$order= Product::find(4039);
        //\Notification::route('mail','info@swissmadecorp.com')->notify(new InvoicePaid($order));

        //return view('admin.import',['pagename'=>"Import"]);

        $config = [
            'merchantId' => 'A3B398SMXTGHH8',
            'marketplaceId' => 'ATVPDKIKX0DER',
            'keyId' => 'AKIAI6XPH223AM2F7MLA',
            'secretKey' => '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',
            'amazonServiceUrl' => 'https://mws.amazonservices.com/',
        ];

        // $feedId = '57280017876';
        // $amz = new AmazonFeedResult('store1',$feedId);
        // $amz->setFeedId($feedId);
        // $amz->setConfig($config);
        
        // try {
        //     $amz->fetchFeedResult();
        //     return $amz->getRawFeed();
        // } catch (Exception $x) {
        //     return 'fetch not ready!';
        // }

$inventory = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
    <Header>
        <DocumentVersion>1.02</DocumentVersion>
        <MerchantIdentifier>A3B398SMXTGHH8</MerchantIdentifier>
    </Header>
    <MessageType>Inventory</MessageType>
    <Message>
        <MessageID>1</MessageID>
        <OperationType>Update</OperationType>
        <Inventory>
            <SKU>4363</SKU>
            <Quantity>1</Quantity>
            <FulfillmentLatency>1</FulfillmentLatency>
        </Inventory>
    </Message>
</AmazonEnvelope> 
EOD;

$feed = 
"<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
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
            <SKU>4364</SKU>
            <StandardProductID>
                <Type>UPC</Type>
                <Value>782150701166</Value>
            </StandardProductID>
            <ProductTaxCode>A_GEN_TAX</ProductTaxCode>
            <DescriptionData>
                <Title>Blancpain Villeret Quantieme Annual GMT 40 mm 6670-3642-55B New Men's Watch</Title>
                <Brand>Blancpain</Brand>
                <Description>New Men's Blancpain Villeret Quantieme Annual GMT 40 mm 6670-3642-55B leather strap, 18k rose gold material, with silver face.</Description>
                <BulletPoint>GMT</BulletPoint>
                <BulletPoint>Date</BulletPoint>
                <BulletPoint>Day</BulletPoint>
                <BulletPoint>Month</BulletPoint>
                <MSRP currency=\"USD\">21000</MSRP>
                <Manufacturer>Blancpain</Manufacturer>
                <MfrPartNumber>782150701166</MfrPartNumber>
                <ItemType>Wrist Watches</ItemType>
            </DescriptionData>
            <ProductData>
                <Jewelry>
                    <ProductType>
                        <Watch>
                            <BandColor>brown</BandColor>
                            <BandMaterial>leather</BandMaterial>
                            <TargetGender>male</TargetGender>
                            <SpecialFeatures>none</SpecialFeatures>
                            <DepartmentName>men</DepartmentName>
                        </Watch>
                    </ProductType>
                </Jewelry>
            </ProductData>
        </Product>
    </Message>
</AmazonEnvelope>";

$price = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
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
      <SKU>4363</SKU>
      <StandardPrice currency="USD">20300</StandardPrice>
    </Price>
  </Message>
</AmazonEnvelope>
EOD;

$image = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
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
        <SKU>4363</SKU> 
        <ImageType>Main</ImageType> 
        <ImageLocation>https://swissmadecorp.com/public/images/rolex-air-king-14000-23444.JPG</ImageLocation>
    </ProductImage>
    </Message>
</AmazonEnvelope>
EOD;

//_POST_PRODUCT_DATA_, _POST_INVENTORY_AVAILABILITY_DATA_,_POST_PRODUCT_PRICING_DATA_,_POST_PRODUCT_IMAGE_DATA_

        $amz = new AmazonFeed("store1"); //store name matches the array key in the config file
        $amz->setConfig($config);
        $amz->setFeedType("_POST_PRODUCT_IMAGE_DATA_"); //feed types listed in documentation
        $amz->setFeedContent($image);
        $amz->submitFeed();
        return $amz->getResponse();
    }

    public function import(Request $request) {
        $filename = base_path() . '/public/uploads/'.$request['filename'];

        ini_set('auto_detect_line_endings',TRUE);
        $handle = fopen($filename,'r');
        while ( ($data = fgetcsv($handle) ) !== FALSE ) {
            if ($data[0]!='Stock number #') {
                // $path = explode("https://",$data[11]);
                // if (is_array($path) && count($path)>1) {
                //     if (substr($path[2],-4,4)=='jpgn') {
                //         $data[11]=$path[2];
                //     } else
                //         $data[11]='';
                // }
                
                $product=Product::find($data[0]);
                if (!$product) 
                    $this->saveProduct($data);
            }
        }

        ini_set('auto_detect_line_endings',FALSE);
        return back();
    }

    private function saveProduct($data) {
        //$box = substr()

        //$papers = isset($request[12]) && $request[12] == 'on' ? 1 : 0;
        $slug = '';

        $r=rand(11111, 99999);
        $category = \App\Models\Category::where('category_name','=',$data[1])->first();
        $g = priceToLetters($data['8']);
        $slug =  strtolower(str_replace([' ','&'],'-',$category->category_name.'-'.$data[2].'-'.$data[10].'-'.$g.'-'.$r));
        $title = $category->category_name.' '.$data[2].' '.$data[5];

        if (strpos($slug,'--')>0)
            $slug = str_replace('--','-',$slug);
        if (strpos($slug,'--')>0)
            $slug = str_replace('--','-',$slug);

        switch ($data[4]) {
            case 'SS':
                $material = 17;
                break;
            case '18KYG/SS':
                $material = 5;
                break;
            case '18KYG':
                $material = 4;
                break;
            case '18KRG':
                $material = 12;
                break;
            case '18KRG/SS':
                $material = 14;
                break;
            case '18KWG':
                $material = 8;
                break;
            case '18KWG/SS':
                $material = 10;
                break;
            case '14KYG/SS':
                $material = 7;
                break;
            case '14KYG':
                $material = 6;
                break;
            case '14KRG':
                $material = 13;
                break;
            case '14KRG/SS':
                $material = 15;
                break;                
            case '14KWG':
                $material = 9;
                break;
            case '14KWG/SS':
                $material = 11;
                break;                
            default:
                $material=0;
        }

        $box=0;$paper=0;
        $boxpapers = substr($data[12],0,2);
        $boxpapers1 = substr($data[12],2);
        
        if ($boxpapers=='BY')
            $box = 1;
        
        if ($boxpapers1='PY')
            $paper = 1;
        
        $params = array(
            'id' => $data[0],
            'category_id' => $category->id,
            'p_model' => $data[2],
            'p_gender' => $data[3],
            'p_material' => $material,
            'p_reference' => $data[5],
            'p_serial' => $data[7],
            'p_price' => $data[8],
            'p_retail' => $data[9],
            'p_box' => $box,
            'p_papers' => $paper,
            'p_qty' => 1,
            'supplier' => $data[6],
            'p_status' => 0,
            'slug' => $slug
        );

        $supplier = \App\Models\Supplier::where('company','=',$data[6])->get();
        
        if (count($supplier)==0) {
            \App\Models\Supplier::create([
                'company'=>$data[6]
            ]);
        }
        
        $product = Product::create($params);
        
        // if (substr($data[11],-4)=='jpgn'){
        //     $image=\App\Image::create([
        //         'title' => $title,
        //         'location' => str_replace('jpgn','jpg',$data[11]),
        //         'position' => 0
        //     ]);
        //     $product->images()->attach($image->id);
        // }

    }

    public function upload(Request $request) {
        $fileName=$request->file('csvfile')->getClientOriginalName();
        request()->file('csvfile')->move(base_path() . '/public/uploads/',$fileName);

        $this->file = $fileName;

        $request->session()->put('filename',$fileName);
        $request->session()->flash('message', 'Successfully uploaded file: '.$fileName);

        return back();
            
    }
}
