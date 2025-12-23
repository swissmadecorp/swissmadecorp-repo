<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\GlobalPrices;
use Livewire\WithPagination;
use App\Models\Order;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Product;
use App\Jobs\AutomateEbayPost;
use App\Jobs\eBayEndItem;
use App\Models\TheShow;
use App\Models\EbayListing;
use App\Models\User;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use App\SearchCriteriaTrait;
use Illuminate\Support\Facades\Storage;
use App\Events\PackageSent;

class Products extends Component
{
    use WithPagination,  SearchCriteriaTrait, WithFileUploads;
    protected $paginationTheme = 'tailwind';

    #[Url(keep: true)]
    public $search = "";

    public $checked = false;
    public $productSelections = [];

    public $sortDirection = "DESC";
    public $sortBy = "id";
    public $onhand = 1;
    public $status = 0;
    public $editProductID = null;
    public $exportSelections = [1,0,0,0];
    public $selectAll = false;
    public $sproducts;
    public $messages=[];
    public $importFile;

    #[Validate('required|min:1|max:3')]
    public $productQty = null;

    #[Validate('required|min:1')]
    public $productDealerPrice = null;

    public $productFieldName = null;
    public $page = 1;

    protected $queryString = [
        'search',
        'page',
        'onhand',
        'status'
    ];

    public function doImport() {
        // 1. Get the CORRECT path to the file content
        if ($this->importFile === null) {
            $this->dispatch('import-incomplete',['error'=>1, 'errorMsg' => "No product(s) has been selected"]);
            return false;
        }
        $correctTempPath = $this->importFile->getRealPath();

        // Add a check just in case the path is null/invalid
        if (empty($correctTempPath) || !file_exists($correctTempPath)) {
            // Log an error or return a user-friendly message
            throw new \Exception("The temporary file could not be found or read.");
        }

        // 2. Read the contents using the correct path
        $contents = file_get_contents($correctTempPath);

        // 3. Define the custom storage disk
        $disk = Storage::build([
            'driver' => 'local',
            'root' => base_path(). '/public/uploads',
        ]);

        // 4. Store the actual file contents
        $disk->put('data.xlsx', $contents);

        // ... continue
        $file = base_path(). '/public/uploads/'. 'data.xlsx';
        $collection = Excel::toCollection(new \App\Imports\DataImport(), $file);

        $rows = [];
        foreach ($collection->first() as $row)
        {
            if ($row[1] != null && is_numeric($row[1])) {
                $rows[] = [$row[1],$row[5]];
            }
        }


        // $selections = array_fill_keys($rows,true);
        $this->dispatch('create-invoice', ids: $rows, page: 'products');
        $this->importFile = null;
    }

    public function updatedSelectAll($value) {
        if ($value) {
            $pr = $this->getProducts();

            $products = $pr['products']->get();
            $flippedArray = array_flip($products->pluck('id')->toArray());

            // Set all values to true
            $resultArray = array_fill_keys(array_keys($flippedArray), true);
            $this->productSelections = $resultArray;
        } else {
            $this->productSelections = [];
        }
    }

    public function doExport() {
        $ids = [];
        if (!empty($this->exportSelections)) {
            $productArray = (array_keys($this->exportSelections));
            foreach ($productArray as $key) {
                if ($this->exportSelections[$key] == true) {
                    $builds[] = $key;
                }
            }

            if (!empty($this->productSelections)) {
                $ids = $this->productsSelected();
            }

        }
        // $this->exportSelections = [];
        if (!$ids) {
            $this->dispatch('export-complete',['error'=>1, 'errorMsg' => "No product(s) has been selected"]);
            return false;
        }

        $products=Product::whereIn('id',$ids)
        ->orderBy('id','desc')
        ->get();

        return Excel::download(new ProductsExport($products,$builds), 'products.xlsx');
        // dd($this->exportSelections);
    }

