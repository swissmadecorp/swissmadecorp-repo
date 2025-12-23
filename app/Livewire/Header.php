<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Cart;

class Header extends Component
{
    
    #[Url(keep: true)]
    public $search = "";

    public $brand = '';
    public $sizes = '';
    public $condition = '';
    public $casesize = '';
    public $model = null;
    public $gender = '';
    public $countCart = 0;

    protected $queryString = [
        'search' => ['except' => ''], 
        'brand' => ['except' => ''],
        'model' => ['except' => ''],
        'condition' => ['except' => ''],
        'sizes' => ['except' => ''],
        'gender' => ['except' => ''],
        'casesize' => ['except' => ''],
    ];

    #[On('refresh-cart-count')]
    public function cartCountRefresh() {
        $this->countCart = Cart::count();
    }

    public function updatedSearch()
    { 
        $this->reset('brand','model','condition','sizes','gender','casesize');
        $this->dispatch('updatingSearch', $this->search);
        return redirect()->route('watch.products', ['search' => $this->search]);

    }

    public function render()
    {
        $this->countCart = Cart::count();
        return view('livewire.header');
    }
}
