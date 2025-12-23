<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Libs\WalmartClass;
use App\Models\Product;
use Carbon\Carbon;
use App\Jobs\ProcessWalmartInventory;

class WalmartController extends Controller
{

    public function show(Request $request) {
      //$sellerId = config('watchfacts.sellerId');
      
    }

    public function getAjaxProducts() {
      $total=0;$qty=0;

      $products = Product::with('categories')
            ->where('p_qty','>',0)
            //->where('p_status','<>',6)
            ->where('category_id','<>',1)
            // ->whereIn('p_condition', array(1,2))
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

    public function index() {
        return view('admin.walmart',['pagename' => 'Walmart Page']);
    }

    public function getActiveItems() {
      $walmart = new WalmartClass();
      $skus = $walmart->getActiveItems();

      $products = Product::select('id')->findMany($skus)->where('p_qty','<', 1)->pluck('id')->toArray();
      dd($products);
    }
    
    public function walmartListing() {
      return view('admin.walmartlisting',['pagename' => 'Create new walmart listing']);
    }

    private function constructDescription($product) {
      ob_start(); ?>
      <ul>
        <li><b>Stock No:</b> <?= $product->id ?></li>
        <li><b>Brand:</b> <?= $product->categories->category_name ?> </li>
        <?php if ($product->p_model) { ?>
          <li><b>Model:</b> <?= $product->p_model ?></li> 
        <?php } ?>
        
        <?php if ($product->p_casesize) { ?>
          <li><b>Case Size:</b> <?= $product->p_casesize ?></li>
        <?php } ?>

        <?php if ($product->p_reference) { ?>
          <li><b>Reference:</b> <?= $product->p_reference ?></li>
        <?php } ?>

        <?php if ($product->serial_code) { ?>
        <li><b>Serial:<b><?= $product->serial_code ?></li>
        <?php } ?>

        <?php if ($product->p_color) { ?>
          <li><b>Face Color:</b><?= $product->p_color ?></li>
        <?php } ?>
        
        <?php if (($product->p_box==0 || $product->p_box==1) && $product->group_id == 0) { ?>
        <li><b>Box:</b><?= $product->p_box==1 ? "Yes" : "No" ?></li>
        <?php } ?>
        
        <?php if (($product->p_papers==0 || $product->p_papers==1) && $product->group_id == 0) { ?>
          <li><b>Papers:</b> <?= $product->p_papers==1 ? "Yes" : "No" ?></li>
        <?php } ?>
        
        <?php if ($product->p_strap>0) { ?>
          <li><b>Strap/Band:</b> <?= Strap()->get($product->p_strap) ?></li>
        <?php } ?>
        
        <?php if ($product->p_clasp>0) { ?>
        <li><b>Clasp Type:</b> <?= Clasps()->get($product->p_clasp) ?></li>
        <?php } ?>
        
        <?php if ($product->p_material>0) { ?>
        <li><b>Case Material:</b> <?= Materials()->get($product->p_material) ?></li>
        <?php } ?>
        
        <?php if ($product->p_bezelmaterial>0) { ?>
        <li><b>Bezel Material:</b> <?= BezelMaterials()->get($product->p_bezelmaterial) ?></li>
        <?php } ?>
        
        <?php if ($product->water_resistance) { ?>
            <li><b>Water Resistance:</b> <?= $product->water_resistance ?></li>
        <?php } ?>
        
        <?php if ($product->movement>-1)  { ?>
          <li><b>Movement:</b> <?= Movement()->get($product->movement) ?></li>
        <?php }

        return ob_get_clean();

    }

    private function getUPC($sku) {
      $filename = base_path().'/public/uploads/new_upcs.dat';
      $file = fopen($filename,'r') or die("Unable to open file!");
      //$upcs = fread($file,filesize($filename));

      $upc = '';
      $temp_filename = base_path().'/public/uploads/temp_upcs.dat';
      $temp_file = fopen($temp_filename,'w');
      $processed = false;$newline='';
      $exists = false;

      while (($line = fgets($file)) !== false) {
        $string = preg_replace('/\s+/', ' ', trim($line));
       
        if (strpos($string,'-')===false && $processed==false) {
          if (!$upc) {
            $newline = $string.'-'.$sku;
            $upc = $string;
            fwrite($temp_file,$newline."\r\n");
            $processed = true;
          } else {
            fwrite($temp_file,$string."\r\n");
          }
        } else {
          if (strpos($string,'-'.$sku)) {
            $upc = str_replace('-'.$sku,'',$string);
            $exists = true;
          }
          fwrite($temp_file,$string."\r\n");
        }
      }

      fclose($temp_file);
      fclose($file);

      rename($temp_filename, $filename);
      return $upc;
    }
    
    public function retireProduct(Request $request) {
      $walmart = new WalmartClass();
      $walmart->retireItem($request['ids']);

      return "<br>Item(s) removed successfully.";
    }

    public function submitProduct(Request $request) {
      $walmart = new WalmartClass();
      
      //$response = $walmart->updateInventory([7116,7114,7113]);
      // $response=$walmart->getFeedStatus('E02AE06D192A4C29955E5E549EF9A1DB@AWcBCgA');
      //$response=$this->getFeedStatus('2CB818A0344D4F79A8BD75A3003E1239@AQMBCgA');
      //return $response;
      
      $time = microtime(true);
      $tMicro = sprintf("%03d",($time - floor($time)) * 1000);
      $tUtc = gmdate('Y-m-d\TH:i:s.', $time).$tMicro.'Z';

      $products = Product::whereIn('id',$request['ids'])->get();
      $item = '';
      
      // $upc = $this->getUPC(6519);
      // return $upc;

      foreach ($products as $product ) {
        $description = $this->constructDescription($product);
        $sku = $product->id;
        $skus[] = $sku;
        $upc = $this->getUPC($sku);
        
        $faceColor = $product->p_color;

        switch ($product->p_color){
          case 'Grey':
          case 'Ruthenium':
          case 'Rodium':
          case 'Meteorite':
              $faceColor = 'Gray';
              break;
          case 'Two Tone':
              $faceColor = 'Multi-color';
              break;
          case 'Ivory':
          case 'Mother of Pearl':
              $faceColor = 'Beige';
              break;
          case 'Diamond-Paved':
              $faceColor = 'White';
              break;
          case 'Sundust':
              $faceColor = 'Off-White';
              break;
          case 'Champagne':
          case 'Cream':
          case 'Salmon':
              $faceColor = 'Yellow';
              break;
          case 'Transparent':
              $faceColor = 'Clear';
          case 'Rose':
          case 'Pink Mother of Pearl':
              $faceColor = 'Pink';
              break;
          case 'Olive Green':
              $faceColor = 'Green';
              break;
          case 'Ice Blue':
          case 'Navy Blue':
              $faceColor = 'Blue';
              break;
          case 'Brown Mother of Pear':
          case 'Chocolate':
              $faceColor = 'Brown';
              break;
        }
        
        $condition = Conditions()->get($product->p_condition);
        if (in_array($condition,['New','Unworn'])) 
          $warranty = 'three years';
        else $warranty = 'one year';
      
        $warranty = "Swiss Made Corp. privides $warranty warranty on all mechanical issues for this watch.";
        $features='';
        
        $category = $product->categories->category_name;
        $firstImage = 'https://swissmadecorp.com/images/' . $product->images->first()->location;
        
        $secondImage='';$imageUrl='';
        if (count($product->images)>1){
          $imageUrl = "<productSecondaryImageURL>";
          
          foreach ($product->images as $image) {
            $l_secondImage = $image->location;
            $secondImage .= "
            <productSecondaryImageURLValue>https://swissmadecorp.com/images/$l_secondImage</productSecondaryImageURLValue>
            ";
          }

          $imageUrl .= $secondImage."
          </productSecondaryImageURL>";
          }

      $retail='';
      if ($product->p_retail) {
          $retail = "<msrp>$product->p_retail</msrp>";
      }

      $title = $product->title . '<br><br>' . $description;
      $weight = $product->p_box == 0 ? '1.00' : '3.00';

      $price = $product->p_newprice;

      if ($price > 1500) {
        $margin = ceil(($price-1500) * 0.03);
        $price = $price + $margin + 225;
      } else 
        $price = ceil($price+($price *.15));
        
      $bandMaterial = Strap()->get($product->p_strap);
      if ($bandMaterial == 'Rubber') {
        $bandMaterial = 'Other';
      } elseif (strpos($bandMaterial,'Gold')>0) {
        $bandMaterial = 'Gold';
      }

      $item.="<MPItem>
        <processMode>CREATE</processMode>
        <sku>$product->id</sku>
        <productIdentifiers>
          <productIdentifier>
            <productIdType>UPC</productIdType>
              <productId>$upc</productId>
            </productIdentifier>
          </productIdentifiers>
        <MPProduct>
          <SkuUpdate>No</SkuUpdate>
            $retail
            <productName>$condition ".$product->title."</productName>
          <ProductIdUpdate>Yes</ProductIdUpdate>
          <category>
          <WatchesCategory>
            <Watches>
            <shortDescription><![CDATA[$title]]></shortDescription>
            ";
                    if ($product->water_resistance) {
                    // $item.=
                    // "<keyFeatures>
                    //   <keyFeaturesValue>$product->water_resistance</keyFeaturesValue>
                    // </keyFeatures>";
                    }
                    
                    $item.="<brand>$category</brand>
                    <manufacturer>$category</manufacturer>
                    <modelNumber>$product->p_reference</modelNumber>
                    <manufacturerPartNumber>$product->p_reference</manufacturerPartNumber>
                    <mainImageUrl>$firstImage</mainImageUrl>";
                    $item.=$imageUrl;
                    if ($product->p_gender== "Men's")
                        $gender = "Male";
                    else $gender = "Female";
                    $item.="<gender>$gender</gender>
                    <ageGroup>
                      <ageGroupValue>Adult</ageGroupValue>
                    </ageGroup>
                    <material>".Materials()->get($product->p_material)."</material>
                    <color>$faceColor</color>
                    <colorCategory>
                      <colorCategoryValue>$faceColor</colorCategoryValue>
                    </colorCategory>
                    <metal>".BezelMaterials()->get($product->p_bezelmaterial)."</metal>
                    <watchStyle>Luxury</watchStyle>
                    <isWaterproof>Yes</isWaterproof>
                    <displayTechnology>Analog</displayTechnology>
                    <hasWarranty>Yes</hasWarranty>
                    <watchBandMaterial>".$bandMaterial."</watchBandMaterial>
                    <warrantyText>$warranty</warrantyText>
                    ";
                    if ($features) {
                        $item.='<features>
                        <feature>Chronograph</feature>
                        </features>';
                    }
                    $item.="<keywords>".$product->keyword_build."</keywords>
                  </Watches>
                </WatchesCategory>
              </category>
            </MPProduct>
            <MPOffer>
              <price>$price</price>
              <MustShipAlone>Yes</MustShipAlone>
              <ShippingWeight>
                <measure>$weight</measure>
                <unit>lb</unit>
              </ShippingWeight>
              <ProductTaxCode>2043986</ProductTaxCode>
            </MPOffer>
          </MPItem>
          ";
        }
        
        $headers = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <MPItemFeed xmlns=\"http://walmart.com/\">
          <MPItemFeedHeader>
            <version>3.2</version>
            <requestId>HP_REQUEST</requestId>
            <requestBatchId>HP_REQUEST_BATCH</requestBatchId>
            <feedDate>$tUtc</feedDate>
            <mart>WALMART_US</mart>
          </MPItemFeedHeader>
          ";

        $items = $headers.$item."</MPItemFeed>";

        //return($items);
        
        $requestUrl = 'https://marketplace.walmartapis.com/v3/feeds?feedType=item';

        $walmart = new WalmartClass();
        $walmart->submitToWalmart($requestUrl,$items);
        
        ProcessWalmartInventory::dispatch($skus); //->onConnection('sqs');
        
        return "Item(s) submitted successfully.";

    }
}

    