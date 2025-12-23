<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ExportToExcel extends Component
{
    use WithPagination;

    public $page = 1;

    #[Url(keep: true)]
    public $search = "";

    public $columns = ['company'];
    public $checked = false;
    public $productSelections = [];
    public $productFieldName = null;
    public $selectAll = false;

    protected $queryString = [
        'search',
        'page'
    ];

    public function updatedProductSelections($value) {
        $this->productSelections = array_filter($this->productSelections);
    }

    public function Export() {
        // parse_str($request['form'],$output);
        
        $ids = $this->productSelections;
        
        if (!$ids) {
            $this->dispatch('export-complete',['error'=>1, 'errorMsg' => "No product(s) has been selected"]);
            return false;
        }

        $ids = array_filter($ids, function($value) {
            return $value === true;
        });
        
        $company = isset($this->columns[0]) ? 1 : 0;
        $is_serial = 0;$is_notes=0;$is_cost=0;
        
        if (in_array('serial', $this->columns)) {
            $is_serial = 1;
        }
        if (in_array('notes', $this->columns)) {
            $is_notes = 1;
        }
        if (in_array('cost', $this->columns)) {
            $is_cost = 1;
        }

        $products=Product::whereIn('id',array_keys($ids))
        ->orderBy('id','desc')
        ->get();

        return Excel::download(new ProductsExport($products,$is_serial,$is_notes,$is_cost), 'products.xlsx');
    }

    public function Export1() {
        
        $ids = $this->productSelections;
        if (!$ids) {
            $this->dispatch('export-complete',['error'=>1, 'errorMsg' => "No product(s) has been selected"]);
            return false;
        }

        $company = isset($this->columns[0]) ? 1 : 0;
        // $calculate = isset($output['calculate']) && $output['calculate'] == 'on' ? 1 : 0;
        // $discount = isset($output['discount']) && $output['discount'] == 'on' ? 1 : 0;
        $is_serial = isset($this->columns[1])  ? 1 : 0;
        // $includeCost = isset($output['include_cost']) && $output['include_cost'] == 'on' ? 1 : 0;
        // $includeNotes = isset($output['include_notes']) && $output['include_notes'] == 'on' ? 1 : 0;

        if ($company)
            $row_num = 2;
        else $row_num = 1;

        $spreadsheet = new Spreadsheet();

        $columnNames = ['Image','Description','Qty'];
        $columnsNewNames = [];

        if ($is_serial)
            $columnNames[] = "Serial";

        $activeSheet=$spreadsheet->getActiveSheet();
        
        //add some data in excel cells
        foreach ($columnNames as $index =>$column ) {
            $activeSheet->setCellValue(chr(65+$index)."$row_num", $column);
            $columnsNewNames[] = [chr(65+$index) => $column];
        }

        $activeSheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        
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

        $firstCol = array_key_first(reset($columnsNewNames)); // reset function is to reset array and go to the first index
        $lastCol = array_key_first(end($columnsNewNames)); // end is to get the last array index
        
        $activeSheet->getStyle("$firstCol$row_num:$lastCol$row_num")->applyFromArray($cell_st);
        $activeSheet->getStyle("$firstCol$row_num:$lastCol$row_num")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('d9d9d9');

        $ids = array_filter($ids, function($value) {
            return $value === true;
        });

        $products=Product::whereIn('id',array_keys($ids))
            ->orderBy('id','desc')
            ->get();
                
        $qty=0; $retail=0;

        foreach ($products as $product) {
            $row_num++;
            $qty+=$product->p_qty;

            $key=array_search($product->id,array_column($ids,'id'));
            $amount= 0; //$request['ids'][$key]['discount'];

            // $totalFound=$this->findIdenticalItem($product->p_reference,$product->p_color,$product->p_condition,$product->p_strap,$product->p_model);
                        
            $retail = $product->p_retail>0 ? $product->p_retail : '0';
            $serial = $product->p_serial; //$product->p_retail>0 ? $product->p_retail : '0';

            $activeSheet->setCellValue("B$row_num", $product->title . ' (Stock #: '. $product->id . ')');
            if ($is_serial)
                $activeSheet->setCellValue("D$row_num", $serial);

            $activeSheet->setCellValue("C$row_num", $product->p_qty);
            // $activeSheet->->setCellValue("D$row_num", $totalFound);
            
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
            // $activeSheet->getStyle("C$row_num")->getNumberFormat()
            //     ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD); // Retail calculation
           
            // $activeSheet->getStyle("E$row_num")->getNumberFormat()
            //     ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
           
            $activeSheet->getStyle("A$row_num:$lastCol$row_num")->applyFromArray($cell_st1);
            
            //set columns width
            $activeSheet->getDefaultRowDimension()->setRowHeight(81);
            $activeSheet->getRowDimension($row_num)->setRowHeight(81);
        }
        $activeSheet->getColumnDimension('A')->setWidth(16);
        $activeSheet->getColumnDimension('B')->setWidth(50);
        $activeSheet->getColumnDimension('C')->setWidth(15);

        // $activeSheet->getColumnDimension('E')->setWidth(15);
        
        $total_row = $row_num+1;

        $activeSheet->insertNewRowBefore($total_row, 2);
        // $activeSheet->getStyle("C$total_row")->getNumberFormat()
        //         ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
        
        // $activeSheet->getStyle("E$total_row:F$total_row")->getNumberFormat()
        //         ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);

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
        
        // $activeSheet->setCellValue("C$total_row","=SUM(D2:D$row_num)");
        

        // $activeSheet->setCellValue("E$total_row","=SUM(E2:E$row_num)");
        
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

        $filename = "swissmadecorp-".date('m-d-Y',time()).'.xlsx';
        $filepath="/public/uploads/$filename";
        
        $fxls =base_path().$filepath;
        $writer->save($fxls);

        $filepath = str_replace('/public','',$filepath);
        //return response()->json(array('error'=>0,'filename'=>str_replace("/public","",$filename)));
        $this->dispatch('export-complete',['error'=>0, 'filename' => "<a class='cursor-pointer' href='https://swissmadecorp.com$filepath'>$filename</a>"]);
    }

    public function updatedSelectAll($value) {
        if ($value) {
            $flippedArray = array_flip($this->products->get()->pluck('id')->toArray());

            // Set all values to true
            $resultArray = array_fill_keys(array_keys($flippedArray), true);
            $this->productSelections = $resultArray;
        } else {
            $this->productSelections = [];
        }
    }

    public function updatingSearch() { 
        $this->resetPage();
    }

    #[Computed]
    public function products() { //public function getProductsProperty() { // or use get(...)Property
        $words = explode(' ', $this->search);
        $searchTerm = "";
        $searchWords = "";
        
        $columns = ['keyword_build','p_serial','id'];
        
        if ($this->search) {
            $searchWords = "(";
            foreach($words as $word) {
                foreach ($columns as $key => $column) {
                    $searchWords .= $column.' LIKE "%'.$word .'%" OR ';
                }
                
                $searchWords = substr($searchWords,0,-4) . ") AND (";
                $searchTerm .= $searchWords;
                $searchWords = "";    
            }   
        }
       
        $searchTerm = substr($searchTerm,0,-6);
        
        $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })
        ->where('p_qty','>',0)
        ->orderBy('id', 'desc');

        return $products;
    }

    public function render()
    {
        return view('livewire.export-to-excel',["products"=>$this->products->paginate(10), 'pageName' => "Export To Excel"]);
    }
}
