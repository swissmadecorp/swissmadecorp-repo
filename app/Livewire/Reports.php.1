<?php

namespace App\Livewire;

use DB;
use Livewire\Component;
use App\SearchCriteriaTrait;
use Livewire\WithPagination;
use App\Models\Product;

class Reports extends Component
{
    use WithPagination, SearchCriteriaTrait;

    public $page = 1;
    public $search = '';
    public $sortBy = 'max_date';
    public $displayName = '';
    public $sortDirection = 'DESC';

    protected $queryString = [
        'page',
    ];

    public function updatingSearch() { 
        $this->resetPage();
    }

    public function doSort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection == "ASC" ? 'DESC' : 'ASC';
            return;
        }
        $this->sortBy = $column;
        $this->sortDirection = "DESC";
    }

    public function render() {
        
        $columns = ['order_id','b_company','product_id','product_name'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);
        
        $products = Product::query()->when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })
            ->selectRaw('if(b_company="",concat(b_firstname, " " ,b_lastname),b_company) as company,order_id, product_name title,p_serial,COALESCE(product_id,0) product_id, orders.created_at max_date')
            ->leftJoin('order_product','product_id','=','products.id')
            ->Join('orders','orders.id','=','order_id')

            //->groupBy('p_model','product_id')
            ->orderBy($this->sortBy,$this->sortDirection)
            ->paginate(perPage: 10);
            
// dd($this->sortBy.' '.$this->sortDirection);
        return view('livewire.reports',['products' => $products, 'pageName' => "Reports"]);
    }
}
