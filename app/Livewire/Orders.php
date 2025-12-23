<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estimate;
use App\Mail\GMailer;
use Livewire\Attributes\On;
use Livewire\Attributes\Js;
use Livewire\Attributes\Url;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Jantinnerezo\LivewireAlert\Enums\Position;
use App\View\Components\Layouts;

// Don't really have to use #[Layout('components.layouts.app')] 
// as /config/livewire.php already has it there.
//#[Layout('components.layouts.app')]
class Orders extends Component
{
    use WithPagination;

    public $page = 1;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'status' => ['except' => true]
    ];

    #[Url(keep: true)]
    public $search = "";

    public $order = null;
    public $status = 0;
    public $sql = '';
    
    public function doSort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection == "ASC" ? 'DESC' : 'ASC';
            return;
        }
        $this->sortBy = $column;
        $this->sortDirection = "DESC";
    }

    public function updatingSearch()
    { 
        $this->resetPage();
    }

    public function setStatus($status) {
        $this->status = $status;
        $this->resetPage();
    }

    public function loadOrder($id) {
        $this->dispatch('load-order',$id);
    }

    public function getOrder($id = null) {
        //$order = Estimate::find($id);
        //$this->order = $order;
        $this->dispatch('current-order',$id);
    }

    #[On('display-message')]
    public function displayMessage($msg) {
        
        if (is_array($msg)) {            
            if (isset($msg['msg'])) 
                LivewireAlert::title($msg['msg'])->success()->position(Position::TopEnd)->toast()->show();

            if (!isset($msg['hide'])) $msg['hide'] = 1;

            $this->dispatch('hide-slider',$msg['hide']);
        } elseif ($msg)
            LivewireAlert::title($msg)->success()->position(Position::TopEnd)->toast()->show();
    }
    
    public function print($id) {
        $estimate=Estimate::find($id);
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object
        $printOrder->print($estimate); // Print newly create proforma.
    }

    public function sendEmail($ids) {
        $ids=explode(',',$ids);
        $filename=array();

        $orders=Estimate::wherein('id',$ids)->get();
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        foreach ($orders as $order) {
            $ret = $printOrder->_print($order,null,'emailmultiple'); // Print newly created proforma/order.
            //$arr=$this->print($id,'emailmultiple');

            $order=$ret[1];
            $filename[] = $ret[0];
            
            if ($order->email=='') {
                request()->session()->flash('error', "Email was not specified. Please enter email and  try again!");
                return;
            }

            $order->emailed=1;
            $order->update();    
        }
        
        $purchasedFrom = $order->purchased_from;
        if ($purchasedFrom==2) {
            $email = 'signtimeny@gmail.com';
            $subject = 'Signature Time';
        } else {
            $email = 'info@swissmadecorp.com';
            $subject = 'Swiss Made Corp.';
        }

        if ($order->b_company != "Website") {
            $company = $order->b_company;
        } else {
            $company = $order->s_company;
        }

        $data = array(
            'template' => 'emails.invoice',
            'to' =>$order->email,
            'company' => $company,
            'order_id' => $order->id,
            'filename'=>$filename,
            'purchasedFrom' => $ret[2],
            'subject' => $subject,
            'from' => $email,
        );

        $gmailer = new GMailer($data);
        $gmailer->send();

        request()->session()->flash('message', "Successfully emailed invoice!");
    }

    public function deleteInvoice($id)
    {
        $order = Estimate::find($id);

        $order->products()->detach();
        $order->customers()->detach();

        $order->delete();

        request()->session()->flash('message', "Successfully deleted invoice!");
    }

    public function render()
    {
        // View::share('title', 'Orders');

        $words = explode(' ', trim($this->search));
        $searchTerm = "";
        $searchWords = "";
        $totalCost = 0;

        $columns = ['id','b_company','b_lastname','b_firstname','method'];
        
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
        }
       
        $searchTerm = substr($searchTerm,0,-6);
       
        $orders = Estimate::with('customers')
            ->when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
                $query->whereRaw($searchTerm);
            })
            ->where('status',$this->status)
            ->orderBy('id','desc');
        
        $totalCost = $orders->sum('total');
        $orders = $orders->paginate(perPage: 10);

        return view('livewire.orders',["orders"=>$orders, 'totalcost' => $totalCost])
            ->layoutData(['pageName' => 'Orders'])
            ->title("Orders");

    }
}
