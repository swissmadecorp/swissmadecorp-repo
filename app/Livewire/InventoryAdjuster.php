<?php

namespace App\Livewire;

use DB;
use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class InventoryAdjuster extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public $search = "";

    public $page = 1;

    protected $queryString = [
        'search',
        'page',
    ];

    protected function rebuildInventoryTable(): void
    {
        \Schema::dropIfExists('table_temp_a');

        DB::unprepared(
            "
                CREATE TABLE table_temp_a AS
                SELECT id
                FROM products
                WHERE p_qty > 0
                  AND p_status NOT IN (4, 5)
                  AND group_id = 0
            "
        );
    }

    protected function ensureInventoryTableExists(): void
    {
        if (! \Schema::hasTable('table_temp_a')) {
            $this->rebuildInventoryTable();
        }
    }

    public function removeItem($id) {
        $inventory = DB::table('table_temp_a')->where('id', $id);
        
        if ($inventory->exists()) {
            $inventory->delete();

            $this->search = "";

            $this->resetPage();
            $this->dispatch('input-set-focus');
        } 
    }

    public function updatingSearch()
    { 
        $this->resetPage();
    }

    public function refreshInventory() {
        $this->rebuildInventoryTable();
        $this->resetPage();
    }

    public function render() {
        $words = explode(' ', $this->search);
        $searchTerm = "";
        $searchWords = "";
        
        $columns = ['keyword_build','p_serial','products.id'];
        
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
            
        $this->ensureInventoryTableExists();

        $products = Product::select('products.*')
            ->when(strlen($searchTerm) > 0, function ($query) use ($searchTerm) {
                $query->whereRaw($searchTerm);
            })
            ->join('table_temp_a', 'table_temp_a.id', '=', 'products.id')
            ->orderBy('products.id', 'asc');

        $products = $products->paginate(perPage: 10);

        return view('livewire.inventory-adjuster',["products"=>$products, 'pageName' => "Inventory Adjuster"]);
    }
}