    public function updateQty() {
        if (! Auth()->user()->hasRole('administrator')) {
            abort(403);
        }

        $id = $this->editProductID;
        $this->validateOnly('productQty');

        $product = Product::find($id);

        $product->p_qty = $this->productQty;
        if ($product->p_status == 8) // Sold
            $product->p_status = 0;

        if ($this->productQty==0) {
            $product->p_status = 8;
            eBayEndItem::dispatch([$id]);
        }

        $productInInvoice = \DB::table('order_product')
            ->where('product_id', $id)->first();

        if (isset($productInInvoice) && $productInInvoice->qty == 1) {
            $order_id = $productInInvoice->order_id;
            LivewireAlert::title("Product #$id is assigned to order #$order_id. Please remove the product from the invoice and try again.")->info()->timer(10000)->position(Position::TopEnd)->toast()->show();
            $this->cancelEdit();
            return;
        }

        $product->update();

        if ($this->productQty==1)
            $this->postToEbay($product,0);

        $this->cancelEdit();
    }

    public function setProductId($id) {
        $this->dispatch('current-productid',$id);
    }

    public function updateDealerPrice() {
        if (! Auth()->user()->hasRole('administrator')) {
            abort(403);
        }

        $id = $this->editProductID;
        $this->validateOnly('productDealerPrice');

        // Product::findOrFail($id)->update([
        //     'p_newprice' => $this->productDealerPrice
        // ]);

        $sign = '';
        $product = Product::find($id);

        $amount=$this->productDealerPrice;
        if ($amount==0) {
            $product->update(['p_newprice' => 0]);
            //Margin::where('product_id','=',$id)->delete();
            //return response()->json(array('error'=>'success','amount'=>$amount));
        }

        if (strpos($amount,'%')>0) {
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

        if ($product->category_id==1 && $product->p_condition==2) $rolexBoxMargin=100;
        $platforms = GlobalPrices::all();
        foreach ($platforms as $platform) {
            $percent = $platform->margin;

            $newprice = ceil($amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * ($percent/100)));
            if ($platform->platform == "Chrono24") {
                $price3p = $newprice;
            } elseif ($platform->platform == "Website") {
                $web_price = $newprice;

            }

        }

        $product->update([
            'p_newprice' => $amount,
            'discount_amount' => $discount,
            'web_price' => $web_price,
            'p_price3P' => $price3p
        ]);

        $this->postToEbay($product);

        $this->cancelEdit();
    }

    public function selectMultiple(array $ids, bool $state)
{
    dd($ids, $state);
    foreach ($ids as $id) {
        $this->productSelections[$id] = $state;
    }
}

    public function endOnEbay($product) {
        $twoMonthsAgo = strtotime('-3 months');

        $now = time();
        // Format in eBay ISO 8601 UTC style
        $formattedToday = gmdate('Y-m-d\TH:i:s.000\Z', $now);
        // Format in eBay ISO 8601 style with UTC (Z)
        $formattedDate = gmdate('Y-m-d\TH:i:s.000\Z', $twoMonthsAgo);

        eBayEndItem::dispatchSync([$product], $formattedToday, $formattedDate);

        $msg = cache('ebay_end');
        if ($msg[1] == 'success') {
            LivewireAlert::title($msg[0])->success()->position(Position::TopEnd)->toast()->show();
        } elseif ($msg[1] == 'notfound') {
            $beginNumber = 9;
            $endNumber = 6;
            while ($msg[1] == 'notfound') {
                $twoMonthsAgo = strtotime("-$beginNumber months");
                $now = strtotime("-$endNumber months");
                // Format in eBay ISO 8601 UTC style
                $formattedToday = gmdate('Y-m-d\TH:i:s.000\Z', $now);
                // Format in eBay ISO 8601 style with UTC (Z)
                $formattedDate = gmdate('Y-m-d\TH:i:s.000\Z', $twoMonthsAgo);

                eBayEndItem::dispatchSync([$product], $formattedToday, $formattedDate);
                sleep(1);
                $beginNumber -= 3;
                $endNumber -= 3;
                if ($endNumber <= 0) {
                    break;
                }
                $msg = cache('ebay_end');
                if ($msg[1] == 'success') {
                    LivewireAlert::title($msg[0])->success()->position(Position::TopEnd)->toast()->show();
                    break;
                }
            }
        } else {
            LivewireAlert::title($msg[0])->error()->position(Position::TopEnd)->toast()->show();
        }
    }

