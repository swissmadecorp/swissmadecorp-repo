<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Models\Product;
use Livewire\Attributes\Validate;
use App\Models\EbayListing;
use App\Jobs\eBayEndItem;
use Livewire\Component;
use App\Models\Repair;

class CreateRepair extends Component
{
    #[Validate([
       'fields.assignTo' => 'required|min:5',
       'fields.jobs' => 'required|min:5',
    ], message: [
        'required' => 'The :attribute is a required field.',
        'fields.required' => 'The :attribute is a required field.',
    ], attribute: [
        'fields.jobs' => 'jobs',
        'fields.notes' => 'notes',
        'fields.assignTo' => 'watchmaker',
    ])]
    public $fields=[];

    public $productId = 0;

    #[On('current-productid')]
    public function ProductId($id) {
        $this->productId = $id;
    }

    public function clearRepairDialogBox() {
        $this->resetValidation();
        $this->reset();
        $this->dispatch('display-message', "");
    }

    public function saveProductRepair() {

        $validated=$this->validate();
        $id = $this->productId;
        if (!$id)
            $this->dispatch('display-message', "No product specified.");

        $product = Product::find($id);
        if (!$product) {
            $this->dispatch('display-message', "Product not found.");
            return;
        }

        eBayEndItem::dispatch([$id]);

        if (isset($this->fields['completed']))
            if ($this->fields['completed']==false)
                $completed = false;
            else $completed = true;
        else $completed = false;

        $this->resetValidation();
        if (count($product->repair)) {
            if ($product->repair[0]->status == 0) {
                $repair = $product->repair->first();
                $repair->update([
                    'assigned_to' => $this->fields['assignTo'],
                    'comments' => $this->fields['notes'],
                    'status' => $completed
                ]);

                $repair->pivot->job = serialize($this->fields['jobs']);

                if (isset($this->fields['cost'])) {
                    $repair->pivot->cost = $this->fields['cost'];
                    $product->p_price += $this->fields['cost'];
                }
                if ($completed) {
                    $product->p_status = 0;
                    $this->postToEbay($product);
                } else
                    $product->p_status = 9;

                $repair->pivot->update();
                $product->update();

                $this->dispatch('display-message', ['msg'=>"Product for repair was updated.",'id'=>$id,'location'=>'repair']);
            }
        } else {
            $notes = "";
            if (isset($this->fields['notes'])) {
                $notes = $this->fields['notes'];
            }
            $repairArray = array(
                'assigned_to' => $this->fields['assignTo'],
                'comments' => $notes,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            );

            $repair = Repair::create($repairArray);

            $repairProduct=array(
                'product_id' => $id,
                'job' => serialize($this->fields['jobs'])
            );
            $product->repair()->attach($repair->id, $repairProduct);
            $product->p_status = 9;
            $product->update();

            $this->dispatch('display-message', "Product was assined to: ".$this->fields['assignTo']);
        }
    }

    public function postToEbay($product) {
        if (is_numeric($product)) {
            $product = Product::find($product);
            request()->session()->flash('message', "Product submitted to eBay.");
        }

        if ($product->categories->category_name != "Rolex" && $product->p_newprice > 100
            && count($product->images)> 0 && $product->p_status == 0) {
                $listing = EbayListing::where('product_id',$product->id)->first();

                if (!$listing)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]])->delay(now()->addMinutes(2));
                elseif ($listing->listitem == null)
                    AutomateEbayPost::dispatch(["ids"=>[$product->id]])->delay(now()->addMinutes(2));
        }
    }

    public function render()
    {
        if ($this->productId) {
            $product = Product::find($this->productId);
            if (count($product->repair)) {
                $repair = $product->repair->first();
                $this->fields['assignTo'] = $repair->assigned_to;

                if ($repair->pivot->job)
                    $this->fields['jobs'] = unserialize($repair->pivot->job);

                if ($repair->pivot->cost)
                    $this->fields['cost'] = $repair->pivot->cost;

                $this->fields['notes'] = $repair->comments;
            }
        }
        return view('livewire.create-repair');
    }
}
