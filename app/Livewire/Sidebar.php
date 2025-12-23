<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class Sidebar extends Component
{
    public $catId = 0;
    #[Url(keep: true)]
    public ?string $brand = '';
    // public $sizes = '';
    // public $condition = '';

    // #[Url(keep: true)]
    public ?string $model = '';
    // public $selectedCategory = '';
    public $categories;
    // public $gender = '';

    // public $listeners = ['setCategory'];

    protected $queryString = [
        'catId' => ['except' => 0],
        'brand' => ['except' => ''],
        'model' => ['except' => ''],
        // 'condition' => ['except' => ''],
        // 'sizes' => ['except' => ''],
        // 'gender' => ['except' => ''],
    ];

    private function models() {
        
        if ($this->brand && !$this->catId) {
            $category = Category::where('category_name',$this->brand)->first();
            $this->catId = $category->id;
        } 
        
        if ($this->catId) {
            
            $models = Product::select('p_model','categories.category_name','category_id')
            ->join('categories','categories.id','=','products.category_id')
            ->where('category_id',$this->catId)
            ->whereNotIn('p_status',[4,7])
            ->where('p_qty','>',0)
            ->groupBy('p_model')
            ->orderBy('p_model','asc')
            ->get();

            $this->brand = $models->first()->category_name;
            return $models;
        }

        return null;
    }

    public function setModel($model) {
        $this->model = $model;
        $this->dispatch('watch-dispatcher',$this->catId,'model',$model);
    }

    public function mount() {
        $this->categories = $this->categories();
    }
    
    public function setCasesize($value) {
        $this->casesize = $value;
        $this->dispatch('watch-dispatcher',$value,'casesize');
    }

    public function setCategory($catId) {
        $this->catId = $catId;
        $this->reset('model');
        $this->dispatch('watch-dispatcher',$catId,'category');
    }

    #[Computed]
    public function casesizes() {
        $casesizes = Product::select('p_casesize')
                ->where('p_qty','>',0)
                ->orderBy('p_casesize','asc')
                ->groupBy('p_casesize')->get();
        
        return $casesizes;
    }

    public function categories() {
        $categories = Category::whereHas('products',function($query) {
            $query->where('p_qty','>',0);
            $query->whereIn('p_status',array(0,1,2,5));
        })->orderBy('category_name')->get();

        return $categories;
    }

    public function setCondition($value) {
        $this->condition = $value;
        $this->dispatch('watch-dispatcher',$value,'condition');
    }

    public function setGender($value) {
        $this->gender = $value;
        $this->dispatch('watch-dispatcher',$value,'gender');
    }

    public function render() {
        $models = $this->models(); 
        
        return view('livewire.sidebar',['models' => $models]);
    }
}