<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Inquiry;
use App\Models\Product;

class InquiryItem extends Component
{
    public $inquiryId;

    #[On('edit-inquiry')]
    public function invokeInquiryId($inquiry) {
        $this->inquiryId = $inquiry['id'];
    }

    public function render()
    {
        if (!$this->inquiryId) {
            return view('livewire.inquiry-item', ['inquiry' => null]);
        }

        $inquiry = Inquiry::find($this->inquiryId);
        $product = Product::find($inquiry->product_id);

        return view('livewire.inquiry-item', ['inquiry' => $inquiry, 'product' => $product])
            ->layoutData(['pageName' => 'Inquiries'])
            ->title("Inquiries");
    }
}
