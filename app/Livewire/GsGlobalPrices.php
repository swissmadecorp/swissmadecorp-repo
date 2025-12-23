<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Models\GlobalPrices;
use Livewire\Attributes\Validate;

class GsGlobalPrices extends Component
{
    public $platformId=0;

    #[Validate('required')]
    public $amount;

    #[Computed()]
    public function platforms() {
        return GlobalPrices::all();
        
    }

    public function getAmount() {
        $amount = $this->platforms->find($this->platformId);
        if ($amount)
            $this->amount = $amount->margin;
        else $this->amount = "";
    }

    public function setGlobalPrices() {
        if (! Auth()->user()->hasRole('administrator')) { 
            abort(403);
        }

        $validated = $this->validate();
        $products = Product::where('p_qty','>',0)
            ->where('p_newprice','>','0')
            ->get();

        $globalPrice = GlobalPrices::find($this->platformId);
        $percent = $this->amount; //$globalPrice->margin;
        
        if ($globalPrice->platform!="eBay") {
            
            foreach ($products as $product) {
                $amount = $product->p_newprice;
                $rolexBoxMargin = 0;

                if ($product->category_id==1 && $product->p_condition==2) $rolexBoxMargin=100;

                $price3p = $amount+$rolexBoxMargin+(($amount+$rolexBoxMargin) * ($percent/100));
                $price3p = number_format($price3p,0,'','');
                $product->update([
                    'p_price3P' => $price3p
                ]);
            }
        
            $this->reset('platformId','amount');
            $this->dispatch('display-message','All prices have been updated');
        } else {
            $this->dispatch('display-message','Updated! New margin will take effect next time items are listed.');
            $this->reset('platformId','amount');
        }
    }

    // #[Layout('components.layouts.admin')] 
    public function render()
    {
        
        return view('livewire.global-prices');
    }
}
