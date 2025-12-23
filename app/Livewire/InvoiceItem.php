<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Models\Country;
use App\Models\State;
use App\Models\Customer;
use App\Models\TheShow;
use App\Models\Product;
use App\Models\Taxable;
use App\Jobs\eBayEndItem;
use App\Models\EbayListing;
use App\Models\Payment;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\EbayController;
use App\Jobs\AutomateEbayPost;
use Hashids\Hashids;

class InvoiceItem extends Component
{
    use WithPagination;

    public $selectedBCountry;
    public $selectedSCountry;
    public $selectedBState;
    public $selectedSState;

    // public $checked = false;
    public $productSelections = [];
    public $isOrderPage;
    public int $invoiceId = 0;
    public int $customerId = 0;
    public $customer = [];
    public $bstates = [];
    public $sstates = [];
    public $purchasedFrom = [];
    public $customerGroup = [];
    public int $customerGroupId = 0;
    public $totalPrice = 0;
    public $grandtotal = 0;
    public $items;
    public $invoice;
    public $removedItems = [];
    public $fromPage = "Invoice";
    public $memoTransfer = false;
    public $invoiceName;
    public $totalProfit = 0;
    public $additionalFee = 0;

    public $newProductId;
    public $newQty;
    public $newOnHand;
    public $newSerial;
    public $newProductName;
    public $newPrice;
    public $newCost;
    public $newImage;
    public $hideSlider;
    public $perPage = 10;

    #[Validate('required', message: 'Payment Amount is required')]
    public $paymentAmount;
    #[Validate('required', message: 'Payment Reference is required')]
    public $paymentRef;

    protected $oldItemValue;

    #[On('create-new')]
    public function createNew() {
        $this->selectedBCountry = 231;
        $this->selectedSCountry = 231;
        $this->selectedBState = 3956;
        $this->selectedSState = 3956;

        $this->invoiceId = 0;
    }

    #[On('load-invoice')]
    public function loadInvoice($id) {
        $this->invoiceId = $id;
        $this->invoice = Order::find($id);

        $invoice=$this->invoice;
        $this->invoiceName = $invoice->method;

        $this->customerId = $invoice->customers->first()->id;

        if ($invoice) {
            $this->customer = $invoice->toArray();

            $this->selectedBCountry = $this->customer['b_country'];
            $this->selectedSCountry = $this->customer['s_country'];
            $this->selectedBState = $this->customer['b_state'];
            $this->selectedSState = $this->customer['s_state'];
            $this->customerGroupId = $invoice->customers->first()->cgroup;

            $this->customer['created_at'] = $invoice->created_at->format('m/d/Y');
            $this->customer['cc_status'] = $invoice->cc_status;
            $this->customer['purchased_from'] = $invoice->purchased_from;

            $this->additionalFee = $invoice->additional_fee;

            // $this->removeItem("");
            foreach ($invoice->products as $product) {
                $p_image = $product->images->toArray();

                if (!empty($p_image)) {
                    $image=$p_image[0]['location'];
                } else $image = '../no-image.jpg';

                $cost = isset($product->pivot->cost) ? $product->pivot->cost : $product->p_price;

                $item = ['op_id'=>$product->pivot->op_id,'id'=>$product->pivot->product_id,'image'=>"/images/thumbs/$image",
                    'product_name'=>$product->pivot->product_name,
                    'qty'=>$product->pivot->qty,'price'=>$product->pivot->price,
                    'onhand'=>$product->p_qty,'msg'=>'','cost'=>$cost,
                    'serial'=>$product->pivot->serial];

                // $this->totalProfit += $product->pivot->price;
                $this->productSelections[$product->pivot->op_id] = true;
                $this->addItem($item);
            }

            // $this->grandtotal = '$'.number_format($invoice->total,2);
            $this->calculateTotalPrice();
        }
    }

