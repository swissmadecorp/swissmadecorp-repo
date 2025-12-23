<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Mail\GMailer;
use Livewire\Attributes\On;
use Livewire\Attributes\Js;
use Livewire\Attributes\Url;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use App\Events\MessagesEvent;
use App\Models\Payment;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\SearchCriteriaTrait;

class Invoices extends Component
{
    use WithPagination, SearchCriteriaTrait;

    public $page = 1;
    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'status' => ['except' => true]
    ];

    #[Url(keep: true)]
    public $search = "";

    public $invoiceSelections = [];
    public $messages=[];
    public int $loggedInUser = 0;
    public $whatsapptoken = "";
    public $whatsAppNewToken = "";
    public $currentInvoiceId;
    public $textPerson;
    public $order = null;
    public $status = 0;
    public $trackingNumber = null;
    public $sql = '';

    public function toggleSelection($id) {
        if (in_array($id, $this->invoiceSelections)) {
            $this->invoiceSelections = array_diff($this->invoiceSelections, [$id]);
        } else {
            $this->invoiceSelections[] = $id;
        }
    }

    public function printLabel() {
        $this->dispatch('open-new-tab', url: '/admin/printlabel?tracking_number='.$this->trackingNumber);
    }

     public function updatedWhatsapptoken() {
        $this->generateFacebookToken($this->whatsapptoken);
    }

    public function closeWhatsapp() {
        $this->reset('whatsAppNewToken','whatsapptoken');
    }

    private function generateFacebookToken($token) {
        // $token = config('chatgpt.FACEBOOK_API');

        // Your App ID, App Secret, and short-lived token
        $app_id = '1279877275969102';
        $app_secret = config('chatgpt.FACEBOOK_SECRET');
        $short_lived_token = $token;  // The token you got initially

        // Construct the API URL
        $url = 'https://graph.facebook.com/v21.0/oauth/access_token?' . http_build_query([
            'grant_type' => 'fb_exchange_token',
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'fb_exchange_token' => $short_lived_token
        ]);

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            echo 'cURL Error: ' . curl_error($ch);
            exit;
        }

        // Close the cURL session
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        // Check if the long-lived token is in the response
        if (isset($data['access_token'])) {
            // echo 'Long-Lived Access Token: ' . $data['access_token'] . "\n";
            // echo 'Expires in: ' . $data['expires_in'] . ' seconds' . "\n";
            $this->whatsAppNewToken = $data['access_token'];
        } else {
            $this->whatsAppNewToken = $data['error']['message'];
            // echo 'Error: ' . $data['error']['message'] . "\n";
        }

    }

    public function doSort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection == "ASC" ? 'DESC' : 'ASC';
            return;
        }
        $this->sortBy = $column;
        $this->sortDirection = "DESC";
    }

    public function setCurrentInvoiceId($id) {
        $this->currentInvoiceId = $id;

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setStatus($status) {
        $this->status = $status;
        $this->resetPage();
    }

    public function loadInvoice($id) {
        $this->dispatch('load-invoice',$id);
    }

    public function getOrder($id = null) {
        //$order = Order::find($id);
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

    private function sendWhatsApp($filename, $handshake) {
        $token = config('chatgpt.FACEBOOK_API');
        $phone_number_id = '580826665103968';
        $phoneTo = "+" . $this->textPerson;

        if ($handshake==0) {

            $headers = [
                'Authorization: Bearer ' . $token,
            ];

            $filePath = public_path()."/uploads/$filename"; // Path to your local file

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/media");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $data = [
                'messaging_product' => 'whatsapp', // Include the messaging_product parameter
                'file' => new \CURLFile($filePath,'application/pdf',$filename),
                'type' => 'application/pdf', // MIME type of the file
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $mediaId = 0;

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                $mediaId = $responseData['id']; // Retrieve the media_id
                // echo "Media uploaded successfully. Media ID: " . $mediaId;
            } else {
                $error="Failed to upload media. Response: " . $response;
                $this->dispatch('itemMsg', $error);
            }

            $headers = [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];

            $post = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $phoneTo,
                "type" => "document",
                "document" => [
                    "id" => $mediaId, /* Only if using uploaded media */
                    "caption" => $filename,
                    "filename" => $filename
                ]
            ];

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Send JSON data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                // Handle CURL error
                $error = 'Error: ' . curl_error($ch);
                $this->dispatch('itemMsg', $error);
            } else {
                $this->dispatch('itemMsg', 'Message has been sent!');
            }
            curl_close($ch);
            unlink(base_path()."/public/uploads/$filename"); // delete file after sending a file
        } else {
            $headers = [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ];

            $post = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $phoneTo,
                "type" => "template",
                "template" => [
                    "name" => 'invitation_template', /* Only if using uploaded media */
                    "language" => ["code" => 'en_US'],
                ]
            ];

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Send JSON data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                // Handle CURL error
                $error = 'Error: ' . curl_error($ch);
                $this->dispatch('itemMsg', $error);
            } else {
                $this->dispatch('itemMsg', 'A template has been executed and sent!');
            }
            curl_close($ch);
        }

        $this->textPerson = 0;
    }

    public function sendText($handshake) {
        // $ids=explode(',',$ids);
        // $filename=array();
        // dd($this->textPerson);
        $id = $this->currentInvoiceId;
        $order=Order::find($id);
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        $ret = $printOrder->_print($order,null,'emailmultiple'); // Print newly created proforma/order.
        // $arr=$this->print($id,'emailmultiple');

        // dd($ret);
        $this->sendWhatsApp($ret[0],$handshake);
    }

    public function sendEmail($ids) {
        $ids=explode(',',$ids);
        $filename=array();

        $orders=Order::wherein('id',$ids)->get();
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        foreach ($orders as $order) {
            $ret = $printOrder->_print($order,null,'emailmultiple'); // Print newly created proforma/order.
            //$arr=$this->print($id,'emailmultiple');

            $order=$ret[1];
            $filename[] = $ret[0];

            if ($order->email=='') {
                LivewireAlert::title("Email was not specified. Please enter email and  try again!")->error()->toast()->show();
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

    public function createNew() {
        $this->dispatch('create-new');
    }

    public function removeInvoice($id)
    {
        // dd('removeInvoice');
        $order = Order::find($id);
        // $product_ids = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id != 74) {
                if ($order->method != "On Memo") {
                    $product->p_qty += $product->pivot->qty;
                    $product->p_status = 0;
                    $product->update();
                }
            }
            // $product->delete(); // Soft delete products
        }

        // $order->products()->detach();
        // $order->customers()->detach();

        // Soft delete payments
        // $payments = Payment::where('order_id', $id)->get();
        // foreach ($payments as $payment) {
        //     $payment->delete();
        // }

        $order->delete(); // Soft delete order

        request()->session()->flash('message', "Successfully deleted invoice!");
    }

    public function deleteInvoice($id)
    {

        $order = Order::find($id);
        // $product_ids = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id != 74) {
                if ($order->method != "On Memo") {
                    if ($product->p_qty == 0)
                        $product->p_qty += $product->pivot->qty;

                    $product->p_status = 0;
                    $product->update();
                }
            }
        }

        // $order->products()->detach();
        // $order->customers()->detach();

        // Soft delete payments
        // $payments = Payment::where('order_id', $id)->get();
        // foreach ($payments as $payment) {
        //     $payment->delete();
        // }

        $order->delete(); // Soft delete order

        request()->session()->flash('message', "Successfully deleted invoice!");
    }

    public function returnAllProducts($id) {
        $order = Order::find($id);
        if (isset($order->payments)) {
            if ($order->payments->count()) {
                $payment = $order->payments->sum('amount');

                $this->dispatch('itemMsg', 'A payment has already been applied in the amount of $' .number_format($payment,2) . '. If you want to modify the quantity or the amount,  you must delete the payment first and then try again.');
                return false;
            }
        }

        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo")
                    $product->p_qty = $product->p_qty + $product->pivot->qty;

                $product->p_status = 0;
                $product->pivot->qty = 0;
                $product->pivot->update();
                $product->update();
            }
        }

        $order->subtotal = 0;
        $order->total = 0;
        $order->status = 2;
        $order->update();

    }

    public function render()
    {
        $totalCost = 0;
        $status = $this->status;

        if ($this->search == "*") {
            $choices = ['Chrono24','Website','eBay'];
            $orderQuery = Order::with(['customers', 'payments', 'products'])
                ->whereIn('b_company', $choices )
                ->where('status', $status)
                ->orderBy('orders.id', 'desc');
        } else {

            $columns = ['orders.id','b_company','b_lastname','b_firstname', 's_company','method','product_name','product_id','serial'];
            $searchTerm = $this->generateSearchQuery($this->search, $columns);

            $orderQuery = Order::select('orders.*')
                ->with(['customers', 'payments', 'products'])
                ->join('order_product', 'order_product.order_id', '=', 'orders.id')
                ->when(strlen($searchTerm) > 0, function ($query) use ($searchTerm) {
                    $query->where(function ($q) use ($searchTerm) {
                        // Use the raw search term (for the `orders` table)
                        $q->whereRaw($searchTerm);
                    });
            })
            ->where('status', $status)
            ->distinct() // If the join causes duplicate orders due to multiple matching products
            ->orderBy('orders.id', 'desc');
        }

        if ($this->status != 1)
            $totalCost = $orderQuery->sum('total');

        // $orders = $orders->paginate(perPage: 10);

        if ($this->status != 1) {
            foreach ($orderQuery->get() as $order) {
                if ($order->payments) {
                    $totalCost -= $order->payments->sum('amount');
                }
            }
        }

        $total = $orderQuery->getQuery()->distinct('orders.id')->count('orders.id');
        $orders = $orderQuery->paginate(10, ['*'], 'page', null)->withPath('')->appends(request()->query());

        // $orders = $orders->paginate(perPage: 10);
        return view('livewire.invoices',["orders"=>$orders, 'totalcost' => $totalCost])
            ->layoutData(['pageName' => 'Invoices'])
            ->title("Invoices");

    }

    public function mount() {
        $this->loggedInUser = auth()->id();
    }

    // public function getListeners() {
    //     return [
    //         "echo-private:message.{$this->loggedInUser},new-message" => "MessageNotification"
    //     ];
    // }

    // public function MessageNotification($payload) {
    //     if ($payload['recipientId'] == auth()->id()) {
    //         $this->messages[] = [
    //             'from' => $payload['from'],
    //             'message' => $payload['message'],
    //             'recipientId' => $payload['recipientId']
    //         ];
    //     }

    // }

}