    public function printTag($id) {
        if (! Auth()->user()->hasRole('administrator')) {
            abort(403);
        }

        $products = $this->productsSelected();
        if (empty($products)) {
            $products[] = $id;
        }
        $this->dispatch('printTag',['ids' => $products]);
    }

    private function productsSelected() {
        $productArray = (array_keys($this->productSelections));
        $build = [];
        foreach ($productArray as $key) {
            if ($this->productSelections[$key] == true) {
                $build[] = $key;
            }
        }

        return $build;
    }

    public function makeInvoice($id) {
        $build = [];
        if (!empty($this->productSelections)) {
            $build = $this->productsSelected();
            $this->productSelections = [];
        } else {
            $build[] = $id;
        }

        $this->dispatch('create-invoice', ids: $build, page: 'products');
    }

    public function returnToVendor($id) {

        if (! Auth()->user()->hasRole('administrator')) {
            abort(403);
        }
        $product = Product::find($id);
        $product->p_qty = 0;
        $product->p_return = 1;
        $product->p_status=0;
        $product->update();

        $theshow=TheShow::where('product_id',$id);
        if(count($theshow->get())){
            $theshow->delete();
        }

        $this->search = '';
        LivewireAlert::title("Product #$id has been returned to vendor successfully.")->success()->position(Position::TopEnd)->toast()->show();
    }

    public function deleteProduct($id) {

        if (! Auth()->user()->hasRole('administrator')) {
            abort(403);
        }
        $product = Product::find($id);
        $product->delete();

        //$username=\Auth::user()->username;
        LivewireAlert::title("Product #$id has been deleted successfully.")->success()->position(Position::TopEnd)->toast()->show();
    }

    #[On('set-onhand-page')]
    public function setonHandPage() {
        $this->onhand = 1;
    }

    #[On('refresh-products')]
    public function refreshProducts($msg) {

    }

    // #[On('receive-message')]
    // public function receiveMessage($payload) {
    //     $this->messages[] = [
    //         'from' => $payload['from'],
    //         'message' => $payload['message'],
    //         'recipientId' => $payload['recipientId']
    //     ];
    // }

    #[On('display-message')]
    public function displayMessage($msg) {

        if (is_array($msg)) {
            // request()->session()->flash('message', $msg['msg']);
            if (isset($msg['msg']) && !isset($msg['status'])) {
                if (!isset($msg['reminder'])) { // If reminder is not set, show success otherwise it's going to display the info reminder alert.
                    LivewireAlert::title($msg['msg'])->success()->position(Position::TopEnd)->toast()->show();
                }
            } else LivewireAlert::title($msg['msg'])->timer(10000)->info()->position(Position::TopEnd)->toast()->show();

            if (!isset($msg['hide'])) $msg['hide'] = 1;

            $this->dispatch('hide-slider',$msg['hide']);
        } elseif ($msg)
            LivewireAlert::title($msg)->success()->position(Position::TopEnd)->toast()->show();

        $this->dispatch('process-product-item-messages',$msg);
    }