    public function paginateItems()
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemsForCurrentPage = $this->items->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $itemsForCurrentPage,
            $this->items->count(),
            $this->perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    public function savePayment($totalLeft) {
        $this->validate();

        if ($this->paymentAmount > $totalLeft)
            $applyAmount = $totalLeft;
        else $applyAmount = $this->paymentAmount;

        $orderId = $this->invoice->id;

        Payment::create ([
            'amount' => $applyAmount,
            'ref' => $this->paymentRef,
            'order_id' => $orderId
        ]);

        if ($this->invoice->payments->sum('amount') == $this->invoice->total) {
            $this->invoice->status = 1;
            $this->invoice->update();
        }

        $this->reset('paymentAmount','paymentRef');
        // $this->clearFields();
        $this->dispatch('display-message',['msg'=>'Successfully applied the payment!','hide' => 0]); // false mean don't close the window

    }

    #[On('hide-slider')]
    public function hideslider($hide=1) {
        $this->hideSlider = $hide;
    }

    public function deletePayment($id) {
        $payment = Payment::find($id);
        $payment->delete();

        Order::find($payment->order_id)->update(['status' => 0]);

        // $this->clearFields();
        $this->dispatch('display-message',['msg'=>'Payment has been successfully deleted!','hide' => 0]); // false mean don't close the window
    }

    private function removeFromInventoryAdjuster($id) {
        $inventory = \DB::table('table_temp_a')->where('id',$id);

        if(count($inventory->get())){
            $product=Product::join('table_temp_a','table_temp_a.id','=','products.id')
            ->where('table_temp_a.id',$id)->first();

            $inventory->delete();
        }
    }

    public function ccPage() {
        // return redirect()->route('credit-card-processor', ['id' => $this->invoiceId]);
        $hashids = new Hashids(config('app.key'), 10);
        $maskedId = $hashids->encode($this->invoiceId);

        $this->invoice['code'] = $maskedId;
        $this->invoice->update();

        $url = route('credit.card.processor', ['id' => $this->invoice->id, 'hash' => $maskedId]);

        return redirect()->to($url);
    }

    public function TransferToInvoice() {
        $this->memoTransfer = true;
        $this->customer['method'] = "Invoice";
        $this->customer['po'] = "FROM MEMO";

        $this->saveInvoice();
        // $this->clearFields();
    }

