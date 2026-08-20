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
use App\Services\PayPalService;
use App\Models\Payment;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\Libs\SearchCriteriaTrait;

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

    public $whatsapptoken = "";
    public $whatsAppNewToken = "";

    public $currentInvoiceId;
    public $textPerson;
    public $order = null;
    public $status = 0;
    public $sql = '';

    public function updatedWhatsapptoken() {
        $this->generateFacebookToken($this->whatsapptoken);
    }

    public function closeWhatsapp() {
        $this->reset('whatsAppNewToken','whatsapptoken');
    }

    private function sendWhatsApp($filename, $handshake, $phone = null) {
        $token = config('chatgpt.FACEBOOK_API');
        $phone_number_id = '580826665103968';
        $phoneTo = $this->textPerson ?? $phone;

        // Normalize (remove non-digits if you want to be safer)
        $phoneTo = preg_replace('/\D/', '', $phoneTo);

        // Validate
        if (empty($phoneTo)) {
            LivewireAlert::title("Phone number was not specified. Please enter phone number and try again!")
                ->error()->toast()->show();
            return;
        }

        $phoneTo = $phoneTo[0] === '1' ? $phoneTo : '1' . $phoneTo;

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
                "to" => $phoneTo,
                "type" => "template",
                "template" => [
                    "name" => 'invoice_copy', // <--- Use the NEW template name here
                    "language" => ["code" => "en_US"], // Try 'en' instead of 'en_US'
                    "components" => [
                        [
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "document",
                                    "document" => [
                                        "id" => $mediaId,
                                        "filename" => $filename
                                    ]
                                ]
                            ]
                        ]
                        // Note: No 'body' component here if your template has no {{1}}
                    ]
                ]
            ];

            $ch = curl_init("https://graph.facebook.com/v21.0/$phone_number_id/messages");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Send JSON data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            // dd($response);
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

        $this->textPerson = null; // Clear the input after sending
    }

    public function sendText($handshake) {
        // $ids=explode(',',$ids);
        // $filename=array();
        // dd($this->textPerson);
        $id = $this->currentInvoiceId;
        $order=Order::find($id);
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        $ret = $printOrder->print($order,'emailmultiple'); // Print newly created proforma/order.
        // $arr=$this->print($id,'emailmultiple');

        // dd($ret);
        $this->sendWhatsApp($ret[0],$handshake,$order->b_phone);
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

    public function balanceForOrder(Order $order): float
    {
        return (float) $order->total - (float) $order->payments->sum('amount');
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

    public function sendEmail($ids) {
        $ids=explode(',',$ids);
        $filename=array();

        $orders=Order::wherein('id',$ids)->get();
        $printOrder = new \App\Libs\PrintOrder(); // Create Print Object

        foreach ($orders as $order) {
            $ret = $printOrder->print($order,'email'); // Print newly created proforma/order.

            //$arr=$this->print($id,'emailmultiple');

            $order=$ret[1];
            $filename[] = $ret[0];

            if ($order->email=='') {
                LivewireAlert::title("Email was not specified. Please enter email and try again!")->error()->toast()->show();
                return;
            }

            $order->emailed=1;
            $order->update();
        }

        LivewireAlert::title("Successfully emailed invoice!")->error()->toast()->show();
        // request()->session()->flash('message', "Successfully emailed invoice!");
    }

    public function createNew() {
        $this->dispatch('create-new');
    }

    public function removeInvoice($id) {
        $order = Order::find($id);
        $product_ids = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo") {
                    $product->p_qty = $product->p_qty + $product->pivot->qty;
                    $product->p_status = 0;
                    $product->update();
                }

            }
        }

        foreach ($order->products() as $product) {
            $product->qty = 0;
            $product->update();
        }

        $payment = Payment::where('order_id',$id);
        $payment->delete();

        request()->session()->flash('message', "Successfully deleted invoice!");
    }

    private function generateAccessToken() {
        $payPalURL = "https://api-m.paypal.com";

        $PAYPAL_CLIENT_ID = config('paypal.live.client_id');
        $PAYPAL_CLIENT_SECRET = config('paypal.live.client_secret');

        if (!$PAYPAL_CLIENT_ID || !$PAYPAL_CLIENT_SECRET) {
            throw new Exception("MISSING_API_CREDENTIALS");
        }

        $auth = base64_encode($PAYPAL_CLIENT_ID . ":" . $PAYPAL_CLIENT_SECRET);

        // Disabling certificate validation for local development
        $client = new Client(['verify' => false]);
        $response = $client->post($payPalURL."/v1/oauth2/token", [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ],
            'headers' => [
                'Authorization' => "Basic $auth"
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }


    /**
    * Issue a Refund
    * * @param string $captureId  The Transaction ID (e.g. 3C679...)
    * @param float|null $amount Optional. If null, refunds FULL amount.
    * @return array
    */
    public function refundInvoice($order) {
        $paypal =  app(PayPalService::class);

        // dd($order->transaction_id);
        $response = $paypal->refund($order);

        if ($response['success']) {
            LivewireAlert::title('Successfully refunded the customer!')->success()->position(Position::TopEnd)->toast()->show();
        } else {
            $errorMessage = $response['error_message'] ?? 'An error occurred during the refund process.';
            LivewireAlert::title('Refund Failed')
                ->withConfirmButton('Ok')
                ->error()
                ->text($errorMessage)
                ->asInfo()
                ->show();
        }
    }

    public function deleteInvoice($id)
    {
        $order = Order::find($id);

        $product_ids = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo") {
                    $product->p_qty = $product->p_qty + $product->pivot->qty;
                    $product->p_status = 0;
                    $product->update();
                }

            }
        }

        $order->products()->detach();
        $order->customers()->detach();

        $payment = Payment::where('order_id',$id);
        $payment->delete();

        $order->delete();

        request()->session()->flash('message', "Successfully deleted invoice!");
    }

    public function returnAllProducts($id) {
        $order = Order::find($id);
        if ($order->transaction_id)
            $response = $this->refundInvoice($order);
        else $response = ['success' => true];

        if (isset($order->payments)) {
            if ($order->payments->count()) {
                $payment = $order->payments->sum('amount');

                $this->dispatch('itemMsg', 'A payment has already been applied in the amount of $' .number_format($payment,2) . '. If you want to modify the quantity or the amount,  you must delete the payment first and then try again.');
                return false;
            }
        }

        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo" && $order->method != "Repair")
                    $product->p_qty = $product->p_qty + $product->pivot->qty;

                $product->p_status = 0;
                $product->pivot->qty = 0;
                $product->pivot->update();
                $product->update();
            }
        }

        $order->subtotal = 0;
        $order->total = 0;
        $order->status = 3; // Returned
        $order->update();

        if ($response['success']) {
            LivewireAlert::title('Successfully refunded the customer!')->success()->position(Position::TopEnd)->toast()->show();
        } else {
            $errorMessage = $response['error_message'] ?? 'An error occurred during the refund process.';
            LivewireAlert::title('Refund Failed')
                ->withConfirmButton('Ok')
                ->error()
                ->text($errorMessage)
                ->asInfo()
                ->show();
        }
    }

    public function render()
    {
        $columns = ['orders.id','b_company','b_lastname','b_firstname', 's_company','method','product_name', 'serial'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);
        $status = $this->status;

        // select('orders.*') tells Laravel to hydrate Order models instead of a mixed collection of joined data.
        // Then with() will correctly load relationships like customers.
        $orderQuery = Order::select('orders.*')
                ->with(['customers', 'payments', 'products'])
                ->join('order_product', 'order_product.order_id', '=', 'orders.id')
                ->when(strlen($searchTerm) > 0, function ($query) use ($searchTerm) {
                    $query->where(function ($q) use ($searchTerm) {
                        // Use the raw search term (for the `orders` table)
                        $q->whereRaw($searchTerm);
                    });
            })
            ->when($status < 4, function ($query) use ($status) {
                $query->where('orders.status', $status);
            })
            ->distinct() // If the join causes duplicate orders due to multiple matching products
            ->orderBy('orders.id', 'desc');

        $totalCost = (clone $orderQuery)
            ->get()
            ->sum(fn (Order $order) => $this->balanceForOrder($order));

        $total = $orderQuery->getQuery()->distinct('orders.id')->count('orders.id');
        $orders = $orderQuery->paginate(10, ['*'], 'page', null)->withPath('')->appends(request()->query());

        return view('livewire.invoices',["orders"=>$orders, 'totalcost' => $totalCost])
            ->layoutData(['pageName' => 'Invoices'])
            ->title("Invoices");

    }
}
