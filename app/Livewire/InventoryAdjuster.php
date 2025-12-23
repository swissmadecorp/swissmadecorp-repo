<?php

namespace App\Livewire;

use DB;
use Livewire\Component;
use App\Models\Product;
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

    public function removeItem($id) {
        $inventory = DB::table('table_temp_a')->where('id',$id);
        
        if(count($inventory->get())){
            $product=Product::join('table_temp_a','table_temp_a.id','=','products.id')
            ->where('table_temp_a.id',$id)->first();

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
        
        \Schema::dropIfExists('table_temp_a');
        $createTempTables = \DB::unprepared(
            "
                CREATE TABLE table_temp_a 
                    AS (
                    SELECT id
                    FROM products 
                    WHERE p_qty > 0 AND p_status <> 4 AND p_status <> 5 AND group_id = 0 
            );"
        );
        
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
            
        $m = \Schema::hasTable('table_temp_a');
        if ($m == false) {
            $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                    $query->whereRaw($searchTerm);
                })
                ->where('p_status','<>', 4)
                ->where('p_qty', 1)
                ->orderBy('id','asc');
            
            $createTempTables = \DB::unprepared(
                "
                    CREATE TABLE table_temp_a 
                        AS 
                        SELECT id
                        FROM products 
                        WHERE p_qty > 0 AND p_status <> 4 AND group_id = 0
                ;"
            );
        } else {
            $products = Product::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                    $query->whereRaw($searchTerm);
                })->join('table_temp_a','table_temp_a.id','=','products.id');
        
        }

        $products = $products->paginate(perPage: 10);

        return view('livewire.inventory-adjuster',["products"=>$products, 'pageName' => "Inventory Adjuster"]);
    }
}
