<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;

class ExportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.exports',['pagename' => 'Export To Excel']);   
    }

    public function getAjaxProducts() {
        $total=0;$qty=0;

        $products = Product::with('categories')
            ->where('p_qty','>',0)
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
                $group = $product->group_id==0 ? $product->title . ' ' .$product->p_color.' Dial' : $product->title . ' ' .$product->p_color. ' Bezel';
                $group_id = $product->group_id;
                $product_id = $product->id;
                $editPath = '<a href="/admin/products/'.$product_id.'/edit">'.$product_id.'</a>';
                $condition = "<span class='condition'>".Conditions()->get($product['p_condition']) . "</span>";

                $data[]=array('sel'=>'',
                    'image'=>$path,
                    'id'=>'<a href="/admin/products/'.$product_id.'/edit">'.$product_id.'</a>', 
                    'name'=>$group.$condition,'retail'=>'$'.number_format($product->p_retail,0),
                    'cost'=>'<span class="hide">$'.number_format($product->p_price,0).'</span>', 'qty'=>$product->p_qty,
                    'discount'=>'<input type="text" name="discount[]" class="discount_'.$product_id.'" style="width: 80px">'
                    
                );

                if ($product->p_qty>0) {
                    $total +=$product->p_price*$product->p_qty;
                    $qty += $product->p_qty;
                }
            }
            
            return response()->json(array('data'=>$data,'total'=>'$'.number_format($total,2),'qty'=>$qty));
    }

    protected function findIdenticalItem($ref,$color,$cond,$strap,$model) {
        
        $product=Product::where('p_reference',$ref)
                       ->where('p_color',$color)
                       ->where('p_model',$model)
                       ->where('p_condition',$cond)
                       ->where('p_strap',$strap)
                       ->where('p_qty',1);

        return $product->count();
    }

    public function Excel(Request $request) {
        parse_str($request['form'],$output);
        
        $company = isset($output['company']) && $output['company'] == 'on' ? 1 : 0;
        $calculate = isset($output['calculate']) && $output['calculate'] == 'on' ? 1 : 0;
        $discount = isset($output['discount']) && $output['discount'] == 'on' ? 1 : 0;
        $serial = isset($output['serial']) && $output['serial'] == 'on' ? 1 : 0;
        $includeCost = isset($output['include_cost']) && $output['include_cost'] == 'on' ? 1 : 0;
        $includeNotes = isset($output['include_notes']) && $output['include_notes'] == 'on' ? 1 : 0;

        if ($discount)
            $lastCol = "G";
        elseif ($includeCost)  
            $lastCol = "E";  //$lastCol = "D"; 
        else $lastCol = "D";
        
        if ($company)
            $row_num = 2;
        else $row_num = 1;

        $spreadsheet = new Spreadsheet();

        $activeSheet=$spreadsheet->getActiveSheet();
        //add some data in excel cells
        $activeSheet
            ->setCellValue("A$row_num", 'Image')
            ->setCellValue("B$row_num", 'Description')
            ->setCellValue("C$row_num", 'Retail Price')
            ->setCellValue("D$row_num", 'Qty');

        $activeSheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        
        if ($discount) {
            if ($includeCost) {
                $activeSheet->setCellValue("E$row_num", 'Cost');
                $activeSheet->setCellValue("F$row_num", 'Discount Amt.');
                $activeSheet->setCellValue("G$row_num", 'Tot. Discount');
            } else {
                $activeSheet->setCellValue("E$row_num", 'Discount Amt.');
                $activeSheet->setCellValue("F$row_num", 'Tot. Discount');
            }
        } elseif ($includeCost) {
            $activeSheet->setCellValue("E$row_num", 'Cost');
        }
         
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

        $activeSheet->getStyle("A$row_num:$lastCol$row_num")->applyFromArray($cell_st);
        $activeSheet->getStyle("A$row_num:$lastCol$row_num")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('d9d9d9');

        $ids = $request['ids'];
        if (!$ids)
            return response()->json(array('error'=>'1','message'=>'No product(s) has been selected'));

        $products=Product::whereIn('id',array_column($request['ids'],'id'))
            ->orderBy('id','desc')
            ->get();
            
            
        $qty=0; $retail=0;

        foreach ($products as $product) {
            $row_num++;
            $qty+=$product->p_qty;

            $key=array_search($product->id,array_column($request['ids'],'id'));
            $amount= 0; //$request['ids'][$key]['discount'];

            if ($calculate) {
                if (strpos($amount,'%')>0) {
                    $amount=str_replace('%','',$amount);
                    $amount=$product->p_retail-($product->p_retail*($amount/100));
                } 
            }

            $totalFound=$this->findIdenticalItem($product->p_reference,$product->p_color,$product->p_condition,$product->p_strap,$product->p_model);
                        
            $retail = $product->p_serial; //$product->p_retail>0 ? $product->p_retail : '0';

            if ($lastCol=="G") {
                $activeSheet->getStyle("E$row_num:G$row_num")
                    ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            
                if ($retail) {
                    if ($includeCost) {
                        $activeSheet->setCellValue("E$row_num", $product->p_price);
                        $activeSheet->setCellValue("F$row_num", $amount);
                        $activeSheet->setCellValue("G$row_num", $amount*$totalFound);
                    } else {
                        $activeSheet->setCellValue("E$row_num", $amount);
                        $activeSheet->setCellValue("F$row_num", $amount*$totalFound);
                    }
                } else {
                    $activeSheet->setCellValue("C$row_num", 0);
                    if ($includeCost) {
                        $activeSheet->setCellValue("E$row_num", $product->p_price);
                        $activeSheet->setCellValue("F$row_num", 0);
                        $activeSheet->setCellValue("G$row_num", $amount);
                    } else {
                        $activeSheet->setCellValue("E$row_num", 0);
                        $activeSheet->setCellValue("F$row_num", $amount);
                    }
                }
            } elseif ($lastCol=="E" && $includeCost) {
                $activeSheet->setCellValue("E$row_num", $product->p_price);
            }

            $activeSheet
                ->setCellValue("B$row_num", $serial==1 ? $product->title . ' (SN: '.$product->p_serial.')' : $product->title)
                ->setCellValue("C$row_num", $retail)
                ->setCellValue("D$row_num", $totalFound);
            
            //$spreadsheet->getActiveSheet()->getPageMargins()->setRight(0.75);
            $image=$product->images->first();
            $noImage=false;$path='';

            if ($image) 
                $path = base_path().'/public/images/thumbs/'.$image->location;
            
            if (!file_exists($path)) {
                $noImage=true;
                $path = base_path().'/public/images/no-image.jpg';
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

            $activeSheet->getStyle("B$row_num")->getAlignment()->setWrapText(true);
            $activeSheet->getStyle("C$row_num")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            
            if ($lastCol=="G") {
                $activeSheet->getStyle("E$row_num")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

                $activeSheet->getStyle("F$row_num")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
                
                $activeSheet->getStyle("G$row_num")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            } elseif ($lastCol == "E") {
                $activeSheet->getStyle("E$row_num")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            }

            $activeSheet->getStyle("A$row_num:$lastCol$row_num")->applyFromArray($cell_st1);
            
            //set columns width
            $activeSheet->getDefaultRowDimension()->setRowHeight(81);
            $activeSheet->getRowDimension($row_num)->setRowHeight(81);
        }
        $activeSheet->getColumnDimension('A')->setWidth(16);
        $activeSheet->getColumnDimension('B')->setWidth(50);
        $activeSheet->getColumnDimension('C')->setWidth(15);

        if ($lastCol=="G") {
            if ($includeCost) {
                $activeSheet->getColumnDimension('E')->setWidth(15);
                $activeSheet->getColumnDimension('F')->setWidth(15);
                $activeSheet->getColumnDimension('G')->setWidth(15);
            } else {
                $activeSheet->getColumnDimension('E')->setWidth(15);
                $activeSheet->getColumnDimension('F')->setWidth(15);
            }
        } elseif ($lastCol=="E") {
            $activeSheet->getColumnDimension('E')->setWidth(15);
        }

        $total_row = $row_num+1;

        $activeSheet->insertNewRowBefore($total_row, 2);
        $activeSheet->getStyle("C$total_row")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
        
        $activeSheet->getStyle("E$total_row:F$total_row")->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

        $cell_st1 =[
            'font' =>['bold' => true],
            'alignment' =>[
                'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                ],
            ];

        $total_row++;
        $activeSheet->getStyle("B$total_row:F$total_row:")->applyFromArray($cell_st1);
        $activeSheet->setCellValue("B$total_row","Total:");
        $activeSheet->setCellValue("C$total_row","=SUM(C2:C$row_num)");
        $activeSheet->setCellValue("D$total_row","=SUM(D2:D$row_num)");
        
        if ($discount) {
            
            if ($retail) {
                if ($includeCost) {
                    $activeSheet->setCellValue("E$total_row","=SUM(E2:E$row_num)");
                    $activeSheet->setCellValue("F$total_row","=SUM(F2:F$row_num)");
                    $activeSheet->setCellValue("G$total_row","=SUM(G2:G$row_num)");
                } else {
                    $activeSheet->setCellValue("E$total_row","=SUM(F2:F$row_num)");
                    $activeSheet->setCellValue("F$total_row","=SUM(G2:G$row_num)");
                }
            }
        } elseif ($lastCol=="E") {
            $activeSheet->setCellValue("E$total_row","=SUM(E2:E$row_num)");
        }

        $activeSheet->getStyle("A$total_row:$lastCol$total_row")->getFill()
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
        $activeSheet->getStyle("A$total_row:$lastCol$total_row:")->applyFromArray($cell_st1);
        $activeSheet->getRowDimension($total_row)->setRowHeight(20);
        $activeSheet->getRowDimension($total_row+1)->setRowHeight(20);
        
        $activeSheet->getPageMargins()->setTop(.25);

        // $activeSheet->getHeaderFooter()
        //         ->setOddHeader('&C&H&14 Swissmade Corp. Proposal&R'.date('F j, Y',time()));
        $activeSheet->getHeaderFooter()
                ->setOddFooter('&L&BInventory&RPage &P of &N');
        
        if ($company) {
            // First Header Row Begin
            $activeSheet->mergeCells("A1:$lastCol"."1");
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
            $activeSheet->getStyle("A1:$lastCol".'1')->applyFromArray($cell_st);
            $activeSheet->setCellValue("A1","Swissmade Corp. Proposal");
            // First Header Row End
        
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath(base_path().'/public/images/logo.png');
            $drawing->setCoordinates("A1");
            
            $drawing->setHeight(60);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $activeSheet->getRowDimension(1)->setRowHeight(50);
        } else 
            $activeSheet->getRowDimension(1)->setRowHeight(20);

        $activeSheet->setTitle('Inventory'); //set a title for Worksheet
        
        //make object of the Xlsx class to save the excel file
        $writer = new Xlsx($spreadsheet);
        if (!file_exists(base_path()."/public/uploads/")) {
            mkdir(base_path()."/public/uploads/");
        }

        $filename='/public/uploads/swissmadecorp-'.date('m-d-Y',time()).'.xlsx';
        
        $fxls =base_path().$filename;
        $writer->save($fxls);
        return response()->json(array('error'=>0,'filename'=>str_replace("/public","",$filename)));
    }

    public function Chrono24XMLExport() {
        $product=Product::where('p_price3P','<>',0)
        ->where('group_id',0)
        ->where('p_qty',1);

        $XMLBegin = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
            <articles>";

        // foreach ($products as $product) {
        //     switch ($product->p_material) {
        //         case 4:
        //         case 6:
        //             $case_material = "Yellow gold";
        //             break;
        //         case 8:
        //         case 9:
        //             $case_material = "White gold";
        //             break;
        //         case 12:
        //         case 13:
        //             $case_material = "Rose gold";
        //             break;
        //         case 17: // Stainless steel
        //             $case_material = "Steel";
        //             break;
        //         case 5:
        //         case 7:
        //         case 10:
        //         case 11:
        //         case 14:
        //         case 15:
        //             $case_material = "Gold/Steel";
        //             break;
        //         case default:
        //             $case_material=Materials()->get($product->p_material);
        //     }

        //     switch ($product->p_strap) {
        //         case 8:
        //         case 9:
        //         case 11:
        //         case 12:
        //             $case_material = "Yellow gold";
        //             break;
        //         case 8:
        //         case 9:
        //             $case_material = "White gold";
        //             break;
        //         case 12:
        //         case 13:
        //             $case_material = "Rose gold";
        //             break;
        //         case 17: // Stainless steel
        //             $case_material = "Steel";
        //             break;
        //         case 5:
        //         case 7:
        //         case 10:
        //         case 11:
        //         case 14:
        //         case 15:
        //             $case_material = "Gold/Steel";
        //             break;
        //         case default:
        //             $case_material=Materials()->get($product->p_material);
        //     }

        //     $XML = "article>
        //     <basic_information>
        //         <price><![CDATA[".$product->p_price3P."]]></price>
        //         <price_negotiable>no</price_negotiable>
        //         <delivery_time><![CDATA[3-5 working days]]></delivery_time>
        //         <brand><![CDATA[".$product->p_brand."]]></brand>
        //         <model><![CDATA[".$product->p_model."]]></model>
        //         <product_name><![CDATA[".$product->title."]]></product_name>
        //         <gender><![CDATA[".$prodict->p_gender."]]></gender>
        //         <reference_number><![CDATA[".$product->p_reference."]]></reference_number>    
        //         <condition><![CDATA[".($product->p_condition+1)."]]></condition>
        //         <link><![CDATA[https://swissmadecorp.com/".$product->slug."]]></link>
        //     </basic_information>
        //     <caliber>
        //         <movement_type/>
        //         <caliber/>
        //         <power_reserve/>
        //         <additional_information/>
        //     </caliber>
        //     <case>
        //         <case_material><![CDATA[".$case_material."]]></case_material>
        //         <case_diameter><![CDATA[".$product->p_casesize."]]></case_diameter>
        //         <case_thickness/>
        //         <bezel_material/>
        //         <crystal></crystal>
    
        //         <additional_information/>
        //     </case>
    
        //     <bracelet>
        //         <bracelet_color><![CDATA[""]]></bracelet_color>
        //         <bracelet_material><![CDATA[]]></bracelet_material>
        //         <bracelet_length><![CDATA[]]></bracelet_length>
        //         <bracelet_width><![CDATA[]]></bracelet_width>
        //         <clasp><![CDATA[]]></clasp>
        //         <additional_information><![CDATA[]]></additional_information>
        //     </bracelet>
    
        //     <miscellaneous>
        //         <functions> date, moonphase, ... </functions>
        //         <orignal_papers><![CDATA[".$product->p_papers==0 ? 'No' : 'Yes' ."]]></orignal_papers>
        //         <original_box><![CDATA[".$product->p_box==0 ? 'No' : 'Yes' ."]]></original_box>
        //         <year></year>
        //         <water_resistance></water_resistance>
        //     </miscellaneous>
    
        //     <images>
        //         <image>".$images."</image>
        //     </images>
        // </article>";
        // }

    $XMLEnd = "<article>
        </articles>";

    $XML = $XMLBegin . $XML . $XMLEnd;

    return $XML;
    }
}