    public function saveInvoice() {

        if (isset($this->customer['method']) && $this->customer['method'] == 'Canceled') {
            $this->cancelInvoice();
            $this->dispatch('display-message','Invoice/Memo Saved.');
            return;
        }

        if (count($this->items)==0) {
            $this->dispatch('itemMsg', 'You did not select any products. Please add at least one item to this invoice.');
            $validatedData = $this->validate(
                $this->rules(),
                $this->messages
            );
        } else {
            $this->resetValidation();

            $this->productSelections = array_filter($this->productSelections);

            foreach ($this->items as $index => $item) {
                if ($item['id'] && !$item['price'] && in_array($item['op_id'], $this->productSelections)) {
                    $this->dispatch('itemMsg', "One or more items don't have a price set.");
                    $this->addError("items.{$index}.price",'Price cannot be empty');
                    return;
                }
            }

            $validatedData = $this->validate(
                $this->rules(),
                $this->messages
            );
            $this->customer['cgroup'] = $this->customerGroupId;

            $created_at = isset($this->customer['created_at']) ? $this->customer['created_at'] : '';

            if ($created_at) {
                $this->customer['created_at']=date('Y-m-d H:i:s', strtotime($created_at));
                $this->customer['updated_at']=date('Y-m-d H:i:s', strtotime($created_at));
            }

            $customer = Customer::find($this->customerId);

            $this->customer['b_country'] = $this->selectedBCountry;
            $this->customer['s_country'] = $this->selectedSCountry;

            $this->customer['b_state'] = $this->selectedBState;
            $this->customer['s_state'] = $this->selectedSState;
            // if (!$customer) {
                $data = array(
                    'cgroup' => $this->customerGroupId,
                    'firstname' => isset($this->customer['b_firstname']) ? allFirstWordsToUpper($this->customer['b_firstname']) : "",
                    'lastname' => isset($this->customer['b_lastname']) ? allFirstWordsToUpper($this->customer['b_lastname']) : "",
                    'company' => isset($this->customer['b_company']) ? $this->customer['b_company'] : "",
                    'address1' => isset($this->customer['b_address1']) ? allFirstWordsToUpper($this->customer['b_address1']) : "",
                    'address2' => isset($this->customer['b_address2']) ? $this->customer['b_address2'] : "",
                    'phone' => isset($this->customer['b_phone']) ? localize_us_number($this->customer['b_phone']) : "",
                    'country' => $this->customer['b_country'],
                    'state' => $this->customer['b_state'],
                    'city' => isset($this->customer['b_city']) ? strtoupper($this->customer['b_city']) : "",
                    'zip' => isset($this->customer['b_zip']) ? $this->customer['b_zip'] : ""
                );

                $customer = Customer::updateOrCreate(['company'=>$this->customer['b_company']],$data);
            // }

            //$subtotal = 0;

            if ($this->invoiceId) {
                $this->customer['status']= $this->invoice->status;
                $this->customer['payment_options'] = $this->invoice->payment_options;
                if ($customer->id != $this->customerId) {
                    $this->invoice->customers()->detach();
                    $this->invoice->customers()->attach($customer->id);
                }
                $this->invoice->update($this->customer);
                $order = $this->invoice;
            } else {
                $this->customer['status'] = 0;
                $this->customer['payment_options'] = 'Due upon receipt';

                $order = Order::create($this->customer);
                $order->customers()->attach($customer->id);
            }

            $productToEnd=array();

            foreach ($this->items as $index => $item) {
                $product_id = $item['id'];

                if ($item['id'] && in_array($item['op_id'], $this->productSelections)) { // if $item has an id then we have a product in the array
                    $qty = $item['qty'];
                    $price = $item['price'];
                    $product_name = $item['product_name'];

                    if (!$product_name)
                        $product_name = "Miscellaneous";

                    $product = Product::where('id',$product_id)->first();
                    if (!$item['op_id'] || !is_numeric($item['op_id'])) {
                        $serial = isset($item['serial']) ? $item['serial'] : "";

                        $cost = $item['cost'];

                        $order->products()->attach($product->id, [
                            'qty' => $qty,
                            'price' => $price,
                            'serial' => $serial,
                            'product_name' => $product_name,
                            'cost' => $cost
                        ]);

                        if ($product_id != 1) {
                            $this->removeFromInventoryAdjuster($product_id);
                            $theshow=TheShow::where('product_id',$product_id);
                            if($theshow->get()){
                                $theshow->delete();
                            }
                        }

                        if ($product->category_id != 74) {
                            if ($this->customer['method'] == 'Invoice') {
                                $productToEnd[]=$product_id;
                                $product->p_status=8; // mark as sold
                                $product->decrement('p_qty');
                                $product->update();
                            } elseif ($this->customer['method'] == 'On Memo') {
                                $product->p_status=1;
                                $product->update();
                            }
                        }

                        //$subtotal += $price*$qty;

                    } else {
                        $op_id = $item['op_id'];
                        \DB::table('order_product')
                            ->where('op_id', $op_id)
                            ->update([
                                'qty' => $qty,
                                'price' => $price,
                                'product_name' => $product_name
                            ]);

                        if ($product->category_id != 74) {
                            $qty = $item['qty'];

                            if ($product->p_qty==0 && $qty == 0) {
                                if ($this->customer['method'] == 'Invoice') {
                                    // dd('invoice qty is 0');
                                    $product->increment('p_qty');
                                    $product->p_status=0; // mark as available

                                    $product->update();
                                    $this->postToEbay($product);
                                } elseif ($this->customer['method'] == 'On Memo') {
                                    // dd('memo qty is 0');
                                    $product->p_status=0;
                                    $product->update();
                                }
                            } elseif ($product->p_qty==1 && $qty == 0) {
                                if ($this->customer['method'] == 'On Memo') {
                                    if ($product->p_qty == 0) // if for some reason the invoice changes back to memo and the qty is 0, we need to increment it
                                        $product->increment('p_qty');

                                    $product->p_status=0;
                                    $product->update();
                                }
                            } elseif ($qty == 1 && $qty != $product->p_qty) {
                                if ($this->customer['method'] == 'Invoice') {
                                    $productToEnd[]=$product_id;
                                    $product->p_status=8; // mark as sold
                                    if ($product->p_qty > 0)
                                        $product->decrement('p_qty');

                                    $product->update();
                                } elseif ($this->customer['method'] == 'On Memo') {
                                    // dd('memo qty is 1');
                                    $product->p_status=1;
                                    $product->update();
                                }
                            }
                        }
                    }
                }
            }

            if ($this->memoTransfer) {
                $items = $this->items->pluck('id')->toArray();
                $products = Product::whereIn('id', $items)->where('category_id',"<>", 74)->get();
                foreach ($products as $product) {
                    $product->p_status=8; // mark as sold
                    $product->decrement('p_qty');
                    $product->update();
                }
            }

            $this->deleteProductFromInvoice();

            // If there is only 1 or item's quantity were set to 0 item in the collection, that means there are no items left
            if (count($this->items) == 0 || $this->allItemsQuantityZero()) {
                $order->status = 2;  // Mark order status as returned
            } elseif ($this->allItemsQuantityZero()==false)
                $order->status = 0;

            $freight = 0;
            if (isset($this->customer['freight']) && $this->customer['freight'] != '')
                $freight = $this->customer['freight'];

            if ($this->customerGroupId == 1) {
                $tax = $this->customer['tax']; //Taxable::where('state_id',$order->s_state)->value('tax');
                $total = number_format($this->additionalFee+$this->totalPrice + ($this->totalPrice * ($tax/100))+$freight,2, '.', '');
            } else {
                $tax = 0;
                $total = $this->totalPrice+$freight+$this->additionalFee;
            }

            // dd($order->payments->sum('amount') , ' ', $total);
            if ($order->payments->sum('amount') == $total && $order->payments->sum('amount') > 0)
                $status = 1;
            else $status = $order->status;

            $order->update([
                'subtotal' => $this->totalPrice,
                'total' => $total,
                'taxable' => $tax,
                'freight' => $freight,
                'status' => $status
            ]);


            if (count($productToEnd)>0)
                eBayEndItem::dispatch($productToEnd);

            if ($this->fromPage == 'products')
                $this->invoiceId = $order->id;

            $this->clearFields();

            $this->dispatch('display-message','Invoice/Memo Saved.');

        }
    }