    public function postToEbay($product,$displayMsg=1) {
        if (is_numeric($product)) {
            $product = Product::find($product);
            // request()->session()->flash('message', "Product submitted to eBay.");

        }

        $status = [0,1,2,5];
        if ($product->categories->category_name != "Rolex" && $product->p_newprice > 100
            && count($product->images)> 0 && in_array($product->p_status, $status)) {
                $listing = EbayListing::where('product_id',$product->id)->first();

                if (!$listing)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]]);
                elseif ($listing->listitem == null)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]]);

                    if ($displayMsg)
                        LivewireAlert::title("Product #$product->id has been submitted to eBay successfully.")->success()->position(Position::TopEnd)->toast()->show();
        } else {
            if ($product->categories->category_name == "Rolex")
                LivewireAlert::title("Rolex watches do not go to eBay.")->error()->position(Position::TopEnd)->toast()->show();
            elseif ($product->p_newprice < 100)
                LivewireAlert::title("Price cannot be less than 1.")->error()->position(Position::TopEnd)->toast()->show();
        }
    }

    public function cancelEdit() {
        $this->reset('editProductID','productQty','productDealerPrice','productFieldName');
    }

    public function createInvoice($id) {

        if (!empty($this->productSelections)) {
            $build = '';
            $productArray = (array_keys($this->productSelections));
            foreach ($productArray as $key) {
                if ($this->productSelections[$key] == true) {
                    if (!$build)
                        $build = "?id[]=" . $key;
                    else $build .= "&id[]=" . $key;
                }
            }

            $this->reset('productSelections');
            if ($build)
                return redirect()->to("/admin/orders/create$build");
            else redirect()->to("/admin/orders/create?id=$id");
        } else {
            return redirect()->to("/admin/orders/create?id=$id");
        }
    }

    public function redirectToProductPage($id) {

        return redirect()->to("/admin/products/$id/edit");
    }

    public function editItem($id) {
        $this->dispatch('edit-item',$id);
    }

    public function editMode($id, $fieldName) {
        $this->editProductID = $id;
        switch ($fieldName) {
            case "qty":
                $this->productQty = Product::findOrFail($id)->p_qty;
                break;
            case "dealerPrice":
                $product = Product::findOrFail($id);
                if ($product) {

                    $this->productDealerPrice=number_format($product->p_newprice,0,"","");
                }
                break;
        }

        $this->productFieldName = $id.'.'.$fieldName;
    }

    public function doSort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection == "ASC" ? 'DESC' : 'ASC';
            return;
        }
        $this->sortBy = $column;
        $this->sortDirection = "DESC";
    }

    public function updatingSearch() {
        $this->resetPage();
        // PackageSent::dispatch(auth()->user()->name, 'updated', Carbon::now(), 0);
    }

    public function updatedStatus(){
        $this->updatingSearch();
    }

    public function getProducts() {
        $columns = ['keyword_build','p_serial','id','web_price'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        if ($this->onhand==1)
            $sign = ">=";
        else $sign = "<=";

        $status = $this->status;
        $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                // make sure it’s enclosed in parentheses so that it binds correctly with the other AND clauses
                $query->whereRaw('(' . $searchTerm . ')');
            })->when($status > 0, function($query) use ($status) {
                if ($status == 11) {
                    $query->where('p_status','<>',5);
                } else
                    $query->where('p_status',$status);
            })
            ->where('p_qty', $sign , $this->onhand)
            ->orderBy($this->sortBy, $this->sortDirection);

        $totalQty = $products->sum('p_qty');
        $totalCost = $products->sum('p_price');

        return [
            'products' => $products,
            'totalCost' => $totalCost,
            'totalQty' => $totalQty
        ];
    }

    public function render()
    {
        // To add a textbox with a length counter just create 2 spans inside the inputbox
        // and within the 1st span add class="absolute -ml-6 mt-2" and
        //  within the 2nd span add x-text="wire.name.length"
        $pr = $this->getProducts();

        // $this->sproducts = $products->get();

        $products = $pr['products']->paginate(perPage: 10);

        return view('livewire.products',["products"=>$products, 'totalCost' => $pr['totalCost'], 'totalQty' => $pr['totalQty'], 'pageName' => "Products"])
            ->layoutData(['pageName' => 'Products'])
            ->title("Products");
    }

}
