<?php

namespace App\Livewire;

use PDF;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Product;
use App\Models\TheShow;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Elibyy\TCPDF\Facades\TCPDF;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\SearchCriteriaTrait;

class CTheShow extends Component
{
    use WithPagination,  SearchCriteriaTrait;

    public $page = 1;
    public $search = '';
    public $product;

    protected $queryString = [
        'page',
    ];

    public function removeItem($id) {
        $theshow=TheShow::where('product_id',$id);
        if(count($theshow->get())){
            $product = Product::where('id',$id)->first();
            $product->p_status=0;
            $product->update();

            $theshow->delete();
            $this->search = '';
            $this->resetPage();
            $this->dispatch('input-set-focus');
        }

    }

    #[On('add-to-show')]
    public function addToShow() {
        $product = $this->product;
        $product->p_status=5;
        $product->update();

        Theshow::insert([
            'product_id'=>$product->id,
            'created_at'=>Carbon::now('America/New_York'),
        ]);

        $this->search = '';
        $this->product = null;
    }

    public function print() {
        $theshow = TheShow::with('product')->orderBy('product_id')->get();

        // set document information

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


        $pdf::SetFont('helvetica', '', 10, '', true);
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
        $pdf::SetAutoPageBreak(TRUE, 32);

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

        $pdf::setXY(15,10);
        ob_start(); $tamount=0;$tprice=0;
        ?>
        <table cellpadding="3" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color: #111;color:#fff">
                    <th width="90" style="border: 1px solid #ddd;color:#fff">Image</th>
                    <td width="50" style="border: 1px solid #ddd;color:#fff">Id</td>
                    <th width="220" style="border: 1px solid #ddd;color:#fff">Product Name</th>
                    <th width="100" style="border: 1px solid #ddd;color:#fff">Serial#</th>
                    <th width="90" style="border-right: 1px solid #ddd;color:#fff">Cost</th>
                    <th width="90" style="border-right: 1px solid #ddd;color:#fff">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i=0;
                    foreach ($theshow as $atshow) {
                    $i++;
                    $product = $atshow->product;
                    if ($product->images->first()) {
                        $img = "/images/thumbs/".$product->images->first()->location;
                    } else $img = "/images/no-image.jpg";

                    $tamount = $tamount + $product->p_price;
                    $tprice = $tprice + $product->p_price3P;
                    ?>
                <tr nobr="true">
                    <td width="90" style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;color:#fff;text-align: center">
                    <img style="width: 70px" src="<?= $img ?>">
                    </td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="50"><?= $product->id ?></td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;" width="220"><?= $product->title ?> </td>
                    <td style="border-left: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0; text-align: right;background-color:#eee" width="100"><?= $product->p_serial ?></td>
                    <td style="border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;text-align: right;" width="90"><?= number_format($product->p_price,0) ?> </td>
                    <td style="border-right: 1px solid #d0d0d0;border-left: 1px solid #d0d0d0;border-bottom: 1px solid #d0d0d0;text-align: right;" width="90"><?= number_format($product->p_price3P,0) ?> </td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: right" colspan="2"><b>Total</b></td>
                    <td style="text-align: right" colspan="2"><?= $i ?></td>
                    <td style="text-align: right">$<?= number_format($tamount,0) ?></td>
                    <td style="text-align: right">$<?= number_format($tprice,0) ?></td>
                </tr>
            </tfoot>
        </table>

        <?php

        $pdf::WriteHTML(ob_get_clean(), true, false, false, false, '');
        PDF::Output('items.pdf', 'I');
    }

    public function lookupProduct($id) {
        $theshow=TheShow::where('product_id',$id)->first();

        if(!$theshow){
            $columns = ['p_serial','id'];
            $searchTerm = $this->generateSearchQuery($this->search, $columns);
            $product = Product::with('categories')
                ->when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                    $query->whereRaw($searchTerm);
                })
                ->where('p_qty','>',0);


            if (!$product) {
                // return response()->json(array('error'=>"Product doesn't exist in the database"));
                // $this->alert('error', "Product doesn't exist in the database");
                LivewireAlert::title("Product doesn't exist in the database")->warning()->position(Position::TopEnd)->toast()->show();
                return;
            }

            if (count($product->get())>1) {
                // return response()->json(array('error'=>"Multiple products found. Please refine your search."));
                // $this->alert('error', "Multiple products found. Please refine your search.");
                LivewireAlert::title("Multiple items found. Search by serial number instead")->warning()->position(Position::TopEnd)->toast()->show();
                return;
            } else {
                $product = $product->first();
            }

            $this->product = $product;

            ob_start();?>
            <div class="">
                <div class="flex gap-4 justify-center">
                    <?php if (count($product->images)) { ?>
                        <img class="w-52 border" title="<?php $product->title ?>" alt="<?php $product->title ?>" src="<?= '/images/thumbs/' . $product->images->first()->location ?>">
                    <?php } else { ?>
                        <img class="w-52 border" title="<?php $product->title ?>" alt="<?php $product->title ?>" src="/images/no-image.jpg">
                    <?php } ?>

                    <div class="bg-gray-50 border p-2 rounded-lg space-y-2 text-left">
                        <div><span class="block font-bold text-sm uppercase">Id</span><span><?= $id ?></span></div>
                        <div><span class="block font-bold text-sm uppercase">Description</span> <h2><span><?= $product->title ?></h2></span></div>
                        <div class="flex justify-between">
                            <div>
                                <span class="block font-bold text-sm uppercase">Serial#</span>
                                <span><?= $product->p_serial ?></span>
                            </div>
                        <div>
                            <span class="block bord font-bold text-sm uppercase">Cost</span>
                            <span>$<?= number_format($product->p_price,0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-100 justify-between mt-3 pt-2 rounded-lg">
                <button type="button" class="SwalBtn1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Ok</button>
                <button type="button" class="SwalBtn2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Cancel</button>
                <button type="button" onclick="window.open('/admin/products/<?=$product->id?>/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Print Tag</button>
            <div>
            <?php
            $content=ob_get_clean();
            $this->dispatch('lookup-product-box',['id' => $id, 'content'=>$content]);
        } else {
            $this->dispatch('lookup-product-box',['id' => $id, 'content'=>'remove']);
        }
    }

    public function updatingSearch() {
        $this->resetPage();
    }

    public function render() {
        $columns = ['p_serial','id'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })
        ->orderBy('the_shows.created_at', 'desc')
        ->join('the_shows','the_shows.product_id','=','products.id');


        // $products = Product::orderBy('the_shows.created_at', 'desc')
        //     ->join('the_shows','the_shows.product_id','=','products.id');

        $totalQty = $products->sum('p_qty');
        $totalCost = $products->sum('p_price');
        $products = $products->paginate(perPage: 10);

        return view('livewire.the-show',["products"=>$products, 'totalQty' => $totalQty, 'totalCost'=> $totalCost ,'pageName' => "At The Show"]);
    }
}
