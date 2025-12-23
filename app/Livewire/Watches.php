<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\DiscountRule;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;

class Watches extends Component
{
    use WithPagination;

    public $models = null;
    public $catId = 0;

    public $isNewArrivalPage;

    #[Url(history: true)]
    public ?string $brand = '';

    public $sizes = '';
    public $condition = '';
    public $casesize = '';

    #[Url(history: true)]
    public ?string $model = null;
    public $gender = '';

    public $discount;
    public $categoryimageHTML;
    public $breadcrumbs = [];

    protected $queryString = [
        'search' => ['except' => ''],

        'condition' => ['except' => ''],
        'sizes' => ['except' => ''],
        'gender' => ['except' => ''],
        'casesize' => ['except' => ''],
    ];

    #[Url(keep: true)]
    public $search = "";

    public $pageName = '';

    private function discountRule() {
        $now = (date('Y-m-d',strtotime(now())));
        $discountRule = DiscountRule::whereIn('action',[4,5])
            ->where('start_date','<=',$now)
            ->where('end_date','>=',$now)
            ->where('is_active', '1')
            ->first();

        return $discountRule;
    }

    private function getCondition($condition) {

        foreach (Conditions() as $key => $_condition) {
            if (strtolower($_condition) == $condition) {
                return $key;
            }
        }

        return '';
    }

    public function goToProductDetails($slug) {
        return redirect()->route('product.details', ['slug' => $slug]);
    }

    public function updatingSearch() {
        $this->resetPage();
    }

    public function getProducts() {

        $this->discount = $this->discountRule();
        $categoryimage=null;

        //$criteria = $request['filter'];
        // if (!$this->catId) {
        //     $category = Category::where('category_name',$this->brand)->first();
        //     if ($category) {
        //         $this->catId = $category->id;
        //         dd($this->catId);
        //     }
        // }

        $catId = $this->catId;
        $condition_key = 0;
        $gender = $this->gender;
        $model = $this->model;
        $casesize = $this->casesize;

        if ($this->condition) {
            $condition_key = $this->getCondition($this->condition);
        }

        if ($this->brand && !$catId) {
            $category = Category::where('category_name',$this->brand)->first();
            $catId = $category->id;
        }

        $words = explode(' ', $this->search);
        $searchTerm = "";
        $searchWords = "";
        //\Log::debug($catId. ' ' . $model . ' ' . $this->brand);
        $columns = ['keyword_build','id'];

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

            $this->reset('catId','gender','model','condition','brand','casesize','categoryimageHTML');
            $catId = 0;
        }
        // dd($catId. ' ' . ' ' . $gender . ' ' . $model  . ' ' . $casesize);

        $searchTerm = substr($searchTerm,0,-6);

