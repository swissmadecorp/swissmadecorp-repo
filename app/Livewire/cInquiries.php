<?php

namespace App\Livewire;

use Livewire\Component;
use App\SearchCriteriaTrait;
use App\Models\Inquiry;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class cInquiries extends Component
{
    use WithPagination,  SearchCriteriaTrait;

    public $page = 1;
    public $search = '';

    public function invokeInquiryId($id) {
        $this->dispatch('edit-inquiry', ['id' => $id]);
    }

    public function render()
    {
        $columns = ['product_id','contact_name','company_name','email','phone'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        $inquiries = Inquiry::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })
        ->orderBy('created_at', 'desc');

        if ($inquiries->exists())
            $inquiries = $inquiries->paginate(perPage: 10);
        else {
            $inquiries = $inquiries->paginate(perPage: 10);
            if ($this->search)
                session()->flash('error', "Item was not found in the current inventory list.");
        }

        return view('livewire.inquiries',["inquiries"=>$inquiries])
            ->layoutData(['pageName' => 'Inquiries'])
            ->title("Inquiries");
    }
}
