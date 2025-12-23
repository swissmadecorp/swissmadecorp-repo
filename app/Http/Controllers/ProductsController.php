<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Http\Requests\ProductRequest;
use App\Models\RepairProduct;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Validation\Rule;
use App\Jobs\AutomateEbayPost;
use App\Models\EbayListing;
use App\Jobs\AIProductDescription;
use Illuminate\Http\Request;
use App\Jobs\eBayEndItem;
use Illuminate\Support\Str;
use App\Models\Reminders;
use App\Models\Repair;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Category;
use App\Models\Image;
use Carbon\Carbon;
use App\Models\GlobalPrices;

class ProductsController extends Controller
{
    public function __construct() {
        $this->middleware('role:superadmin|administrator', ['only' => ['create', 'duplicate', 'store', 'edit', 'delete']]);

    }

    public function printlabel(Request $request) {
        $printlabel = new \App\Libs\PrintOrder(); // Create Print Object

        $input = trim($request->input('tracking_number'));

        $printlabel->printLabel($input); // Print newly create proforma.

    }

    public function facebookToken(Request $request) {
        $token = config('chatgpt.FACEBOOK_API');

        // Your App ID, App Secret, and short-lived token
        $app_id = '1279877275969102';
        $app_secret = 'd77efcc02bf9489b6c1d2747b799453a';
        $short_lived_token = $token;  // The token you got initially

        // Construct the API URL
        $url = 'https://graph.facebook.com/v21.0/oauth/access_token?' . http_build_query([
            'grant_type' => 'fb_exchange_token',
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'fb_exchange_token' => $short_lived_token
        ]);

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request
        $response = curl_exec($ch);
// return $response;
        // Check for errors
        if ($response === false) {
            echo 'cURL Error: ' . curl_error($ch);
            exit;
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if the long-lived token is in the response
        if (isset($data['access_token'])) {
            echo 'Long-Lived Access Token: ' . $data['access_token'] . "\n";
            echo 'Expires in: ' . $data['expires_in'] . ' seconds' . "\n";
        } else {
            echo 'Error: ' . $data['error']['message'] . "\n";
        }

    }

    public function whatsappVerify(Request $request) {
        \Log::debug($request);
        return $request['hub_challenge'];
    }

    public function scraper1(Request $request) {
        $token = config('chatgpt.FACEBOOK_API');
        $phone_number_id = '580826665103968';
        $phoneTo = '+17186569494';

        if ($request['handshake']==0) {

            $headers = [
                'Authorization: Bearer ' . $token,
            ];

            $filePath = public_path().'/uploads/BayHill--Invoice-9965.pdf'; // Path to your local file

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/media");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $data = [
                'messaging_product' => 'whatsapp', // Include the messaging_product parameter
                'file' => new \CURLFile($filePath,'application/pdf','BayHill--Invoice-9965.pdf'),
                'type' => 'application/pdf', // MIME type of the file
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                $mediaId = $responseData['id']; // Retrieve the media_id
                echo "Media uploaded successfully. Media ID: " . $mediaId . "\n";
            } else {
                echo "Failed to upload media. Response: " . $response . "\n";
            }

            $headers = [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];

            $post = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $phoneTo,
                "type" => "document",
                "document" => [
                "id" => $mediaId, /* Only if using uploaded media */
                "caption" => "BayHill--Invoice-9965.pdf",
                ]
            ];

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Send JSON data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                // Handle CURL error
                echo 'Error: ' . curl_error($ch);
            } else {
                echo 'Successful response: ' . $response;
            }
            curl_close($ch);

            // return $response;
            //$this->askToChatGPT("Hello");
            // $process = new Process(['python', base_path(). '/public/python/scraper.py']);
            // $process->run();

            // executes after the command finishes
            // if (!$process->isSuccessful()) {
            //     throw new ProcessFailedException($process);
            // }
            //dd($process->getOutput());
            // $products = explode("/n",$process->getOutput());
            // foreach ($products as $product) {
            //     $item = explode("\n",str_replace("/n","",$product));
            //     $items[] = $item;
            // }