    private function postToEbay($product) {
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

    private function allItemsQuantityZero() {
        return $this->items->every(function ($item) {
            return empty($item['qty']) || $item['qty'] == 0;
        });
    }

    public function cancelInvoice() {
        $order = $this->invoice;

        if (isset($order->payments)) {
            if ($order->payments->count()) {
                $payment = $order->payments->sum('amount');

                $this->dispatch('itemMsg', 'A payment has already been applied in the amount of $' .number_format($payment,2) . '. If you want to modify the quantity or the amount,  you must delete the payment first and then try again.');
                return false;
            }
        }

        $productToEnd = array();
        foreach ($order->products as $product) {
            if ($product->p_status != 4 && $product->category_id!=74) {
                if ($order->method != "On Memo")
                    $product->p_qty = $product->p_qty + $product->pivot->qty;

                $productToEnd[] = $product->id;
                $product->p_status = 0;
                $product->pivot->qty = 0;
                $product->pivot->update();
                $product->update();
            }
        }

        if (count($productToEnd) > 0)
            eBayEndItem::dispatch($productToEnd);

        $order->subtotal = 0;
        $order->total = 0;
        $order->status = 2;
        $order->update();
    }

    public function deleteProductFromInvoice() {
        $productToEnd = array();
        $excludedProducts = $this->excludedProducts();

        foreach ($excludedProducts as $item) {
            // if (in_array($item,$this->removedItems)) {
                // $product = $this->invoice->products->find($item['id']);
                $orderProduct = \DB::table('order_product')
                    ->where('op_id', $item);

                if ($orderProduct->first()) {
                    $product = $this->invoice->products->find($orderProduct->first()->product_id);

                    $orderProduct->delete();

                    if ($product->category_id!=74)  {
                        if ($this->invoice->method != 'On Memo') {
                            $productToEnd[] = $product->id;
                            $product->update([
                                'p_qty' => $product->p_qty + $product->pivot->qty,
                                'p_status' => 0
                            ]);
                        } else
                            $product->update([
                                'p_status' => 0
                            ]);
                    }
            }
        }

        if (count($productToEnd) > 0)
            eBayEndItem::dispatch($productToEnd);
    }

    #[On('create-invoice')]
    public function createInvoice($ids,$page) {
        $this->fromPage = $page;

        $this->selectedBCountry = 231;
        $this->selectedSCountry = 231;
        $this->selectedBState = 3956;
        $this->selectedSState = 3956;

        foreach ($ids as $prod) {
            if (is_array($prod)) {
                $id = $prod[0];
                $price = $prod[1];
            } else {
                $price = '';
                $id = $prod;
            }

            $product = Product::find($id);
            $p_image = $product->images->toArray();

            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';

            $newOpId = str_replace('-', '', (string) Str::uuid());
            $this->productSelections[$newOpId] = true;

            $item = ['op_id'=>$newOpId,'id'=>$product->id,'image'=>"/images/thumbs/$image",
                'product_name'=>$product->title, 'qty'=>1,'price'=>$price,
                'onhand'=>$product->p_qty,'msg'=>'','cost'=>$product->p_price,'serial'=>$product->p_serial];

            $this->addItem($item);
        }

    }

    public function clearFields() {
        $this->resetValidation();
        $this->reset();

        $this->purchasedFrom = [0=>'Swiss Made',1=>'Signature Time'];
        $this->customerGroup = ['Dealer','Customer'];

        // Clear all items in the collection
        $this->selectedBCountry = 0;
        $this->selectedSCountry = 0;
        $this->selectedBState = 0;
        $this->selectedSState = 0;

        $this->initItems();
    }

    public function initItems() {
        // $this->fill([
        //     'items' =>collect([['op_id'=>0,'id'=>'','img'=>'','product_name'=>'', 'price'=>'','qty'=>'','onhand'=>'','msg'=>'','cost'=>'','serial'=>'']])
        // ]);

        $this->items = collect([]);
    }


    #[Computed]
    public function countries() {
        return Country::All();
    }

    #[Computed]
    public function billingStates() {
        return State::where('country_id',$this->selectedBCountry)->get();
    }

    #[Computed]
    public function shippingStates() {
        return State::where('country_id',$this->selectedSCountry)->get();
    }

    public function mount() {

        $this->purchasedFrom = [0=>'Swiss Made',1=>'Signature Time'];
        $this->customerGroup = ['Dealer','Customer'];

        $states = $this->shippingStates;

        // If `selectedSState` is not valid or not set, set it to the first valid state
        if (!$this->selectedSState || !in_array($this->selectedSState, $states->pluck('id')->toArray())) {
            $this->selectedSState = $states->first()?->id;
        }

        $this->initItems();
    }

    protected function rules() {
        return [
            'customer.method' => ['required','not_in:-1'],
            'customer.b_company' => ['required'],
            'items' => 'required|array|min:1',
            //'items.*.price' => 'required'
        ];
    }

    protected $messages = [
        'customer.method.required' =>'This field is required.',
        'customer.method.not_in' => 'The selected payment method is invalid.',
        'customer.b_company.required' => 'This field is required.',
        'items.required' => 'At least one row must be filled in.',
        'items.min' => 'At least one row must be filled in.',
        //'items.*.price.required' => 'Price cannot be empty.',
    ];

    protected function calculateTotalPrice($calc = 1) {

        if ($calc) {
            $this->totalProfit = 0;
            $this->totalPrice = $this->items
                ->filter(function ($item) {
                    return !empty($item['id']) || $this->items->last() !== $item;
                })
                ->sum(function ($item) {
                    if (!$item['qty'])
                        $qty = 0;
                    else $qty = $item['qty'];

                    if (isset($item['price']))
                        $price = preg_replace('/[\$,]/', '', $item['price']);
                    else $price = 0;

                    if ($price)
                        $p = ($price*$qty);
                    else $p=0;

                    $c = $item['cost']*$qty;

                    $this->totalProfit += $p - $c;

                    return $p && $qty ? $price * $qty : 0;
                });
                $this->totalProfit = '$'.number_format($this->totalProfit,2);
        }

        $freight = 0;
        $tax = 0;
        $discount = 0;

        if (isset($this->customer['freight']) && $this->customer['freight'])
            $freight = $this->customer['freight'];

        if ($this->selectedSState == 3956 && $this->customerGroupId == 1) {
            $tax = Taxable::where('state_id',$this->selectedSState)->value('tax');
            $this->customer['tax'] = $tax;
        } else $this->customer['tax'] = null;

        if (isset($this->customer['discount']) && $this->customer['discount']) {
            $discount = $this->customer['discount'];
            // $this->totalPrice -= $discount;
        }



        if ($calc) {
            $total = ($this->totalPrice -$discount + $freight );
            $total = $total + ($total) * ($tax/100);
            $this->grandtotal = '$'.number_format($this->additionalFee + $total,2);
        } else {
            if (is_numeric($this->totalPrice)) {
                $total = ($this->totalPrice) / (1+($tax/100))+$discount - $freight;
                $this->totalPrice = $total + $this->additionalFee;
            }
        }
    }

    public function updated($propertyName) {
        // \Log::debug($propertyName);

        if (preg_match('/items\.(\d+)\.(\w+)/', $propertyName, $matches)) {
            // $index = (int) filter_var($propertyName, FILTER_SANITIZE_NUMBER_INT);
            $index = $matches[1];
            $property = $matches[2];

            if ($property == 'price' || $property == 'qty') {

                $this->calculateTotalPrice();

                if (isset($this->invoice->payments)) {
                    if ($this->invoice->payments->count()) {
                        $payment = $this->invoice->payments->sum('amount');
                        $value = preg_replace('/[\$,]/', '', $this->grandtotal);
                        $total = floatval($value);

                        if ($payment > $total) {
                            $item = $this->items->get($index);

                            $item['qty'] = $this->invoice->products[$index]->pivot->qty;
                            $item['price'] = $this->invoice->products[$index]->pivot->price;
                            $this->items->put($index, $item);

                            $this->calculateTotalPrice();
                            $this->dispatch('itemMsg', 'A payment has already been applied in the amount of $' .number_format($payment,2) . '. If you want to modify the quantity or the amount,  you must delete the payment first and then try again.');
                            return false;
                        }
                    }
                }
            }
            // dd($this->items);
        } elseif (preg_match('/productSelections\.([a-zA-Z0-9\-]+)/', $propertyName, $matches)) {
            $op_id = $matches[1];

            if (isset($this->productSelections[$op_id]) && !$this->productSelections[$op_id]) {
                // set the product to 0
                $this->items = $this->items->transform(function ($item) use ($op_id) {
                    if ($item['op_id'] == $op_id) {
                        $item['qty'] = 0; // or set another field to 0 if you meant something else
                    }
                    return $item;
                });

            } else {
                // set the product to 1
                $this->items = $this->items->transform(function ($item) use ($op_id) {
                    if ($item['op_id'] == $op_id) {
                        $item['qty'] = 1; // or set another field to 0 if you meant something else
                    }
                    return $item;
                });
            }
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'customer.freight' || $propertyName == 'customer.discount' || $propertyName == 'customer.cgroup' || $propertyName == 'customerGroupId') {
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'selectedBCountry') {
            $this->selectedSCountry = $this->selectedBCountry;
            $this->selectedBState = null;
            $this->selectedSState = null;
        } elseif ($propertyName == 'customer.additional_fee') {
            $this->additionalFee = $this->customer['additional_fee'] > 0 ? $this->customer['additional_fee'] : 0;
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'selectedSCountry') {
            $this->selectedSState = null;
        } elseif ($propertyName == 'selectedBState') {
            $this->selectedSState = $this->selectedBState;
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'customer.s_zip') {
            $szip = trim($this->customer['s_zip']);
            if (strlen($szip) == 5) {
                $address = addressFromZip($szip);

                $this->selectedSCountry = 231;
                $this->customer['s_city'] = $address['city'];
                $this->selectedSState = $address['state'];
            }
        } elseif ($propertyName == 'customer.b_zip') {
            $bzip = trim($this->customer['b_zip']);
            if (strlen($bzip) == 5) {
                $address = addressFromZip($bzip);

                $this->selectedBCountry = 231;
                $this->customer['b_city'] = $address['city'];
                $this->selectedBState = $address['state'];
            }
        } elseif ($propertyName == "newProductId") {
            $this->addItem();
        } elseif ($propertyName == 'customer.s_firstname') {
            if (isset($this->customer['s_firstname'])) {
                $firstname = trim($this->customer['s_firstname']);
                if (strpos($firstname,' ') !== false) {
                    $firstname_lastname = explode(' ', $firstname);
                    $this->customer['s_firstname'] = $firstname_lastname[0];
                    $this->customer['s_lastname'] = $firstname_lastname[1];
                    $this->customer['s_company'] = $firstname;
                }
            }
        } elseif ($propertyName == 'grandtotal') {

            if ($this->totalPrice) {
                $value = preg_replace('/[\$,]/', '', $this->grandtotal);
                $value = floatval($value);
                $this->totalPrice = $value;
                $this->calculateTotalPrice(0);
            }

        }
    }

    public function addItem($invoiceitem='') {
        if (!$invoiceitem) {
            $product = Product::find($this->newProductId);

            if ($product) {
                if ($this->newProductId==1)
                    $this->newOnHand = 1;
                else $this->newOnHand = $product->p_qty;

                if ($this->newOnHand > 0) {
                    if (count($product->images)) {
                        $image = $product->images->first();
                        $path = '/images/thumbs/'.$image->location;
                    } else {
                        $image="/images/no-image.jpg";
                        $path = $image;
                    }

                    if ($product->p_status == 1) { // On Memo
                        $order = Order::where('method', 'On Memo')
                            ->whereHas('products', function ($query) {
                                $query->where('product_id', $this->newProductId);
                            })->first();

                        $this->newProductId = '';
                        $this->dispatch('itemMsg',$this->newProductId. ' is held for Memo - ' . $order->b_company);
                        return;
                    }
                    $this->newImage = $path;
                    $this->newProductName = $product->title;
                    $this->newCost = number_format($product->p_price,0,'.','');
                    $this->newSerial = $product->p_serial;

                    $newOpId = str_replace('-', '', (string) Str::uuid());
                    $newItem = [
                        'op_id' => $newOpId,
                        'id' => $this->newProductId,
                        'product_name' => $this->newProductName,
                        'price' => $this->newPrice,
                        'cost' => $this->newCost,
                        'image' => $this->newImage,
                        'onhand' => $this->newOnHand,
                        'serial' => $this->newSerial,
                        'qty' => 1
                    ];


                    $this->productSelections[$newOpId] = true;
                    // Check for duplicates and allow duplicates for id 1
                    if ($newItem['id'] != 1) {
                        $existingItem = $this->items->firstWhere('id', $newItem['id']);
                        if ($existingItem) {
                            // If an item with the same ID exists, replace it
                            $this->dispatch('itemMsg', $this->newProductId. ' is already in the list.');
                            $this->items = $this->items->map(function ($item) use ($newItem) {
                                return $item['id'] == $newItem['id'] ? $newItem : $item;
                            });
                        } else {
                            $this->items->push($newItem);
                            $this->dispatch('itemadded', $this->newProductId);
                        }
                    } else {
                        $this->items->push($newItem);
                        $this->dispatch('itemadded', $this->newProductId);
                    }

                    if ($product->p_status == 2) { // On hold
                        $this->dispatch('itemMsg',$this->newProductId. ' is on hold');
                    }
                } else {
                    // $this->dispatch('itemZero', id: $this->newProductId);
                    $this->dispatch('itemMsg', $this->newProductId. ' is out of stock');
                }
            } else {
                $this->dispatch('itemMsg', $this->newProductId. ' is not found in the inventory.');
            }

            // Clear the input fields
            $this->newProductId = '';
            $this->newProductName = '';
            $this->newPrice = '';
            $this->newCost = '';
            $this->newImage = '';
        } else {
            if ($invoiceitem['id'] != 1) {
                $existingItem = $this->items->firstWhere('id', $invoiceitem['id']);
                if ($existingItem) {
                    // If an item with the same ID exists, replace it
                    $this->items = $this->items->map(function ($item) use ($invoiceitem) {
                        return $item['id'] == $invoiceitem['id'] ? $invoiceitem : $item;
                    });
                } else {
                    $this->items->push($invoiceitem);
                    $this->dispatch('itemadded', $this->newProductId);
                }
            } else {
                $this->items->push($invoiceitem);
                $this->dispatch('itemadded', $this->newProductId);
            }
        }

        // Force Livewire to re-render the component
        $this->render();
    }

    private function excludedProducts() {
        $productArray = (array_keys($this->productSelections));
        $build = [];
        foreach ($productArray as $key) {
            if ($this->productSelections[$key] == false) {
                $build[] = $key;
            }
        }

        return $build;
    }

    public function removeSingleItemById($index)
    {
        $selections = $this->productsSelected();
        if (count($selections) > 1) {

        } else {

        $arr = $this->items->get($index);

        $itemId = $arr['id'];

        // store item with the op_id which is already in the database to be deleted later.
        if (isset($arr['op_id'])) {
            $itemToRemove = $this->items->firstWhere('op_id', $arr['op_id']);
            if ($itemToRemove) {
                if ($itemToRemove['op_id'] != 0) {
                    $this->removedItems[] = $itemToRemove;
                }
            }
        }

        if ($itemId == 1) {
            $this->items = $this->items->filter(function ($item,$indx) use ($itemId, $index) {
                if ($index == $indx) {
                    return false;
                }
                return true;
            })->values();
        } else {

            $this->items = $this->items->reject(function ($item) use ($itemId) {
                return $item['id'] == $itemId;
            })->values();
        }


        // Dispatch an event to alert the user
        // $this->dispatch('itemRemovedAlert', ['message' => 'Item removed successfully']);

    }

        $this->calculateTotalPrice();
        // Force Livewire to re-render the component
        $this->render();
    }

    public function updatedCustomerBFirstname () {
        //$invoice = Order::Where($this->customer['b_firstname'])->get();
        $id = $this->customer['b_firstname'];
        $customer = Customer::find($id);

        if ($customer) {
            $this->customer['b_firstname'] = $customer->firstname;
            $this->customer['b_lastname'] = $customer->lastname;
            $this->customer['b_company'] = $customer->company;
            $this->customer['b_address1'] = $customer->address1;
            $this->customer['b_address2'] = $customer->address2;
            $this->customer['b_phone'] = $customer->phone;
            $this->customer['b_country'] = $customer->country;
            $this->customer['b_state'] = allFirstWordsToUpper($customer->state);
            $this->customer['b_city'] = allFirstWordsToUpper($customer->city);

            // dd($this->customer['b_country']);
            $this->customer['b_zip'] = $customer->zip;
            $this->customer['email'] = $customer->email;

            $this->customerId = $customer->id;
            $this->customer['s_firstname'] = $customer->firstname;
            $this->customer['s_lastname'] = $customer->lastname;
            $this->customer['s_company'] = $customer->company;
            $this->customer['s_address1'] = $customer->address1;
            $this->customer['s_address2'] = $customer->address2;
            $this->customer['s_phone'] = $customer->phone;
            $this->customer['s_country'] = $customer->country;
            $this->customer['s_state'] = $customer->state;
            $this->customer['s_city'] = allFirstWordsToUpper($customer->city);
            $this->customer['s_zip'] = allFirstWordsToUpper($customer->zip);
        } else
            $this->customerId = 0;

        //$this->calculateTotalPrice();

    }
    public function render()
    {
        return view('livewire.invoice-item', ['items' => $this->items, 'paginatedItems' => $this->paginateItems()]);
    }
}
