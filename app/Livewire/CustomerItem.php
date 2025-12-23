<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use App\SearchCriteriaTrait;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Customer;
use App\Models\Product;
use Livewire\Component;
use App\Models\Country;
use App\Models\Order;
use App\Models\State;

class CustomerItem extends Component
{
    use WithPagination, SearchCriteriaTrait;

    public $customerId;
    public $customer;
    public $company;
    public $selectedBCountry;
    public $selectedBState;
    public $customerGroup = [];
    public $customerOrder;
    public int $customerGroupId = 0;
    
    public $page = 1;
    public $search = "";
    public $searchSupplier = "";

    #[Computed]
    public function countries() {
        return Country::All();
    }

    #[Computed]
    public function billingStates() {
        return State::where('country_id',$this->selectedBCountry)->get();
    }

    public function clearFields() {   
        $this->resetValidation();
        $this->reset();
        
        $this->customerGroup = ['Dealer','Customer'];

        // Clear all items in the collection
        $this->selectedBCountry = 0;
        $this->selectedBState = 0;

    }

    protected function rules() {
        return [
            'customer.company' => ['required'],
        ];
    }

    protected $messages = [
        'customer.company.required' => 'This field is required.',
    ];

    public function saveCustomer() {
        $validatedData = $this->validate(
            $this->rules(),
            $this->messages
        );

        $data = $this->customer; // Get the full data
        unset($data['orders']); // Remove the 'orders' array
        $this->customerOrder->update($data);

        $this->dispatch('display-message',['msg'=>'Product Saved.','id'=>$this->customerOrder->id]);
    }

    #[On('create-new')]
    public function createNew() { 
        $this->selectedBCountry = 231;
        $this->selectedBState = 3956;
    }

    #[On('set-customer')]
    public function setCustomerId($id,$company) {
        $this->customerId = $id;
        $this->company = $company;
        $this->customerOrder = Customer::find($this->customerId);
        $this->customer = $this->customerOrder->toArray();
    }

    
    public function render() {
        
        $previousOrders = null;
        $supplierProducts = null;
        $id = $this->customerId;

        if ($this->customerId) {
            $columnsOrder = ['id','b_company','b_lastname','b_firstname', 'b_company'];
            $searchTermOrder = $this->generateSearchQuery($this->search, $columnsOrder);

            $previousOrders = Order::when(strlen($searchTermOrder) > 0, function ($query) use ($searchTermOrder) {
                $query->whereRaw($searchTermOrder);
            })->whereHas('customers',function($query) use($id) {
                $query->where('id', $id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'orders');// Adjust pagination size as needed

            $columnsSupplier = ['id','title','created_at','p_price'];
            $searchTermSupplier = $this->generateSearchQuery($this->searchSupplier, $columnsSupplier);
    
            $supplierProducts = Product::when(strlen($searchTermSupplier) > 0, function ($query) use ($searchTermSupplier) {
                $query->whereRaw($searchTermSupplier);
            })->where('supplier',$this->company)->paginate(10, ['*'], 'suppliers');

            if (!$this->customer['country'])
                $this->selectedBCountry = 231;
            else $this->selectedBCountry = $this->customer['country'];

            if (!$this->customer['state'])
                $this->selectedBState = 3956;
            else $this->selectedBState = $this->customer['state'];
        }

        return view('livewire.customer-item', ['previousOrders' => $previousOrders, 'supplierProducts' => $supplierProducts]);
    }
}
