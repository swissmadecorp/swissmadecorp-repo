<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class InquiryItem extends Component
{
    #[On('edit-inquiry')]
    public function invokeInquiryId($id) {
        $inquiry = Inquiry::find($id);
        dd($inquiry);
        if ($inquiry) {
            $product = Product::find($inquiry->product_id);
            if ($product) {
                $this->dispatch('edit-product', ['productId' => $product->id]);
            } else {
                session()->flash('error', "Product with ID {$inquiry->product_id} was not found.");
            }
        } else {
            session()->flash('error', "Inquiry with ID {$id} was not found.");
        }
    }

    public function render()
    {
        return view('livewire.inquiry-item');
    }
}
