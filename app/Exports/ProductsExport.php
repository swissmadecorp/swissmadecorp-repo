<?php

// Swiss Made Corp class
namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromView, WithColumnFormatting, WithStyles, WithPreCalculateFormulas, WithDrawings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return Products::select('model');
    // }

    private $products;
    private $columnWidths;
    private $criteria;
    private $is_serial=0;
    private $is_notes=0;
    private $is_cost=0;

    public function drawings()
    {
        // Calculate total width of merged cells (A1:E1)
        // $totalWidth = array_sum($this->columnWidths);

        // Set the approximate width of each Excel column (in points)
        // $excelColumnWidthFactor = 7.5; // Adjust if needed based on font and DPI

        // Calculate the horizontal offset to center the logo
        // $offsetX = ($totalWidth * $excelColumnWidthFactor / 2) - (50 / 2); // Adjust 50 to the half-width of your image

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Swiss Made Logo');
        $drawing->setPath("assets/logo-swissmade.jpg");
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        
        // $drawing->setOffsetX($drawing->getImageWidth());
        // $drawing->setOffsetX($offsetX);

        return $drawing;
    }

    public function __construct($products,$builds) {
        $this->products = $products;
        
        foreach ($builds as $build) {
            switch ($build) {
                case 1:
                    $this->is_company = 1;
                    break;
                case 2:
                    $this->is_serial = 1;
                    break;
                case 3:
                    $this->is_cost = 1;
                    break;
                case 4:
                    $this->is_notes = 1;
                    break;
            }
        }
        // dd($this->is_serial.' '.$this->is_cost.' ' . $this->is_notes);
    }

    public function styles(Worksheet $sheet)
    {
        $numOfRows = count($this->products)+2;
        $totalRow = $numOfRows + 1;

        // Add cell with SUM formula to last row
        $sheet->setCellValue("D{$totalRow}", "=SUM(D3:D{$numOfRows})");
// dd($numOfRows);
        // $spreadSheet = new Spreadsheet();
        // $this->workSheet = $spreadSheet->getActiveSheet();
        // dd(1);
            
        // dd($f);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);

                // $sheet->getStyle('A1:A'.count($this->products)+2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // $sheet->getStyle('A1:A'.count($this->products)+2)->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
                
                // $sheet->getStyle('B1:B'.count($this->products)+2)->getAlignment()->setWrapText(true); 

                // $this->columnWidths = [
                //     $sheet->getColumnDimension('A')->getWidth(),
                //     $sheet->getColumnDimension('B')->getWidth(),
                //     $sheet->getColumnDimension('C')->getWidth(),
                //     $sheet->getColumnDimension('D')->getWidth(),
                //     $sheet->getColumnDimension('E')->getWidth(),
                // ];

                // Access and customize the active sheet here
                $sheet->setTitle('Product Data'); // Example: Set a custom title for the sheet
                // $sheet->getStyle('A1:Z1')->getFont()->setBold(true); // Example: Make header row bold
            },
        ];
    }

    public function columnFormats(): array
    {
        if ($this->is_serial && !$this->is_cost)
            return [
                'E' => '@',
            ];
        elseif ($this->is_cost && !$this->is_serial) 
            return [
                'E' => '#,##0',
            ];
        elseif ($this->is_cost && $this->is_serial) 
            return [
                'E' => '@',
                'F' => '#,##0',
            ];
        else return [];
    }

    public function view(): View
    {
        
        return view('admin.exports.products', [
            'products' => $this->products,
            'is_serial' => $this->is_serial,
            'is_notes' => $this->is_notes,
            'is_cost' => $this->is_cost
        ]);
    }
}
