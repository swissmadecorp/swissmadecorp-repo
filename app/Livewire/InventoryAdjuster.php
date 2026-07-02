<?php

namespace App\Livewire;

use App\Events\InventoryAdjusterUpdated;
use App\Models\Product;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
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
        Schema::dropIfExists('table_temp_a');

        DB::unprepared(
            "
                CREATE TABLE table_temp_a AS
                SELECT id
                FROM products
                WHERE COALESCE(p_qty, 0) > 0
                  AND COALESCE(p_status, 0) <> 4
                  AND COALESCE(p_status, 0) <> 1
                  AND COALESCE(group_id, 0) = 0
            "
        );
    }

    protected function ensureInventoryTableExists(): void
    {
        if (! Schema::hasTable('table_temp_a')) {
            $this->rebuildInventoryTable();
        }
    }

    protected function buildSearchTerm(): string
    {
        $words = array_values(array_filter(explode(' ', trim($this->search))));
        $searchTerm = "";
        $searchWords = "";
        $columns = ['keyword_build', 'p_serial', 'products.id'];

        if ($words !== []) {
            $searchWords = "(";

            foreach ($words as $word) {
                foreach ($columns as $column) {
                    $searchWords .= $column.' LIKE "%'.$word.'%" OR ';
                }

                $searchWords = substr($searchWords, 0, -4).") AND (";
                $searchTerm .= $searchWords;
                $searchWords = "";
            }
        }

        return substr($searchTerm, 0, -6);
    }

    protected function inventoryQuery(): Builder
    {
        $this->ensureInventoryTableExists();

        $searchTerm = $this->buildSearchTerm();

        return Product::select('products.*')
            ->when(strlen($searchTerm) > 0, function ($query) use ($searchTerm) {
                $query->whereRaw($searchTerm);
            })
            ->join('table_temp_a', 'table_temp_a.id', '=', 'products.id')
            ->orderBy('products.id', 'asc');
    }

    protected function normalizeCurrentPage(): void
    {
        $total = (clone $this->inventoryQuery())->count('products.id');
        $lastPage = max((int) ceil($total / 10), 1);

        if ((int) $this->page > $lastPage) {
            $this->setPage($lastPage);
        }
    }

    #[On('echo:inventory-adjuster,InventoryAdjusterUpdated')]
    public function syncInventoryAdjuster(): void
    {
        $this->normalizeCurrentPage();
    }

    public function removeItem($id) {
        $inventory = DB::table('table_temp_a')->where('id', $id);

        if ($inventory->exists()) {
            $inventory->delete();
            InventoryAdjusterUpdated::dispatch('removed', (int) $id);

            $this->search = "";

            $this->resetPage();
            $this->dispatch('input-set-focus');
        } else {
            session()->flash('error', "Item {$id} was not found in the current inventory list.");
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
        InventoryAdjusterUpdated::dispatch('refreshed');
    }

    public function render() {
        $products = $this->inventoryQuery()->paginate(perPage: 10);

        if ($products->isEmpty() && $products->total() > 0 && $products->currentPage() > $products->lastPage()) {
            $this->setPage($products->lastPage());
            $products = $this->inventoryQuery()->paginate(perPage: 10);
        }

        if ($products->isEmpty() && filled($this->search)) {
            session()->flash('error', "Item was not found in the current inventory list.");
            $this->dispatch('input-set-focus');
        }

        return view('livewire.inventory-adjuster',["products"=>$products, 'pageName' => "Inventory Adjuster"])
            ->layoutData(['pageName' => 'Inventory Adjuster'])
            ->title("Inventory Adjuster");;
    }
}