        if ($this->isNewArrivalPage == "new-arrival") {
            $now = (date('Y-m-d 23:59:59',strtotime(now())));
            $then = (date('Y-m-d 12:00:00',strtotime("-4 days")));

            $products = Product::whereNotIn('p_status',[4,7,8,9])
                ->where('p_qty','>',0)
                ->where('created_at','<=',$now)
                ->where('created_at','>=',$then)
                ->orderBy('updated_at','desc')
                ->latest()->paginate(20);

        } else {
            $products = Product::when($condition_key, function($query,$condition_key) {
                if ($condition_key==1)
                    $query->whereIn('p_condition',[1,2]);
                else
                    $query->where('p_condition',$condition_key);
                })
                ->when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                    $query->whereRaw($searchTerm);
                })
                ->when($this->gender, function($query,$gender) {
                    $query->where('p_gender',$gender);
                })
                ->when($catId, function($query, $catId) {
                    $query->where('category_id',$catId);
                })
                ->when($this->casesize, function($query, $casesize) {
                    $query->where('p_casesize',$casesize);
                })
                ->when($model, function($query, $model) {
                    $query->where('p_model',$model);
                })
            ->whereNotIn('p_status',[3,4,7,8,9])
            ->where('p_qty','>',0)
            ->orderBy('updated_at','desc')
            ->latest()->paginate(20);
            //\Log::debug(print_r($products,true));
            if ($catId) {
                $this->models = Product::select('p_model','categories.category_name','category_id')
                    ->join('categories','categories.id','=','products.category_id')
                    ->where('category_id',$catId)
                    ->where('p_qty','>',0)
                    ->groupBy('p_model')
                    ->orderBy('p_model','asc')
                    ->get();

                $categoryimage=Category::find($catId);
                $this->categoryimageHTML = '';

                // if ($products->count())
                $this->brand = $categoryimage->category_name; // $products->first()->categories->category_name;
                // \Log::debug(print_r($this->models,true));
                if ($categoryimage && $categoryimage->image_name) {
                    ob_start();
                    ?>

                        <div class="gap-8 items-center md:flex p-2">
                            <div class="flex items-center justify-center">
                                <img class="w-96" src="/images/categories/<?= $categoryimage->image_name ?>" />
                            </div>
                            <div class="w-full">
                                <h2 class="text-2xl"><?= strtoupper($categoryimage->category_title) ?></h2>
                                <p class="pt-2 pb-2"><?= $products->count() ?> MATCHES FOUND</p>
                                <div class="description"><?= $categoryimage->category_description ?></div>
                            </div>
                        </div>


                    <?php
                    $this->categoryimageHTML = ob_get_clean();
                }

                // $products = $products->latest()->paginate(20);
            }
        }

        return $products;
    }

    private function setBreadcrumbs() {
        $breadcrumbs = array_filter(['Category'=>$this->brand,'Model'=>$this->model,'Condition'=>$this->condition,'Gender'=>$this->gender,'Casesize' => $this->casesize]);
        $this->breadcrumbs = $breadcrumbs; //array_map('ucfirst', array_map('strtolower', $breadcrumbs));

    }

    public function clearCondition() {
        $this->reset('gender','casesize');

        $this->setBreadcrumbs();
    }

    public function clearGender() {
        $this->reset('casesize');

        $this->setBreadcrumbs();
    }

    public function clearCasesize() {
        // $this->reset('casesize');

        $this->setBreadcrumbs();
    }

    public function clearModel() {
        $this->reset('condition','gender','casesize');

        $this->setBreadcrumbs();
    }

    public function clearCategory() {

        $this->reset('model','condition','gender','casesize');
        $this->setBreadcrumbs();
    }

    #[On('breadcrumbSelected')]
    public function breadcrumbSelected($breadcrumbs) {
        $this->breadcrumbs = $breadcrumbs;
    }

    public function testDispatch()
    {
        $this->dispatch('breadcrumbSelected', breadcrumbs: $this->breadcrumbs);
    }

    #[On('watch-dispatcher')]
    public function watchDispatcher($value,$name,$model="") {
        $this->reset('search');
        // \Log::debug($value.' '.$model.' '.$name);
        if ($name=="casesize") {
            $this->casesize = $value;
        } elseif ($name=="category") {
            $this->catId = $value;
            $this->reset('model');
        } elseif ($name == "condition") {
            $this->condition = $value;
        } elseif ($name == "model") {
            $this->catId = $value;

            $this->model = $model;
        } elseif ($name == "gender") {
            $this->gender = $value;
        }

        $this->resetPage();
    }

    public function getListeners() {
        return ['syncFromUrl' => 'syncQueryParams'];
    }

    public function mount() {
        // $this->setBreadcrumbs();
    }

    #[On('updatingSearch')]
    public function headerSearch($search) {
        $this->search = $search;
    }

    public function render() {
        //\Log::debug(request()->query('brand') . ' '. request()->query('model'));
        // if (request()->query('brand') || request()->query('model')) {
        //     $this->model=request()->query('model');
        //     $this->brand = request()->query('brand');


        // }

        $products=$this->getProducts();
        $this->setBreadcrumbs();

        return view('livewire.watches',['products' => $products]);
    }
}