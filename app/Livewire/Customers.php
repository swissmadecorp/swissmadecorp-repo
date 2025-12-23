<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\SearchCriteriaTrait;
use Livewire\Attributes\Url;
// use Jantinnerezo\LivewireAlert\LivewireAlert;

class Customers extends Component
{
    use WithPagination, SearchCriteriaTrait;

    public $page = 1;
    public $customerSelections = [];

    #[Url(keep: true)]
    public $search = "";

    public $customerId = 0;

    protected $queryString = [
        'page',
        'search'
    ];

    public function deleteCustomer($id) {
        $customer = Customer::find($id);
        $customer->delete();
        // $this->alert('success', "Customer #$id has been deleted successfully.");
    }

    public function invokeCustomerId($id,$company) {
        $this->dispatch('set-customer',$id,$company);
    }

    public function createNewCustomer() {
        $this->dispatch('create-new');
    }

    public function transferCustomer($sourceId, $targetId)
    {
        // Don't do anything if they are the same customer
        if ($sourceId === $targetId) {
            return;
        }

        $sourceCustomer = Customer::find($sourceId);
        $targetCustomer = Customer::find($targetId);


        $this->dispatch('confirm-transfer', [
            'source' => [
                'id' => $sourceCustomer->id,
                'company' => $sourceCustomer->company
            ],
            'target' => [
                'id' => $targetCustomer->id,
                'company' => $targetCustomer->company
            ]
        ]);
    }

    #[On('confirm-and-transfer')]
    public function confirmAndTransfer($sourceId, $targetId)
    {
        // Use a database transaction to ensure data integrity
        \DB::transaction(function () use ($sourceId, $targetId) {
            // $sourceCustomer = Customer::findOrFail($sourceId);
            // $targetCustomer = Customer::findOrFail($targetId);
// dd($sourceId, $targetId);
            // Update related tables
            // Example: Transfer all orders from source to target customer
            // DB::table('orders')
            //   ->where('customer_id', $sourceCustomer->id)
            //   ->update(['customer_id' => $targetCustomer->id]);

            // Now, delete the source customer
            // $sourceCustomer->delete();

            // Re-fetch the customers to update the view
            // $this->customers = Customer::all();

            \DB::update("update customer_order set customer_id = $targetId where customer_id = $sourceId");

            $this->dispatch('transfer-success');
        });
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $columns = ['company','firstname','lastname'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        $customers = Customer::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })->paginate(perPage: 16);

        return view('livewire.customers',['customers' => $customers,'pageName' => "Customers"]);
    }
}