            // return view('test2',['products' => $items]);
        } else {
            $headers = [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];

            $post = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $phoneTo,
                "type" => "template",
                "template" => [
                    "name" => 'new_connection', /* Only if using uploaded media */
                    "language" => ["code" => 'en'],
                ]
            ];

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Send JSON data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                // Handle CURL error
                echo 'Error: ' . curl_error($ch);
            } else {
                echo 'Successful response: ' . $response;
            }
            curl_close($ch);

        }

    }

    private function askToChatGPT($prompt)
    {
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('chatgpt.FACEBOOK_API'),
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v19.0/17186147678/messages", [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "type" => "text",
                "to" => "+19174359735",
                'text' => [
                    "preview_url" => "false",
                    "body" => "Thanks for your order! Tell us what address you’d like this order delivered to."
                ]
            ]);

        return $response->throw()->json();;
    }

    public function WireDiscount(Request $request) {

        Product::find($request['product_id'])->update([
            'wire_discount' => $request['enablediscount']
        ]);
    }

    public function addImageById(Request $request) {
        $id = $request['id'];

        if ($id && $request->ajax()) {
            $productId=$request['productId'];
            $image = Image::find($id);

            $imageDB = \App\Models\Product::find($productId);
            $imageDB->images()->attach($id);

            ob_start();
            ?>
                <div class="image">
                    <div class="image-title"><?= $id ?></div>
                    <div class="delete-image">X</div>
                    <img alt="<?= $image->title ?>" src="<?= '/images/thumbs/' . $image->location  ?>" title="<?=  $image->title  ?>" >
                    <div class="position"><input type="text" value="0" placeholder="image position" class="position-input" /></div>
                    <input type="hidden" name="filename[]" value="<?= $image->location ?>" />
                </div>
            <?php

            return array('message'=>'success','content'=>ob_get_clean());
        }

    }

    public function ajaxOnHold(Request $request) {
        parse_str($request['form'],$output);

        if ($request->ajax()) {

            $status = $request['status'];
            $product = Product::find($request['_id']);
            if ($product) {
                if ($status == 2) {
                    $product->update([
                        'reserve_amount' => $output['p_amount'],
                        "reserve_for" => $output['p_company'],
                        "reserve_date" => Carbon::now(),
                        "p_status" => $status
                    ]);
                    return response()->json("<div><p style='padding: 30px 20px;width: 90%'>This product has been reserved and will be held for 72 hours.</p></div>");
                } elseif ($status == 9) {
                    $repairArray = array(
                        'assigned_to' => $output['watchmaker'],
                        'comments' => $output['repair_notes'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    );

                    $repair = Repair::create($repairArray);

                    $repairProduct=array(
                        'product_id' => $product->id,
                        'job' => serialize($output['repair_reason'])
                    );
                    $product->repair()->attach($repair->id, $repairProduct);

                    $product->update(["p_status" => $status]);

                    return response()->json("<div><p style='padding: 30px 20px;width: 90%'>Repair created successfully.</p></div>");
                }

            }

            return response()->json('There was an error');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $categories = Category::select("id", "category_name")
        //         ->withSum('products', function($query) {
        //             $query->where('p_qty','>',0);
        //         }, "p_price")
        //         ->get()
        //         ->toArray();

        // $categories = Category::select('id','category_name')->withCount([
        //     'products' => fn($query) => $query->where('p_status','=',8)->orderBy("p_price")
        // ],'p_price')->get()->toArray();

        //dd($categories);
        //$user = Order::find(5950);
        return view('admin.products',['pagename' => 'Product Page','includeDataTableCss'=>'1','includeDataTableJs'=>'1']); //,'note'=>$user]);

    }

    public function checkRepairStatus(Request $request) {
        $id = $request['id'];
        $product = Product::find($id);

        if (count($product->repair)) {
            if ($product->repair[0]->status == 0) {
                $repair = $product->repair->first();
                return [$repair->assigned_to, unserialize($repair->pivot->job),$repair->comments];
            }
        }
    }

    public function UpdateRepair(Request $request) {
        $id = $request['product_id'];
        $product = Product::find($id);

        if (count($product->repair)) {
            if ($product->repair[0]->status == 0) {
                $repair = $product->repair->first();
                $repair->update([
                    'assigned_to' => $request['watchmaker'],
                    'comments' => $request['repair_notes'],
                    'status' => $request['completed'] ? 1 : 0
                ]);
                $repair->pivot->job = serialize($request['repair_reason']);
                $repair->pivot->update();

                if ($request['completed'])
                    $product->p_status = 0;
                else
                    $product->p_status = 9;

                $product->update();
                return "Product was assined to: ".$request['watchmaker'];
            }
        } else {
            $repairArray = array(
                'assigned_to' => $request['watchmaker'],
                'comments' => $request['repair_notes'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            );

            $repair = Repair::create($repairArray);

            $repairProduct=array(
                'product_id' => $id,
                'job' => serialize($request['repair_reason'])
            );
            $product->repair()->attach($repair->id, $repairProduct);
            $product->p_status = 9;
            $product->update();

            return "Product was assined to: ".$request['watchmaker'];
        }


    }

    public function getAll(Request $request) {
        if ($request->ajax()) {
            $total=0;$qty=0;

            if ($request['display']=='Display not on-hand')
                $display=0;
            else $display=1;

            if ($request['action']=='active') {
                $start_time= microtime(true); // Top of page
                if ($display==1) {
                    $products = Product::with('images','listings')->where('p_qty','>','0')->orderBy('updated_at','desc')->get();

                } else {
                    // ini_set('memory_limit', '-1');
                    // $minutes = Carbon::now()->addMinutes(10);
                    // $products = Cache::remember('products', $minutes, function() {
                    //     return Product::with('images')->where('p_qty','<=','0')->orderBy('updated_at','desc')->get();
                    // });
                    $products=Product::with('images')->where('p_qty','<=','0')->orderBy('updated_at','desc')->get();
                }

               // \Log::Debug(number_format(microtime(true) - $start_time, 2));
            } else {
                $products = Product::onlyTrashed()->get();
            }

            $data=array();$path='';

            foreach ($products as $product) {
               // dd($product->images[0]);

               //\Log::debug($product->id);
                if (isset($product->images[0])) {
                    $image = $product->images[0];
                    $path = '/images/thumbs/'.$product->images[0]->location;
                    $path = '<a href="/'.$product->slug.'" target="_blank"><img style="width: 80px" class="lazy" title="'.$product->images[0]['title'].'" alt="'.$product->images[0]['title'].'" data-src="'.$path.'"></a>';
                } else {
                    $image="/images/no-image.jpg";
                    $path = "<a href='/$product->slug' target='_blank'>";
                }

                if ($product->listings) {
                    $listingNumber = $product->listings->orderBy('created_at','desc')->first()->listitem;
                    $ebay = "<div style='position: relative;'><a target='_blank' href='https://www.ebay.com/itm/".$listingNumber."'><img src='/assets/ebay-logo.png' style='position: absolute;top: -87px;left: -7px;height: 28px;'></a></div>";
                    $path .= $ebay;
                }

                $status = $product['p_status'] != 0 && $product['p_status'] <> 9 ? "(<span style='color:red'>".Status()->get($product['p_status'])."</span>)" : '';
                if (count($product->repair)) {
                    if ($product->repair[0]->status == 0) {
                        $repair = $product->repair->first();
                        $status = "<a href='#' class='repair_link' data-assigned='".$repair->assigned_to."' data-jobs='".unserialize($repair->pivot->job)."' style='color:red'>(Repair)</a>";
                    }
                }

                $condition = "<span class='condition'>".Conditions()->get($product['p_condition']) . "</span>";
                $title = "<span>".$product['title']."</span>";
                if ($product['group_id']==0)
                    $group = $title  . $condition .$status;
                elseif ($product['group_id']==2)
                    $group = $title . ' ' .$product['p_color']. ' Bezel';
                else $group = $title . ' ' . MetalMaterial()->get($product['p_material']). ' '. $product['jewelry_type'];

                $group_id = $product['group_id'];
                $groupname='';

                if ($group_id>0)
                    $groupname = $group_id==1 ? 'jewelry' : 'bezel';

                $editPath = "<a href='/admin/products/".$product['id']."/{$groupname}edit'>".$product['id'].'</a>';
                $details = "<span class='block'>".($product['p_box']==1 ? '<i class="fa fa-box"></i>' :'') . ' ' . ($product['p_papers']==1 ? '<i class="fas fa-pager"></i>' :'').'</span>';
                $details .= ($product['p_strap']>0) ? Strap()->get($product['p_strap']) : '';
                $data[]=array(
                    "<span style='display:none'>$group_id</span>",
                    $path,
                    "<a href='/admin/products/".$product['id']."/{$groupname}edit'>".$product['id'].'</a>',
                    $group,
                    $product['p_serial'],
                    '<span class="hide">$'.number_format($product['p_price'],0).'</span>',
                    '$'.number_format($product['p_newprice'],0).'&nbsp;('.number_format($product['discount_amount'],0).'%)',
                    '$'.number_format($product['p_retail'],0),$product['p_qty'],$details
                );

                if ($product['p_qty']>0) {
                    $total +=$product['p_price']*$product['p_qty'];
                    $qty += $product['p_qty'];
                }
            }
            //\Log::Debug(number_format(microtime(true) - $start_time, 2)); // Bottom of page
            //die;

            return response()->json(array('data'=>$data,'total'=>'$'.number_format($total,2),'qty'=>$qty));
        }
    }

    public function ajaxGetProductsForOrder(Request $request) {
        if ($request->ajax()) {
            $key = str_replace(["'","&"],'',$request['query']);
            $addParam = '';

            $products = Product::select('title','id')
                ->where("title","LIKE", "%$key%")
                ->where("p_qty",'>',0)
                ->where('p_status', 0)
                ->get();
            $data = array();
            $data['query'] = $key;
            $data['suggestions'] = array();

            foreach ($products as $product) {
                $data['suggestions'][] = array('value'=>$product->id.'-'.$product->title,'data' => $product->id);
            }

            return response()->json($data);
        }
    }

    public function getRelatedProducts(Request $request) {
        if ($request->ajax()) {
            $products = Product::whereHas('categories', function($query) use ($request) {
                $query->where('id',$request['catId']);
            })
                ->where('p_qty','>',0)
                ->where('p_model','LIKE','%'.$request['p_model'].'%')
                ->where('id','<>',$request['product_id'])
                ->get();

            $related = \App\Models\RelatedProducts::select('product_id')->where('parent_id',$request['product_id'])->get()->pluck('product_id')->toArray();

            ob_start();
            foreach ($products as $product) {
                ?>
                <option <?= in_array($product->id, $related) ? 'selected' : '' ?> value="<?= $product->id?>"><?= $product->id . '-' . $product->title?></option>
                <?php
            }

            return response()->json(ob_get_clean());
        }
    }

    public function createNewColumn(Request $request) {
        //return response()->json('A newly column was just created.');
        if ($request->ajax()) {
            parse_str($request['form'],$output);

            \Schema::table('products', function($table) use($output) {
                $column_name = 'c_' . Str::slug($output['column_name']);

                if ($output['column_type'] == 'String')
                    $table->string($column_name,100)->nullable();
                elseif ($output['column_type'] == 'Money')
                    $table->string($column_name,100)->nullable();
                else $table->integer($column_name)->nullable();
            });

            return response()->json('A newly column was just created.');
        }
    }

    public function Excel(Request $request) {

        $spreadsheet = new Spreadsheet();
        //add some data in excel cells
        $spreadsheet
            ->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Image')
            ->setCellValue('B2', 'Description')
            ->setCellValue('C2', 'Price')
            ->setCellValue('D2', 'Qty')
            ->setCellValue('E2', 'Discount');

        //set style for A1,B1,C1 cells
        $cell_st =[
            'font' =>['bold' => true],
            'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'd9d9d9'],
                ],
            ],
        ];

        $activeSheet=$spreadsheet->getActiveSheet();
        $activeSheet->getStyle('A2:E2')->applyFromArray($cell_st);
        $activeSheet->getStyle("A2:E2")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('d9d9d9');

        $ids = $request['ids'];
        if (!$ids)
            return response()->json(array('error'=>'1','message'=>'No product(s) has been selected'));

        $products=Product::findMany($ids)
            ->orderBy('id','desc');

        $row_num=2;$qty=0;

        foreach ($products as $product) {
            $row_num++;
            $qty+=$product->p_qty;

            $spreadsheet
                ->setActiveSheetIndex(0)
                ->setCellValue("B$row_num", $product->title)
                ->setCellValue("C$row_num", $product->p_retail)
                ->setCellValue("D$row_num", $product->p_qty);

            //$spreadsheet->getActiveSheet()->getPageMargins()->setRight(0.75);
            $image=$product->images->first();
            $noImage=false;$path='';

            if ($image)
                $path = base_path().'/images/thumbs/'.$image->location;

            if (!file_exists($path)) {
                $noImage=true;
                $path = base_path().'/images/no-image.jpg';
            }

            // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($path);
            $drawing->setCoordinates("A$row_num");

            if ($noImage) {
                $drawing->setResizeProportional(false);
                $drawing->setWidth(90);
                $drawing->setHeight(97);
                $drawing->setOffsetX((112-90)/2); // 112 is the cell width
                $drawing->setOffsetY((108-97)/2);
            } else {
                $drawing->setWidth(90);
                $drawing->setHeight(97);
                $drawing->setOffsetX((112-$drawing->getWidth())/2); // 112 is the cell width
                $drawing->setOffsetY((108-$drawing->getHeight())/2);
            }

            $drawing->setWorksheet($activeSheet);

            $cell_st1 =[
            'alignment' =>[
                'vertical'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'd9d9d9'],
                    ],
                ],
            ];

            // First Header Row Begin
            $activeSheet->mergeCells('A1:E1');
            $cell_st =[
                'font' =>['bold' => true],
                'font' => [
                    'size' => 14
                ],
                'alignment' =>[
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ];
            $activeSheet->getStyle("A1:E1:")->applyFromArray($cell_st);
            $activeSheet->setCellValue("A1","Swissmade Corp. Proposal");
            // First Header Row End

            $activeSheet->getStyle("B$row_num")->getAlignment()->setWrapText(true);
            $activeSheet->getStyle("C$row_num")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

            $activeSheet->getStyle("E$row_num")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

            $activeSheet->getStyle("A$row_num:E$row_num")->applyFromArray($cell_st1);

            //set columns width
            $activeSheet->getColumnDimension('A')->setWidth(16);
            $activeSheet->getDefaultRowDimension()->setRowHeight(81);
            $activeSheet->getColumnDimension('B')->setWidth(35);
            $activeSheet->getColumnDimension('C')->setWidth(15);
            $activeSheet->getColumnDimension('E')->setWidth(15);
            $activeSheet->getRowDimension($row_num)->setRowHeight(81);
        }
        $total_row = $row_num+1;

        $activeSheet->insertNewRowBefore($total_row, 2);
        $activeSheet->getStyle("C$total_row")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

        $activeSheet->getStyle("E$total_row")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

        $cell_st1 =[
            'font' =>['bold' => true],
            'alignment' =>[
                'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                ],
            ];

        $total_row++;
        $activeSheet->getStyle("B$total_row:E$total_row:")->applyFromArray($cell_st1);
        $activeSheet->setCellValue("B$total_row","Total:");
        $activeSheet->setCellValue("C$total_row","=SUM(C2:C$row_num)");
        $activeSheet->setCellValue("D$total_row",$qty);
        $activeSheet->setCellValue("E$total_row","=SUM(E2:E$row_num)");
        $activeSheet->getStyle("A$total_row:E$total_row")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('d9d9d9');

        $cell_st1 =[
            'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                    ],
                ],
            ];

        $total_row--;
        $activeSheet->getStyle("A$total_row:E$total_row:")->applyFromArray($cell_st1);
        $activeSheet->getRowDimension($total_row)->setRowHeight(20);
        $activeSheet->getRowDimension($total_row+1)->setRowHeight(20);

        $activeSheet->getPageMargins()->setTop(.25);

        // $activeSheet->getHeaderFooter()
        //         ->setOddHeader('&C&H&14 Swissmade Corp. Proposal&R'.date('F j, Y',time()));
        $activeSheet->getHeaderFooter()
                ->setOddFooter('&L&BInventory&RPage &P of &N');

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setPath(base_path().'/public/images/logo.png');
        $drawing->setCoordinates("A1");

        $drawing->setHeight(60);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $activeSheet->getRowDimension(1)->setRowHeight(50);
        $activeSheet->setTitle('Inventory'); //set a title for Worksheet

        //make object of the Xlsx class to save the excel file
        $writer = new Xlsx($spreadsheet);
        $filename='/uploads/swissmadecorp-'.date('m-d-Y',time()).'.xlsx';

        $fxls =base_path().$filename;
        $writer->save($fxls);
        return response()->json(array('error'=>0,'filename'=>$filename));
    }

    public function ajaxEstimatedProducts(Request $request) {
        if ($request->ajax()) {
            $blade = $request['_blade'];

            if ($blade!='create-order-estimator') {
                $products = Product::with('categories')->latest()->get();
                $content=view('admin.estimated-products-ajax',['products' => $products,'selection'=>'partial']);
                return response()->json(['content'=>$content->renderSections(),'selection'=>'partial']);
            } else {
                $products = Product::latest()->get();
                $content=view('admin.estimated-products-ajax',['products' => $products,'selection'=>'all']);
                return response()->json(['content'=>$content->renderSections(),'selection'=>'all']);
            }

        }
    }

    public function updateQty(Request $request) {
        if ($request->ajax()) {
            $id=$request['id'];
            $qty=$request['qty'];

            $product = Product::find($id);
            $product->update(['p_qty' => $qty]);

            return response()->json($qty);
        }
    }

    public function EbayResubmit(Request $request) {

        $id = $request['product_id'];
        $listing = EbayListing::where('product_id',$id)->first();
        if ($listing) {
            if (!$listing->listitem) {
                $listing->delete();
                AutomateEbayPost::dispatch(["ids"=>[$id]]);
            }
        } else {
            AutomateEbayPost::dispatch(["ids"=>[$id]]);
            return "Submitted";
        }
    }

    private function Platforms() {
        $globalPricers = GlobalPrices::all();
        foreach ($globalPricers as $platform) {
            $platforms[] = ['platform'=>$platform->platform, 'margin'=>$platform->margin];
        }

        return $platforms;
    }

    public function globalPriceChange(Request $request) {
        if ($request->ajax()) {
            //$percent = $request['percent'] / 100;

            $products = Product::where('p_qty','>',0)
                ->where('p_newprice','>','0')
                ->get();

            foreach ($products as $product) {
                $amount = $product->p_newprice;
                $rolexBoxMargin = 0;

                //$percent = Chrono24Magin($amount);
                $percent = GlobalPrices::where('platform','Chrono24')->first()->margin;

                if ($product->category_id==1 && $product->p_condition==2) $rolexBoxMargin=100;

                $price3p = $amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * $percent);

                $price3p = number_format($price3p,0,'','');

                //return response()->json($product->id . ' '.$amount.' '.$price3p);

                $product->update([
                    'p_price3P' => $price3p
                ]);
            }

            return response()->json('Price change complete.');
        }
    }

    public function invoicepayments() {
        return view('admin.lvinvoicepayments'); //->compact('users');
    }

    public function lvopenai() {
        return view('admin.lvopenai'); //->compact('users');
    }

    public function lvreports() {
        return view('admin.lvreports'); //->compact('users');
    }

    public function lvproducts() {
        return view('admin.lvproducts'); //->compact('users');
    }

    public function lvreminders() {
        return view('admin.lvreminders'); //->compact('users');
    }

    public function inventory() {
        return view('admin.lvinventory'); //->compact('users');
    }

    public function exportToExcel() {
        return view('admin.lvexport'); //->compact('users');
    }

    public function theshow() {
        return view('admin.lvtheshow'); //->compact('users');
    }

    public function globalProperties(Request $request) {
        if ($request->ajax()) {
            $ids = $request['ids'];
            $status = $request['status'];
            $newprice = $request['newprice'];

            if ($status != 'none') {
                // $products = Product::whereIn('id',$ids)->update([
                //     'p_status' => $status
                // ]);

                $products = Product::whereIn('id',$ids)->update([
                    'p_status' => $status
                ]);
            }

            if ($newprice>0) {
                $amount = $newprice;

                //$products=Product::whereIn('id',$ids)->get();
                $products=Product::findMany($ids);

                if (strpos($newprice,'%')>0) {
                    $amount=str_replace('%','',$newprice);
                    $sign='Percent';
                } else $sign='Amount';

                $newprice = $amount;
                $m = '';
                foreach ($products as $product) {
                    $discount = 0;$rolexBoxMargin=0;
                    if ($sign=='Percent' && $product->p_retail>0) {
                        $discount = $newprice;
                        $amount=number_format($product->p_retail-($product->p_retail*($newprice/100)),0,'','');
                    } elseif ($product->p_retail>0)
                        $discount = number_format(abs(1 - ($newprice / $product->p_retail))*100,0) ;

                    //$percent = Chrono24Magin($amount);
                    $percent = GlobalPrices::find('Chrono24')->margin;
                    if ($product->category_id==1 && $product->p_condition==2) $rolexBoxMargin=100;

                    $price3p = $amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * $percent);
                    $price3p = number_format($price3p,0,'','');

                    //$webprice = $amount+($amount *CCMargin());

                    //return ceil($webprice);
                    $m &= $price3p;
                    $product->update([
                        'p_newprice' => $amount,
                        'discount' => $discount,
                        'web_price' => ceil($amount),
                        'p_price3P' => ceil($price3p)
                    ]);

                }

                return response()->json('All property values update complete. You might not see the changes until you refresh the page.');
            }
        }
    }

    public function updatePrice(Request $request) {
        if ($request->ajax()) {
            $id=$request['id'];
            $sign = '';

            $product = Product::find($id);

            $amount=$request['amount'];
            if ($amount==0) {
                $product->update(['p_newprice' => 0]);
                //Margin::where('product_id','=',$id)->delete();
                return response()->json(array('error'=>'success','amount'=>$amount));
            }

            if (strpos($amount,'%')>0) {
                if ($product->p_retail==0) {
                    return response()->json(array('error'=>'No Retail Price specified!'));
                }

                $amount=str_replace('%','',$amount);
                $sign='Percent';
            } else $sign='Amount';

            $discount = 0;$rolexBoxMargin=0;
            if ($sign=='Percent') {
                $discount = $amount;
                $amount=number_format($product->p_retail-($product->p_retail*($amount/100)),0,'','');
            } elseif ($product->p_retail>0) {
                if ($amount < $product->p_retail)
                    $discount = number_format(abs(1 - ($amount / $product->p_retail))*100,0) ;
            }

            //$percent = Chrono24Magin($amount);
            $percent = GlobalPrices::find('Chrono24')->margin;
            if ($product->category_id==1 && $product->p_condition==2) $rolexBoxMargin=100;

            $price3p = $amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * $percent);

            $web_price = $amount;

            $price3p = number_format(ceil($price3p),0,'','');

            $product->update([
                'p_newprice' => $amount,
                'discount_amount' => $discount,
                'web_price' => $web_price,
                'p_price3P' => $price3p
            ]);

            $this->postToEbay($product);
            return response()->json(array('error'=>'success','amount'=>$amount,'discount' => $discount));
        }
    }

    public function ajaxProducts(Request $request) {
        if ($request->ajax()) {
            $products = Product::with('categories')->latest()->get();

            $content=view('admin.products-ajax',['products' => $products]);

            return response()->json($content->renderSections());
        }
    }

    public function printTag($ids) {

        $printTag = new \App\Libs\PrintOrder(); // Create Print Object
        $printTag->printProductTag($ids); // Print newly create proforma.

    }

    public function print($id) {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $product = Product::find($id);
        // set auto page breaks
        //$pdf::SetAutoPageBreak(TRUE, 30);

        // set image scale factor
        //$pdf::setImageScale(1);


        // define barcode style
        $style = array(
            'position' => '',
            'align' => '',
            //'stretch' => true,
            //'fitwidth' => false,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            //'fgcolor' => array(0,0,128),
            //'bgcolor' => array(255,255,128),
            //'text' => true,
            //'label' => 'CUSTOM LABEL',
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 0
        );

        // set font
        //$page_format = array();//'Rotate' => 270);
        // add a page
        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 7);

        $user_agent = getenv("HTTP_USER_AGENT");


            $os = "Mac";


        if ($product->p_comments!='Temp') {
                $pdf::write2DBarcode("$product->id", 'QRCODE,L', 5.8, 2, 12, 12, $style, 'N');
                $pdf::setXY(16.5,3);

            $id = $product->id;
        } else {
            $pdf::setXY(0,11);
            $id = $product->p_reference;
        }

        ob_start();
        ?>
        <table>
            <tr>
                <td><div><?= $id ?></div></td>
            </tr>
            <tr>
                <td><?= substr($product->p_serial,0,7) ?></td>
            </tr>
            <tr>
                <td><?= 'B'.($product->p_box==1 ? 'Y' :'N') . 'P' . ($product->p_papers==1 ? 'Y' :'N') ?></td>
            </tr>
        </table>

        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        ob_start();

            $pdf::setXY(31,3);

        ?>
        <table>
            <tr>
                <td><div><?= priceToLetters($product->p_price); ?></div></td>
            </tr>
            <tr>
                <td>$<?= number_format($product->p_retail,2) ?></td>
            </tr>
            <tr>
                <td><?= $product->p_reference ?></td>
            </tr>
        </table>

        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        $pdf::StopTransform();
        \PDF::Output('example_048.pdf', 'I');

    }

    public function PrintReturn($id) {
        $product=Product::find($id);
        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        \PDF::setHeaderCallback(function($pdf) {
            // Logo
            $image_file = '/images/logo.jpg';
            $pdf->Image($image_file, 14, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        });

        \PDF::setFooterCallback(function($pdf) {
            // Position at 15 mm from bottom
            //$pdf->SetY(-15);
            // Set font
            $pdf->SetFont('helvetica', 'I', 8);

            // $pdf->Write(0, "If you have any questions regarding this ". $orderStatus . ", please contact us.", '', 0, 'C', true, 0, false, false, 0);
            // $pdf->WriteHTML("<b><i>Thank You For Your Business!</i></b>", true, false, false, false, 'C');

                // Page number
            $pdf->Cell(0, 10, 'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        });

        // set header and footer fonts
        $pdf::setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf::setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf::SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        // ---------------------------------------------------------
        // add a page
        $pdf::AddPage();
        $orderStatus = '';

        $orderStatus = "Return";

        $pdf::setXY($pdf::getPageWidth()-55,20);
        ob_start();
        ?>
        <table cellpadding="3">
            <tr>
                <td style="text-align:right"><div style="font-size:25px;color:#6b8dcb;font-weight:bold"><?= $orderStatus?></div></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica"><?= date('F d, Y',time()) ?></td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align:right;font-family:helvetica">Product # <?= $product->id ?></td>
            </tr>
        </table>
        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');

        $pdf::SetFont('helvetica', '', 10);
        $pdf::setY(23);

        $pdf::WriteHTML("15 W 47th Street, Ste # 503<br>New York, NY 10036<br>United States<br>212-840-8463<br>info@swissmadecorp.com", true, false, false, false, '');
        // -----------------------------------------------------------------------------

        $pdf::Ln();
        $countries = new \App\Libs\Countries;
        $customer = Customer::where('company',$product->supplier)->first();

        $country = $countries->getCountry($customer->country);
        $state='';
        if ($customer->state)
            $state = $countries->getStateCodeFromCountry($customer->state);

        ob_start();
        ?>
            <table cellpadding="1">
            <tr>
                <td style="width: 43%;background-color:#111;color:#fff">
                    <b>From</b>:
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;background-color:#111;color:#fff">
                    <b>To</b>:
                </td>
            </tr>
            <tr>
            <td style="width: 43%;">
                    Swiss Made Inc.<br>
                    15 W 47th Street, Ste #503<br>
                    New York, NY 10036<br>
                    United States<br>
                    212-840-8463
                </td>
                <td style="width: 80px"></td>
                <td style="width: 43%;">
                    <?= $customer->firstname . ' ' . $customer->lastname ?><br>
                    <?= !empty($customer->company) ? $customer->company . '<br>' : '' ?>
                    <?= !empty($customer->address1) ? $customer->address1 .', ' : ''?>
                    <?= !empty($customer->address2) ? $customer->address2 .'<br>' : '' ?>
                    <?= !empty($customer->city) ? $customer->city .', '. $state . ' ' . $customer->zip.'<br>': '' ?>
                    <?= !empty($country) ? $country.'<br>' : '' ?>
                    <?= !empty($customer->phone) ? $customer->phone . '<br>' : '' ?>
                </td>
            </tr>
        </table>

            <table cellpadding="5">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th style="border: 1px solid #ddd;color:#fff">Customer Invoice #</th>
                        <th style="border: 1px solid #ddd;color:#fff">Received Date</th>
                        <th style="border: 1px solid #ddd;color:#fff"><?= $orderStatus ?> Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd"><?= $product->supplier_invoice ?></td>
                        <td style="border: 1px solid #ddd"><?= $product->created_at->format('m-d-Y') ?></td>
                        <td style="border: 1px solid #ddd"><?= $product->updated_at->format('m-d-Y') ?></td>
                    </tr>
                </tbody>
            </table>

            <?php
                $pdf::Ln();
                $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
                ob_start();
            ?>

            <table cellpadding="5" style="border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #111;color:#fff">
                        <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                        <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                        <th width="210" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                        <th width="75" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                        <th width="50" style="border: 1px solid #ddd;color:#fff">Qty</th>
                        <th width="81" style="border: 1px solid #ddd;color:#fff">Retail</th>
                        <th width="81" style="border: 1px solid #ddd;color:#fff">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $p_image = $product->images->toArray();
                        if (!empty($p_image)) {
                            $image=$p_image[0]['location'];
                        } else $image = 'no-image.jpg';?>
                    <tr>
                        <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff">
                            <?php if($product->p_status!=3) { ?>
                                <img style="width: 70px" src="<?= '/images/'.$image ?>" />
                            <?php } ?>
                        </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="50"><?= $product->id ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="210"><?= $product->title  ?> </td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="75"><?= $product->p_serial ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0" width="50"><?= $product->qty ?></td>
                        <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right" width="81" ><?= number_format($product->p_retail,2)?></td>
                        <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="81"><?= number_format($product->p_price,2)?></td>
                    </tr>

                </tbody>

            </table>

        <?php
        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        $pdf::Ln();
        //$pdf::Write(0, "Thank you for your purchase.", '', 0, 'L', true, 0, false, false, 0);
        $pdf::Write(0, "If you have any questions regarding this return, please contact us.", '', 0, 'C', true, 0, false, false, 0);

        //Close and output PDF document
        $filename = str_replace(' ','-',$product->company).'-'.$orderStatus.'-'.$product->id.'.pdf';
        \PDF::Output($filename.'.pdf', 'I');

    }

    public function ajaxCreateEmptyRowForInvoice(Request $request) {
        if ($request->ajax()) {
            ob_start();

        ?>
            <tr>
                <?php if ($request['isMobile']=='desktop' || $request['_blade'] == 'repair') {?>
                    <td><input style="width: 65px" class="form-control product_id" type="text" pattern="\d*" name="id[]"></td>
                    <td><?php if ($request['_blade']=='repair') {?>
                        <input type=button value="Activate Snapshot" class="activeSnapshot btn btn-primary btn-sm">
                        <input type=button value="Take Snapshot" class="takesnapshot btn btn-primary btn-sm" >
                        <div style="width: 278px" class="captureimage_<?= $request['num']?>"></div>
                        <input type="hidden" name="filename[]">
                    <?php } ?>
                    </td>
                    <td><input class="form-control producttypeahead" name="product_name[]" type="text">
                    <input type="hidden" class="p_retail">
                </td>
                    <?php if ($request['_blade']=='invoice' || $request['_blade'] == 'create-order-estimator') {?>
                    <td><input class="form-control qtycalc" name="qty[]" pattern="\d*" type="number"></td>
                    <td></td>
                    <?php } elseif ($request['_blade']=='invoice_edit') {?>
                        <td>
                            <input type="hidden" value="0" name="op_id[]" />
                            <input type="hidden" name="product_id[]" />
                            <input class="form-control qtycalc" name="qty[]" pattern="\d*" type="number">
                        </td>
                    <?php } else {?>
                        <td>
                            <div class="col-2 input-group">
                                <div class="input-group-addon">$</div>
                                <input style="width: 80px" class="form-control cost" name="cost[]" pattern="\d*" type="text" value="0"></td>
                            </div>
                    <?php } ?>
                    <td>
                        <div class="col-2 input-group">
                            <div class="input-group-addon">$</div>
                            <input style="width: 80px" class="form-control pricecalc" name="price[]" pattern="^\d*(\.\d{0,2})?$" type="text">
                        </div>
                    </td>
                    <?php if ($request['_blade']=='invoice' || $request['_blade']=='invoice_edit') {?>
                    <td><span style='display:none'></span></td>
                    <?php } ?>
                    <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
                    <?php if ($request['_blade']!='invoice' && $request['_blade']!='invoice_edit' && $request['_blade'] != 'create-order-estimator') {?>
                        <td>
                        <select data-placeholder="Choose a job ..." class="chosen-select" name="jobs[]" multiple>
                        <option>Overhaul/Clean</option>
                        <option>Staff</option>
                        <option>Stem &amp; Crown</option>
                        <option>Crystal</option>
                        <option>Hands</option>
                        <option>Rusty</option>
                        <option>Dial Refurbish</option>
                        <option>Battery</option>
                        <option>Gasket</option>
                        <option>Coil</option>
                        <option>Mainspring</option>
                        <option>Circuit</option>
                        <option>Factory</option>
                        <option>Estimate</option>
                        <option>Guarantee</option>
                        </select>
                    </td>
                    <td><input class="form-control" name="instructions[]"  type="text"></td>
                    <?php } ?>
                    <td style="text-align: center">
                        <?php if ($request['_blade']!='invoice' && $request['_blade']!='invoice_edit' && $request['_blade'] != 'create-order-estimator') {?>
                        <button type="button" style="text-align:center" class="btn btn-primary btn-sm newrow" aria-label="Left Align">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                        <?php } ?>
                        <button type="button" style="text-align:center" class="btn btn-danger deleteitem" aria-label="Left Align">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </button>
                    </td>
                <?php } else { ?>
                    <td>
                        <div class="mobilizer">
                            <div class="row">
                                <div class="col-3">
                                    <label for="id" >Product Id</label>
                                    <input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="^\d*(\.\d{0,2})?$" name="id[]">
                                </div>
                                <div class="col-3 img_containers" style="border: 1px solid #999;background:#ccc"><img src="" alt="" /></div>
                                <div class="col-6">
                                    <label>Cost:</label>
                                    <span class="form-control cost">0</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label for="product_name">Product Name</label>
                                    <input class="form-control product_name" name="product_name[]" type="text">
                                    <input type="hidden" class="p_retail">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <label for="pricecalc" >Price</label>
                                    <input class="form-control pricecalc" pattern="^\d*(\.\d{0,2})?$" name="price[]" type="number">
                                </div>
                                <div class="col-3">
                                    <label for="qty" >Qty</label>
                                    <input class="form-control qty" readonly pattern="\d*" name="qty[]" type="number">
                                </div>
                                <div class="col-5">
                                    <label for="serial">Serial</label>
                                    <input class="form-control serial" name="serial[]" type="text">
                                </div>
                            </div>
                        </div>
                    </td>
                <?php } ?>
            </tr>
        <?php

            $content = ob_get_clean();
            return response()->json($content);
        }
    }

    public function ajaxFindProduct(Request $request) {

        if ($request->ajax()) {
            $id = $request['id'];$is_memo=0;
            $product = Product::find($id);

            if (!$product)
                return response()->json(array('error'=>1));

            $imageFilename='';

            $casesize='';
            if ($product->p_casesize)
                $casesize=$product->p_casesize;

            if ($product) {
                // $memo = Product::where('id',$id)->with('orders', function($query) {
                //     $query->where('method','On Memo');
                // })->first();

                // if ($memo)
                //     $is_memo = 1;

                // if (isset($product->categories->category_name))
                //     $product_name= $product->categories->category_name . ' ' . $product->p_model . ' ' . $casesize . ' ' . $product->p_reference;
                // else
                $product_name= $product->title;

                if ($product->category_id == 74)
                    $product_name= '';

                $imageElem='';
                if ($product->images->first()) {
                    $image = $product->images->first();
                    $imageElem = '/images/thumbs/' . $image->location;
                    $imageFilename=$image->location;
                } else {
                    $imageElem = '/images/no-image.jpg';
                    $imageFilename='no-image.jpg';
                }


                $price = number_format($product->p_price, 0, '', '');
                $status=0;

                if ($product->p_status==1 || $product->p_status==2)
                    $status = $product->p_status;

                if (isset($product->theshow) && $product->p_qty == 0)
                    $qty = 1;
                else $qty = $product->p_qty;

                return response()->json(
                    array(
                        'image'=>"<img style='width: 80px' src='$imageElem' />",
                        'filename' => $imageFilename,
                        'product_name'=>$product_name,
                        'onmemo' => $is_memo,
                        'onhand'=>$qty,
                        'price'=>$price,
                        'retail' => $product->p_retail,
                        'serial'=>$product->p_status==4 ? '' : $product->p_serial,
                        'product_id'=>$product->id,
                        'status' => $status,
                        'reservedFor' =>$product->reserve_for,
                        'error'=>0)
                    );
            }

            return response()->json(array('error'=>1));
        }
    }


    public function ajaxgetProduct(Request $request) {
        if ($request->ajax()) {
            $ids = $request['_ids'];
            $blade = $request['_blade'];

            $products = Product::with('categories')
                ->findMany($ids);

            ob_start();
            foreach ($products as $product) {
                $image = $product->images->first();
                if ($blade == 'create') {
                    ?>
                        <tr>
                            <td><img style="width: 80px" src="<?=  '/images/' . $image->location  ?>"</td>
                            <td style="width: 30%">
                                <?= $product->title ?>
                                <!--<input type="hidden" name="id[<?= $product->id?>]" value="<?= $product->id ?>"></input>-->
                            </td>
                            <td><input style="width: 50px" type="text" name="qty[<?= $product->id?>]" value="1"></td>
                            <<td><?= $product->p_qty ?></td>
                            <td>$<input style="width: 100px" type="" oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Please enter a valid serial number')" name="price[<?= $product->id?>]" value="<?= $product->p_price ?>"></td>
                            <td><input type="hidden" name="serial[<?= $product->id?>]" value="<?= $product->p_serial ?>">
                                <?= $product->p_serial ?></td>
                            <<td>
                                <button type="button" style="padding: 3px 5px" class="btn btn-danger deleteitem" aria-label="Left Align">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    } elseif ($blade=='edit-estimator') {
                        ?>
                        <tr>
                            <td><img style="width: 80px" src="<?=  '/images/' . $image->location  ?>" /></td>
                            <td><?= $product->categories->category_name . ' ' . $product->p_reference . ' ' . $product->p_model ?> </td>
                            <td><input style="width: 50px" type="text" name="qty[<?= $product->id?>]" value="1"></td>
                            <td style="text-align: right"><?= number_format($product->p_retail,2)  ?></td>
                            <td style="text-align: right">$<input style="width: 100px" type="text" name="price[<?= $product->id?>]" value="<?= $product->p_price ?>"></td>
                            <td style="text-align: right">
                                <input type="hidden" name="id[<?= $product->id?>]" value="new" />
                                <button type="button" class="btn btn-warning deletenew" aria-label="Left Align">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    } elseif ($blade=='create-estimator') {
                        ?>
                        <tr>
                            <td><img style="width: 80px" src="<?=  '/images/' . $image->location  ?>" /></td>
                            <td><?= $product->categories->category_name . ' ' . $product->p_reference . ' ' . $product->p_model ?> </td>
                            <td><input style="width: 50px" type="text" name="qty[<?= $product->id?>]" value="1"></td>
                            <td style="text-align: right"><?= number_format($product->p_retail,2)  ?></td>
                            <td style="text-align: right">$<input style="width: 100px" type="text" name="price[<?= $product->id?>]" value="<?= $product->p_price ?>"></td>
                            <td>
                                <button type="button" style="padding: 3px 5px" class="btn btn-danger deleteitem" aria-label="Left Align">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    } elseif ($blade=='create-order-estimator') {
                        ?>
                        <tr>
                            <td><img style="width: 80px" src="<?=  '/images/' . $image->location  ?>"</td>
                            <td style="width: 30%;">
                            <input type="hidden" name="model[<?= $product->id?>]" value="<?= $product->p_model ?>" />
                                <?= $product->categories->category_name . ' ' . $product->p_reference . ' ' . $product->p_model ?></td>
                            <td><input style="width: 50px;" type="text" name="qty[<?= $product->id?>]" value="1"></td>
                            <td style="text-align:center"><?= $product->p_qty ?></td>
                            <td>$<input style="width: 80px" type="text" name="price[<?= $product->id?>]" value="<?= $product->p_price ?>"></td>
                            <td><input autocomplete="off" oninput="setCustomValidity('')" value="<?= $product->p_serial ?>" oninvalid="this.setCustomValidity('Please Enter valid a serial number')" style="width: 80px" type="text" name="serial[<?= $product->id?>]" required /></td>
                            <td style="width: 30px">
                                <button type="button" class="btn btn-warning deleteitem" aria-label="Left Align">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    } elseif ($blade=='memotransfer') {
                        ?>
                        <tr>
                            <td><img style="height: 70px" src="<?=  '/images/' . $image->location  ?>"></td>
                            <td><?= $product->id ?></td>
                            <td>
                                <input style="width: 40px" type="hidden" name="model[<?= $product->id ?>]" value="<?=  $product->p_model  ?>">
                                <?=  $product->title?>
                            </td>
                            <td><input style="width: 50px" type="text" name="qty[<?= $product->id?>]" value="<?=  $product->p_qty ?>"></td>
                            <td>$<input style="width: 80px" type="text" name="price[<?= $product->id?>]" value="<?= $product->p_price ?>"></td>
                            <td><?= $product->p_serial ?></td>
                            <!-- oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Please Enter valid a serial number')" -->
                            <td>
                                <button type="button" class="btn btn-danger deleteitem" aria-label="Left Align">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                } else { ?>
                    <tr>
                    <?php
                        if (count($product->images)) {
                            $image = $product->images->first();
                            $path = '/images/thumbs/'.$image->location;
                            $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' title='$image->title' alt='$image->title' src='$path'></a>";
                        } else {
                            $image="/images/no-image.jpg";
                            $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
                        }
                    ?>
                        <td><?= $path ?><input type="hidden" name="product_id[]" value="<?= $product->id ?>">
                            <input type="hidden" value="0" name="op_id[]" />
                        </td>
                        <td><?= $product->id ?></td>
                        <td  style="width:28%">
                            <input type="text" value="<?= $product->title ?>" name="product_name[]">
                            <input type="hidden" class="p_retail">
                        </td>
                        <td><input style="width: 50px" type="text" name="qty[]" value="<?= $product->p_qty ?>"></td>
                        <td style="text-align: right"><input type="hidden" name="serial[]" value="<?= $product->p_serial ?>">
                            <?= $product->p_serial ?></td>
                        <td style="text-align: right;width: 72px"><span class="hide"><?= number_format($product->p_price) ?></span></td>
                        <td style="text-align: right"><input style="width: 70px" type="text" name="price[]"></td>
                        <td style="text-align: right">
                            <button class="btn btn-danger deleteitem" data-id="<?= $product->id ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                <?php
                }
            }

            $content = ob_get_clean();
            return response()->json($content);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = \DB::table('categories')->orderBy('category_name','asc')->get();
        $custom_columns = getCustomColumns();
        return view('admin.products.create',['pagename' => 'New Product','categories' => $categories, 'custom_columns'=>$custom_columns]);
    }

    public function jewelryCreate()
    {
        $categories = \DB::table('categories')->orderBy('category_name','asc')->get();
        return view('admin.products.jewelrycreate',['pagename' => 'New Jewelry Product','categories' => $categories]);
    }

    public function bezelCreate()
    {
        $categories = \DB::table('categories')->orderBy('category_name','asc')->get();
        return view('admin.products.bezelcreate',['pagename' => 'New Bezel Product','categories' => $categories]);
    }

    public function duplicate($id='')
    {
        $categories = \DB::table('categories')->orderBy('category_name','asc')->get();
        if ($id) {
            $product = Product::find($id);
            $custom_columns = getCustomColumns();
            //dd($custom_columns);
            return view('admin.products.duplicate',['pagename' => 'Duplicate Product','categories' => $categories,'product' => $product,'custom_columns'=>$custom_columns]);
        }

        return view('admin.products.duplicate',['pagename' => 'Duplicate Product','categories' => $categories]);
    }

    public function passInfo(Request $request) {
        return $request;
    }

    // public function storeDuplicate(ProductRequest $request,$id=''){

    //     $validated = $request->validated(); // This validation rule located in \App\Http\Requests\ProductRequest

    //     $product=$this::saveProduct($request->all());
    //     $id = $product->id;
    //     $modelName = str_replace([' - ',',','"',"'"],'',$request['p_model']);
    //     $categoryName = $request['p_category'];
    //     $referenceName = $request['p_reference'];

    //     $reminder = Reminders::whereRaw("criteria LIKE '%$categoryName%' AND ".
    //         "criteria LIKE '%$modelName%' AND criteria LIKE '%$categoryName%'")
    //         ->where('status',1)
    //         ->first();

    //     if (!empty($reminder)) {
    //         $condition = unserialize($reminder->product_condition);
    //         $boxpapers = unserialize($reminder->boxpapers);
    //         $box = isset($request['p_box']) && $request['p_box'] == 'on' ? "Box" : "";
    //         $papers = isset($request['p_papers']) && $request['p_papers'] == 'on' ? "Papers" : "";

    //         if (in_array($request['p_condition'],$condition) && (in_array($box,$boxpapers) || in_array($papers,$boxpapers)))
    //             return redirect("admin/products/$id/edit/?reminder=".$reminder->id);
    //     }
    //     return redirect("admin/products/$id/edit");
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request,$id='')
    {
        $validated = $request->validated();

        $product=$this::saveProduct($request->all());

        $this->postToEbay($product);
        AIProductDescription::dispatch($product)->delay(now()->addMinutes(1));

        $id = $product->id;

        $modelName = str_replace([' - ',',','"',"'"],'',$request['p_model']);
        $categoryName = $request['p_category'];
        $referenceName = $request['p_reference'];

        $reminder = Reminders::whereRaw("criteria LIKE '%$categoryName%' AND ".
            "criteria LIKE '%$modelName%' AND criteria LIKE '%$categoryName%'")
            ->where('status',1)
            ->first();

        if (!empty($reminder)) {
            if ($request['printAfterSave'] == 1)
                return redirect("admin/products/$id/edit/?reminder=$reminder->id&print=$id");
            else {
                $condition = unserialize($reminder->product_condition);
                $boxpapers = unserialize($reminder->boxpapers);
                $box = isset($request['p_box']) && $request['p_box'] == 'on' ? "Box" : "";
                $papers = isset($request['p_papers']) && $request['p_papers'] == 'on' ? "Papers" : "";

                if (in_array($request['p_condition'],$condition) && (in_array($box,$boxpapers) || in_array($papers,$boxpapers)))
                    return redirect("admin/products/$id/edit/?reminder=".$reminder->id);
            }
        } else {
            if ($request['printAfterSave'] == 1)
                return redirect("admin/products/$id/edit/?print=$id");
            else return redirect("admin/products/$id/edit");
        }

        $this->updateRelatedProducts($id,$request);
    }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBezel(Request $request,$id='')
    {
        // if (\Auth::user()->name == 'Edward B')
        //     dd($request->all());

        $validator = \Validator::make($request->all(), [
            'p_category' => 'required',
            'p_condition' => 'required',
            'p_qty' => 'required',
            'supplier' => 'required',
            'p_price' => 'required',
            'p_color' => 'required'
        ]);


        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $product=$this::saveProduct($request->all());
        $id = $product->id;

        return redirect("admin/products/$id/bezeledit");
    }

    protected function generateTitle($output) {
        $material='';$model='';
        $reference='';$category_name='';$casesize='';
        $features='';

        $category = Category::find($output['category_selected']);
        if (isset($output['p_model']))
            $model = $output['p_model'];

        if (isset($output['p_casesize']))
            $casesize=str_replace(' ', '', $output['p_casesize']);

        if (isset($output['p_reference']))
            $reference = $output['p_reference'];

        if (isset($output['p_material'])) {
            if ($output['group_id']==0)
                $material = Materials()->get($output['p_material']);
            elseif ($output['group_id']==1)
                $material = MetalMaterial()->get($output['p_material']);
        }

        $gender = $output['p_gender'];
        if ($category)
            $category_name=$category->category_name;

        $orgTitle='';

        if ($output['group_id'] == 0)
            $orgTitle = "$category_name $model $casesize $reference $material $gender Watch";
        else {
            $jewelryType =$output['jewelry_type'];
            if ($category_name)
                $orgTitle = $category_name.' ';
            if ($model)
                $orgTitle .= $model.' ';
            if ($reference)
                $orgTitle .= $reference.' ';

            $orgTitle .= "$material $gender $jewelryType";
        }

        return $orgTitle;
    }

    protected function saveProduct($request) {
        $data = $request;
        if ($data['group_id']==0) {
            $data['p_box'] = isset($data['p_box']) && $data['p_box'] == 'on' ? 1 : 0;
            $data['p_papers'] = isset($data['p_papers']) && $data['p_papers'] == 'on' ? 1 : 0;
        }

        //$data['p_return'] = isset($data['p_return']) && $data['p_return'] == 'on' ? 1 : 0;
        $category = Category::where('category_name','=',$data['p_category'])->get();
        if (count($category)==0 && isset($data['p_category'])) {
            $cat = Category::create([
                'category_name' => $data['p_category'],
                'location' => strtolower(str_replace([' ','&','/','.'],'-',$data['p_category']))
            ]);
            $data['category_id'] = $cat->id;
        } else {
            $data['category_id'] = $data['category_selected'];
        }

        if (!$data['category_id']) $data['category_id'] = 1;

        $customer = Customer::where('company','=',$data['supplier'])->get();
        if (count($customer)==0 && isset($data['supplier'])) {
            Customer::create([
                'company' => $data['supplier'],
            ]);
        }

        $data['web_price'] = $data['p_newprice'];

        $data['slug']=$this->createSlug($data);
        $serial=strtoupper($data['p_serial']);
        $data['p_serial'] = $serial;

        $title = $this->generateTitle($data);

        $data['title'] = $title;

        foreach ($data as $key => $index) {
            if ( strpos($key,'position_') !== false) {
                    unset($data[$key]);
            }
        }

        $rolexBySerial = $this->RolexYearBySerial($request['category_selected'],$request['p_condition'],$serial,$data['p_year']);
        if ($rolexBySerial) {
            $data['serial_code'] = $rolexBySerial['serial'];
            if ($rolexBySerial['year'])
                $data['p_year'] = $rolexBySerial['year'];
        }

        if ($request['group_id'] == 2) { // 2=Bezel group id
            $data['p_metal_cost'] = $request['metal_cost'];
            $data['p_metal_market_cost'] = $request['metal_cost'];
            $data['p_metal_weight'] = $request["metal_weight"];
            $data['p_diamond_cost'] = $request['diamond_cost'];
            $data['p_diamond_market_cost'] = $request['diamond_market_cost'];
            $data['p_diamond_weight'] = $request['diamond_weight'];
            $data['p_labor_cost'] = $request['labor'];
        }

        //dd($data);
        $product = Product::create($data);

        if (isset($data['filename'])) {
            $title = $data['title'];

            foreach ($data['filename'] as $filename) {
                $image=Image::create([
                    'title' => $title,
                    'location' => $filename,
                    'position' => 0
                ]);


                $product->images()->attach($image->id);

            }
        }

        //$product->searchable(); // Needed for algolia scout
        //$product->update();
        $product = Product::find($product->id);
        $data['id'] = $product->id;
        $keyword_build=$this->generateKeywordDescription($data);

        $product->update(['keyword_build' => $keyword_build]);

        return $product;

    }

    protected function saveBezelProduct($request) {
        $p_box = isset($request['p_box']) && $request['p_box'] == 'on' ? 1 : 0;
        $papers = isset($request['papers']) && $request['papers'] == 'on' ? 1 : 0;

        $category = Category::where('category_name','=',$request['category'])->get();
        if (count($category)==0 && isset($request['category'])) {
            $cat = Category::create([
                'category_name' => $request['category'],
                'location' => strtolower(str_replace([' ','&','/','.'],'-',$request['category']))
            ]);
            $request['category_selected'] = $cat->id;
        }

        $customer = Customer::where('company','=',$request['supplier'])->get();
        if (count($customer)==0 && isset($request['supplier'])) {
            Customer::create([
                'company' => $request['supplier'],
            ]);
        }

        $slug=$this->createSlug($request);
        $keyword_build=$this->generateKeywordDescription($request);

        $data=array(
            'group' => $request['group'],
            'category_id' => $request['category_selected'],
            'title' => $this->generateTitle($request),
            'p_condition' => $request['condition'],
            'p_model' => $request['model'],
            'p_material' => $request['material'],
            'p_reference' => $request['reference'],
            'p_serial' => $request['serial'],
            'p_box' => $p_box,
            'p_papers' => $papers,
            'p_price' => $request['price'],
            'p_retail' => $request['retail'],
            'p_qty' => $request['qty'],
            'supplier' => $request['supplier'],
            'supplier_invoice' => $request['supplier_invoice'],
            'p_status' => $request['status'],
            'p_comments' => $request['comments'],
            'p_smalldescription' => $request['smalldescription'],
            'p_gender' => $request['gender'],
            'p_strap' => $request['strap'],
            'p_color' => $request['color'],
            'keyword_build'=>$keyword_build,
            'slug' => $slug
        );

        $product = Product::create($data);

        if (isset($request['filename'])) {
            $title = $request['title'];

            foreach ($request['filename'] as $filename) {
                $image=Image::create([
                    'title' => $title,
                    'location' => $filename,
                    'position' => 0
                ]);
                $product->images()->attach($image->id);
            }
        }

        return $product;
    }

    public function storeJewelry(Request $request) {
        $message = array('p_condition.not_in' => 'Product condition must be specified.',
        'p_material.required' => 'Please specify the color of the jewelry');

        $validator = \Validator::make($request->all(), [
            'p_condition' => 'required|not_in:0',
            'p_qty' => 'required',
            'supplier' => 'required',
            'p_price' => 'required',
            'p_material' => 'required'
        ],$message);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $request['p_box'] = isset($request['p_box']) && $request['p_box'] == 'on' ? 1 : 0;

        $category = Category::where('category_name','=',$request['p_category'])->get();

        if (count($category)==0 && isset($request['p_category'])) {
            $cat = Category::create([
                'category_name' => $request['p_category'],
                'location' => strtolower(str_replace([' ','&','/','.'],'-',$request['p_category']))
            ]);
            $request['category_selected'] = $cat->id;
        }

        $customer = Customer::where('company','=',$request['supplier'])->get();
        if (count($customer)==0 && isset($request['supplier'])) {
            Customer::create([
                'company' => $request['supplier'],
            ]);
        }

        $request['slug']=$this->createSlug($request);
        $request['keyword_build']=$this->generateKeywordDescription($request);

        $product = Product::create($request->all());

        if (isset($request['filename'])) {
            $title = $request['title'];

            foreach ($request['filename'] as $filename) {
                $image=Image::create([
                    'title' => $title,
                    'location' => $filename,
                    'position' => 0
                ]);
                $product->images()->attach($image->id);
            }
        }

        return redirect('admin/products');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    protected function editProduct($id) {

        date_default_timezone_set('America/New_York');
        $product = Product::find($id);

        // $response = \Gate::inspect('edit', $product);
        // dd($response);

        $custom_columns = getCustomColumns();
        if (!$product)
            return $product;

        $categories = \DB::table('categories')->orderBy('category_name','asc')->get();
        return array('categories'=>$categories,'product' => $product,'custom_columns'=>$custom_columns);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (\Auth::user()->name == 'Edward B') {

        // }

        $obj = $this->editProduct($id);
        if (!$obj)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        // $repair = Repair::with('products')->whereHas('products', function($query) use($id) {
        //     $query->where("products.id",$id);
        // })->where('status',0)->first();
        $product = $obj['product'];
        $orders = $product->orders;

        // foreach ($product->orders as $order) {

        //     foreach ($order->returns as $return) {
        //         echo $return->pivot->product_id . '<br>' ;
        //     }
        // }
        // dd('asfd');
        return view('admin.products.edit',['pagename' => 'Edit Product', 'product' => $product, 'categories' => $obj['categories'], 'custom_columns'=>$obj['custom_columns']]); //, 'repair' => $repair]);
    }

    public function bezelEdit($id)
    {
        $obj = $this->editProduct($id);

        if (!$obj)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view('admin.products.bezeledit',['pagename' => 'Edit Product', 'product' => $obj['product'], 'categories' => $obj['categories']]);
    }

    public function jewelryEdit($id)
    {
        $obj = $this->editProduct($id);

        if (!$obj)
            return response()->view('errors/admin-notfound',['id'=>$id],404);

        return view('admin.products.jewelryedit',['pagename' => 'Edit Product', 'product' => $obj['product'], 'categories' => $obj['categories']]);
    }

        /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateBezel(Request $request, $id)
    {

        //$message = ['serial.unique' => 'Combination of serial number and model number already exists, please enter another serial number.'];
        $product = Product::find($id);
        $validator = \Validator::make($request->all(), [
            'p_category' => 'required',
            'p_condition' => 'required',
            //'serial' => 'required|unique:products,p_serial,'.$product->id.',id,p_model,'.$product->p_model,
            'p_qty' => 'required',
            'p_price' => 'required',
            'supplier' => 'required'
            // 'retail' => 'required'
        ],$message);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $this->updateProduct($request->all(),$product);

        $keys='';
        //dd($request->all());
        foreach($request->all() as $key=>$value){

            if (strpos($key,'position_')!==false){
                    $keys = explode("_",$key)[1];
                    echo($keys);
                    Image::find($keys)->update([
                        'position' => $value
                    ]);

            }
        }

        redirect()->route('admin.products.editbezel', $id);
        //return redirect("admin/products/$id/editbezel");
    }


    private function updateRelatedProducts($id,$request) {

        $products = Product::whereHas('categories', function($query) use ($request) {
            $query->where('id',$request['category_selected']);
        })
            ->where('p_qty','>',0)
            ->where('p_model','LIKE','%'.$request['p_model'].'%')
            ->where('id','<>',$request['id'])
            ->where('p_reference',"<>",$request['p_reference'])
            ->get();


        $related = \App\Models\RelatedProducts::select('product_id')->where('parent_id',$id)->get()->pluck('product_id')->toArray();
        $skus = array();

        foreach ($products as $product) {

            //if (in_array($product->id, $related)) {
                $skus[] = $product->id;
            //}

        }

        if ($skus) {
            foreach ($skus as $sku) {
                $data[] = [
                    'parent_id' => $id,
                    'product_id' => $sku
                ];
            }
            \App\Models\RelatedProducts::insert($data);
        }
    }

    private function postToEbay($product) {

        if ($product->categories->category_name != "Rolex" && $product->p_newprice > 100
            && count($product->images)> 0 && $product->p_status == 0) {
                $listing = EbayListing::where('product_id',$product->id)->first();

                if (!$listing)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]]);
                elseif ($listing->listitem == null)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $related = \App\Models\RelatedProducts::where('parent_id',$id);
        if ($related->get())
            $related->delete();

        //if ($request['related'])
        $this->updateRelatedProducts($id,$request);
        $product = Product::find($id);
        $error=array(
            'p_condition' => 'required',
            'p_qty' => 'required',
            'p_price' => "required",
            'supplier' => 'required'
        );

        $message=[];
        if ($request['group_id']==0) {
            $error['p_category'] = 'required';

            //$error['p_serial'] = 'required|unique:products,p_serial,'.$product->id.',id,p_model,'.$product->p_model;
            $error['p_serial'] = [
                'required',
                Rule::unique('products')->ignore($product->id)->where(function ($query) use($request) {
                    $query
                        ->where('category_id', $request['category_selected'])
                        ->where('p_qty', '=', 1);

                })
            ];

            $message = [
                'p_serial.unique' => 'Combination of serial number and category name already exists, please enter another serial number.',
                'greater_than_field' => 'Web Price and Chrono24 Price must be greater than cost. 3rd Party may be left at $0.00'
            ];

        }

        if ($request['p_price3P'] > 0)
            $error['p_price3P'] = "numeric|greater_than_field:p_price";

        if ($request['p_webprice'] > 0)
            $error['p_webprice'] = "numeric|greater_than_field:p_price";

        \Validator::extend('greater_than_field', function($attribute, $value, $parameters) use($request) {
            $other = $request[$parameters[0]];
            return isset($other) and intval($value) > intval($other);
        });

        $validator = \Validator::make($request->all(), $error,$message);

        if ($validator->fails()) {
            return back()->withInput($request->all())->withErrors($validator);
        }

        $this->updateProduct($request->all(),$product);
        $this->postToEbay($product);

        AIProductDescription::dispatch($product); //->delay(now()->addMinutes(1));

        $keys='';
        //dd($request->all());
        foreach($request->all() as $key=>$value){

            if (strpos($key,'position_')!==false){
                    $keys = explode("_",$key)[1];
                    //echo($keys);
                    $image=Image::find($keys);
                    if ($image) {
                        $image->update([
                            'position' => $value
                        ]);
                    }
            }
        }

        $modelName = str_replace([' - ',',','"',"'"],'',$request['p_model']);
        $categoryName = $request['p_category'];
        $referenceName = $request['p_reference'];

        $reminder = Reminders::whereRaw("criteria LIKE '%$categoryName%' AND ".
            "criteria LIKE '%$modelName%' AND criteria LIKE '%$categoryName%'")
            ->where('status',1)
            ->first();

        if ($request['group_id']==0) {
            if (!empty($reminder)) {
                $condition = unserialize($reminder->product_condition);
                $boxpapers = unserialize($reminder->boxpapers);
                $box = isset($request['p_box']) && $request['p_box'] == 'on' ? "Box" : "";
                $papers = isset($request['p_papers']) && $request['p_papers'] == 'on' ? "Papers" : "";

                if (in_array($request['p_condition'],$condition) && (in_array($box,$boxpapers) || in_array($papers,$boxpapers)))
                    return redirect("admin/products/$id/edit/?reminder=".$reminder->id);
            }
            else
		        return redirect("admin/products/$id/edit");
        } elseif ($request['group_id']==1)
            return redirect("admin/products/$id/jewelryedit");
        else return redirect("admin/products/$id/bezeledit");
    }

    protected function createSlug($request) {
        $slug = '';$model='';

        if (isset($request['p_model']))
            $model=$request['p_model'];


        if (isset($request['slug']))
            $slug = $request['slug'];

        if (!$slug) {
            $r=rand(11111, 99999);
            $g = priceToLetters($request['p_price']);

            $category = Category::find($request['category_selected']);
            $category_name='';
            if ($category)
                $category_name=$category->category_name;

            $slug =  strtolower(str_replace([' ','&','/','.'],'-',$category_name.'-'.$model.'-'.$request['p_reference'].'-'.$request['p_color'].'-'.$g.'-'.$r));
            if (strpos($slug,'--')>0)
                $slug = str_replace('--','-',$slug);
            if (strpos($slug,'--')>0)
                $slug = str_replace('--','-',$slug);
        }

        return $slug;
    }

    protected function generateKeywordDescription($request) {

        $condition = Conditions()->get($request['p_condition']);
        $gender = Gender()->get($request['p_gender']);
        $material='';
        if ($request['p_material']!=0){
            if ($request['group_id'] == 1)
                $material = MetalMaterial()->get($request['p_material']);
            else
                $material = Materials()->get($request['p_material']);
            $material=strtolower($material).' bezel, ';
        }

        if ($condition == "Unworn")
            $condition = "New / Unworn";

        $strap='';$model='';$keyword_build='';
        $casesize='';
        $serial = '';

        if (isset($request['p_casesize']))
            $casesize=str_replace(' ', '', $request['p_casesize']);

        if ($request['group_id']==0) {
            $model = str_replace('-', '', $request['p_model']);
            $strap = Strap()->get($request['p_strap']);
            $strap=strtolower($strap) . ' strap, ';

            $keyword_build=$condition. ' '. $gender.' '.$request['p_category'].' '
                .$model. ' ' . $casesize . ' '
                . $request['p_reference']. ' '
                . $strap . $material
                . 'on '.strtolower($request['p_color']).' face watch.';

        } elseif ($request['group_id'] == 1) {
            $keyword_build=$condition. ' '. $gender.' '.$request['p_category'].' '
                .$model. ' ' . $casesize . ' '
                . $request['p_reference']. ' '
                . $material;
        }

        return $keyword_build . ' ' . $request['id'];
    }

    protected function RolexYearBySerial($category_selected,$condition,$serial,$year) {
        $serial_code = '';

        if ($category_selected == 1 && ($condition == 3 || $condition == 4)) { // Rolex
            if (ord($serial[0]) >= 65 && ord($serial[0]) <= 90) {
                for ($i=1; $i<strlen($serial);$i++) {
                    if (ord($serial[$i]) >= 65 && ord($serial[$i]) <= 90) {
                        $serial_code = 'Scrambled Serial';
                        break;
                    }
                }
                if (!$serial_code)
                    $serial_code = $serial[0].' Serial';

            } elseif (ord($serial[0]) >= 48 && ord($serial[0]) <= 57) {
                for ($i=0; $i<strlen($serial);$i++) {
                    if (ord($serial[$i]) >= 65 && ord($serial[$i]) <= 90) {
                        $serial_code = 'Scrambled Serial';
                        break;
                    }
                }

                if (!$serial_code)
                    if (strlen($serial)>=7)
                        $serial_code = 'Million Serial';
                    else $serial_code = 'Thousand Serial';
            }
        }

        if (!$year && $category_selected == 1) {
            $years = [
                2011 => "Scrambled",2010=>"G",2009=>"V",
                2008=>"M",2007=>"Z",2001=>"K",
                2006=>"Z",2005=>"D",2004=>"F",2003=>"F",
                2002=>"Y",2000=>"P",1999=>"A",1998=>"U",
                1997=>"U",1996=>"T",1995=>"W",1994=>"S",
                1993=>"S",1992=>"C",1991=>"X",
                1990=>"E",1989=>"L",1988=>"R"
            ];

            $definition = substr($serial_code,0,strpos($serial_code," "));

            $key = array_search($definition,$years);

            $year = "";
            if (false !== $key || $definition == "N" || $definition == "Z") {
                if ($key == 2011)
                    $year = "2011-Present";
                elseif ($definition == "F")
                    $year = "2003-2005";
                elseif ($definition == "N")
                    $year = "1991";
                elseif ($definition == "Z")
                    $year = "2006-2007";
                else $year = $key;
            }
        }

        return ["serial"=>$serial_code,"year"=>$year];
    }

    protected function updateProduct($productArray,$product) {
        $serial = '';
        $id = $product->id;
        $productArray = array_slice($productArray,2);

        if ($productArray['group_id']==0 || $productArray['group_id']==1) {
            $productArray['p_box'] = isset($productArray['p_box']) && $productArray['p_box'] == 'on' ? 1 : 0;
            $productArray['p_papers'] = isset($productArray['p_papers']) && $productArray['p_papers'] == 'on' ? 1 : 0;
        }

        //$productArray['p_return'] = isset($productArray['p_return']) && $productArray['p_return'] == 'on' ? 1 : 0;
        //if ($productArray['p_status'] == 10) $productArray['p_qty'] = 0;

        $category = Category::where('category_name','=',$productArray['p_category'])->get();

        if (count($category)==0 && isset($productArray['p_category'])) {
            $cat = Category::create([
                'category_name' => $productArray['p_category'],
                'location' => strtolower(str_replace([' ','&','/','.'],'-',$productArray['p_category']))
            ]);
            $productArray['category_id'] = $cat->id;
        } else {
            $productArray['category_id'] = $productArray['category_selected'];
        }

        $customer = Customer::where('company','=',$productArray['supplier'])->get();
        if (count($customer)==0 && isset($productArray['supplier'])) {
            Customer::create([
                'company' => $productArray['supplier'],
            ]);
        }

        //$product = Product::find($id);
        $gender = Gender()->get($productArray['p_gender']);

        $productArray['p_gender'] = $gender;
        $productArray['slug']=$this->createSlug($productArray);
        $productArray['id'] = $id;
        $keyword_build=$this->generateKeywordDescription($productArray);

        if (isset($productArray['p_serial'])) {
            $serial=strtoupper($productArray['p_serial']);
            $productArray['p_serial'] = $serial;
        }

        $productArray['keyword_build']=$keyword_build;

        if (!$productArray['title']) {
            $productArray['title'] = $this->generateTitle($productArray);
        }

        foreach ($productArray as $key => $index) {
            if ( strpos($key,'position_') !== false ) {
                    unset($productArray[$key]);
            }
        }

        $productArray['web_price'] = $productArray['p_newprice'];
        //dd($productArray);
        if (($product->p_status==2 || $product->p_status==9) && $productArray["p_status"] == 0) {
            $productArray["reserve_for"] = '';
            $productArray["reserve_amount"] = 0;
            $productArray["reserve_date"] = NULL;

            // $productArray["watchmaker"] = null;
            // $productArray["repair_reason"] = null;
            // $productArray["repair_date"] = NULL;

            if (isset($productArray['repair_id'])) {
                $repair_id = $productArray['repair_id'];
                $repair = Repair::find($repair_id);
                $repair->status = 1;
                $repair->update();
            }

            $productArray["p_status"] = 0;
        }

        $rolexBySerial = $this->RolexYearBySerial($productArray['category_id'],$productArray['p_condition'],$serial,$productArray['p_year']);
        if ($rolexBySerial) {
            $productArray['serial_code'] = $rolexBySerial['serial'];
            if ($rolexBySerial['year'])
                $productArray['p_year'] = $rolexBySerial['year'];
        }

        if ($productArray['p_qty'] == 0) {
            eBayEndItem::dispatch($id);
            // $listing = EbayListing::where('product_id',$id)
            //     ->where('status','active')
            //     ->first();

            // if ($listing){
            //     $item=EbayController::EndItem(['datainfo' => "reason=NotAvailable&itemID=".$listing->listitem]);
            //     $listing->delete();
            // }
        }

        // if ($productArray['group_id'] == 2) { // 2=Bezel group id
        //     $productArray['p_metal_cost'] = $productArray['metal_cost'];
        //     $productArray['p_metal_market_cost'] = $productArray['metal_market_cost'];
        //     $productArray['p_metal_weight'] = $productArray["metal_weight"];
        //     $productArray['p_diamond_cost'] = $productArray['diamond_cost'];
        //     $productArray['p_diamond_market_cost'] = $productArray['diamond_market_cost'];
        //     $productArray['p_diamond_weight'] = $productArray['diamond_weight'];
        //     $productArray['p_labor_cost'] = $productArray['p_labor_cost'];
        // }

        // if (\Auth::user()->name == 'Edward B')
        //      dd($arr_product);

        $product->update($productArray);

        if (isset($productArray['filename'])) {
            $title = strtolower(str_replace(' ','-',$productArray['title']));

            foreach ($productArray['filename'] as $filename) {
                $image=Image::create([
                    'title' => $title,
                    'location' => $filename,
                    'position' => 0
                ]);

                $product->images()->attach($image->id);
            }
        }

        return $product;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $product = Product::find($id);
        $product->delete();

        //$username=\Auth::user()->username;

        \Session::flash('message', "Successfully deleted product!");
        return redirect('admin/products');
    }

    /**
     * Restores the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->where('id',$id)->restore();
        //$username=\Auth::user()->username;

        \Session::flash('message', "Successfully restored product!");
        return redirect('admin/products');
    }
}
