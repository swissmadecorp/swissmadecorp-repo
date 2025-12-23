<?php

namespace App\Livewire;

use App\Models\Estimate;
use Livewire\Component;
use App\Models\Country;
use App\Models\State;
use App\Models\Customer;
use App\Models\TheShow;
use App\Models\Product;
use App\Models\Taxable;
use App\Models\Payment;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderItem extends Component
{
    use WithPagination;

    public $selectedBCountry = 231;
    public $selectedSCountry = 231;
    public $selectedBState = 3956;
    public $selectedSState = 3956;
    public $isOrderPage = true;

    public int $orderId = 0;
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
    public $order;
    public $removedItems = [];
    public $fromPage = "Order";
    public $memoTransfer = false;
    public $orderName;
    public $totalProfit = 0;

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

    #[On('load-order')]
    public function loadOrder($id) {
        $this->orderId = $id;
        $this->order = Estimate::find($id);

        $order=$this->order;
        // $this->order = $order->method;

        $this->customerId = $order->customers->first()->id;

        if ($order) {
            $this->customer = $order->toArray();

            $this->selectedBState = $this->customer['b_state'];
            $this->selectedSState = $this->customer['s_state'];
            $this->selectedBCountry = $this->customer['b_country'];
            $this->selectedSCountry = $this->customer['s_country'];
            $this->customerGroupId = $order->customers->first()->cgroup;

            $this->customer['created_at'] = $order->created_at->format('m/d/Y');
            $this->customer['cc_status'] = $order->cc_status;

            // $this->removeItem("");
            foreach ($order->products as $product) {
                $p_image = $product->images->toArray();

                if (!empty($p_image)) {
                    $image=$p_image[0]['location'];
                } else $image = '../no-image.jpg';

                $cost = isset($product->pivot->cost) ? $product->pivot->cost : $product->p_price;

                $item = ['op_id'=>$product->pivot->id,'id'=>$product->pivot->product_id,'image'=>"/images/thumbs/$image",
                    'product_name'=>$product->pivot->product_name,
                    'qty'=>$product->pivot->qty,'price'=>$product->pivot->price,
                    'onhand'=>$product->p_qty,'msg'=>'','cost'=>$cost,
                    'serial'=>$product->pivot->serial];

                //$this->totalProfit += $product->pivot->price-$cost;
                $this->addItem($item);
            }

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

    #[On('hide-slider')]
    public function hideslider($hide=1) {
        $this->hideSlider = $hide;
    }

    private function removeFromInventoryAdjuster($id) {
        $inventory = \DB::table('table_temp_a')->where('id',$id);

        if(count($inventory->get())){
            $product=Product::join('table_temp_a','table_temp_a.id','=','products.id')
            ->where('table_temp_a.id',$id)->first();

            $inventory->delete();
        }
    }

    public function TransferToOrder() {
        $this->memoTransfer = true;
        $this->customer['po'] = "FROM MEMO";

        $this->saveOrder();
        // $this->clearFields();
    }

    public function saveOrder() {

        // if (\Auth::user()->name == 'Edward B' && $this->order) {

        // }

        if (count($this->items)==0) {
            $this->dispatch('itemMsg', 'You did not select any products. Please add at least one item to this order.');
            $validatedData = $this->validate(
                $this->rules(),
                $this->messages
            );
        } else {
            $this->resetValidation();

            foreach ($this->items as $index => $item) {
                if ($item['id'] && !$item['price']) {
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

        if ($this->orderId) {
            $this->customer['status']= $this->order->status;
            $this->customer['payment_options'] = $this->order->payment_options;
            if ($customer->id != $this->customerId) {
                $this->order->customers()->detach();
                $this->order->customers()->attach($customer->id);
            }
            $this->order->update($this->customer);
            $order = $this->order;
        } else {
            $this->customer['status'] = 0;
            $this->customer['payment_options'] = 'Due upon receipt';

            $order = Estimate::create($this->customer);
            $order->customers()->attach($customer->id);
        }

        $product_ids=array();

        foreach ($this->items as $index => $item) {
            $product_id = $item['id'];

            if ($item['id']) { // if $item has an id then we have a product in the array

                $qty = $item['qty'];
                $price = $item['price'];
                $product_name = $item['product_name'];

                if (!$product_name)
                    $product_name = "Miscellaneous";

                $product = Product::where('id',$product_id)->first();
                if (!$item['op_id']) {
                    $product_ids[]=$product_id;
                    $serial = isset($item['serial']) ? $item['serial'] : "";

                    $order->products()->attach($product->id, [
                        'qty' => $qty,
                        'price' => $price,
                        'product_name' => $product_name,
                    ]);


                } else {
                    $op_id = $item['op_id'];
                    \DB::table('estimate_product')
                        ->where('id', $op_id)
                        ->update([
                            'qty' => $qty,
                            'price' => $price,
                            'product_name' => $product_name
                        ]);

                }
            }

            if ($this->memoTransfer) {
                $items = $this->items->pluck('id')->toArray();
                $products = Product::whereIn('id', $items)->where('category_id',"<>", 74)->get();
                foreach ($products as $product)
                    $product->p_status=8; // mark as sold
                    $product->decrement('p_qty');
                    $product->update();
                }
            }

            $this->deleteProductFromorder();

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
                $total = number_format($this->totalPrice + ($this->totalPrice * ($tax/100))+$freight,2, '.', '');
            } else {
                $tax = 0;
                $total = $this->totalPrice+$freight;
            }

            $order->update([
                'subtotal' => $this->totalPrice,
                'total' => $total,
                'taxable' => $tax,
                'freight' => $freight
            ]);

            if ($this->fromPage == 'products')
                $this->order = $order->id;

            $this->clearFields();

            $this->dispatch('display-message','order/Memo Saved.');

        }
    }

    private function allItemsQuantityZero() {
        return $this->items->every(function ($item) {
            return empty($item['qty']) || $item['qty'] == 0;
        });
    }

    public function deleteProductFromorder() {
        foreach ($this->removedItems as $item) {
            if (in_array($item,$this->removedItems)) {
                $product = $this->order->products->find($item['id']);
                \DB::table('order_product')
                    ->where('op_id', $item['op_id'])
                    ->delete();

                if ($product->category_id!=74)  {
                    if ($this->order->method != 'On Memo')
                        $product->update([
                            'p_qty' => $product->p_qty + $product->pivot->qty,
                            'p_status' => 0
                        ]);
                    else
                        $product->update([
                            'p_status' => 0
                        ]);
                }
            }
        }
    }

    #[On('create-order')]
    public function createorder($ids,$page) {

        $this->fromPage = $page;
        foreach ($ids as $id) {
            $product = Product::find($id);
            $p_image = $product->images->toArray();

            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';

            $item = ['op_id'=>0,'id'=>$product->id,'image'=>"/images/thumbs/$image",
                'product_name'=>$product->title, 'qty'=>1,'price'=>'',
                'onhand'=>$product->p_qty,'msg'=>'','cost'=>$product->p_price,'serial'=>$product->p_serial];

            $this->addItem($item);
        }

    }

    public function clearFields() {
        $this->resetValidation();
        $this->reset();

        $this->purchasedFrom = [1=>'Swiss Made',2=>'Signature Time'];
        $this->customerGroup = ['Dealer','Customer'];

        // Clear all items in the collection
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

        $this->purchasedFrom = ['Swiss Made','Signature Time'];
        $this->customerGroup = ['Dealer','Customer'];
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

                    $price = preg_replace('/[\$,]/', '', $item['price']);

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
            $this->grandtotal = '$'.number_format($total,2);
        } else {
            if (is_numeric($this->totalPrice)) {
                $total = ($this->totalPrice) / (1+($tax/100))+$discount - $freight;
                $this->totalPrice = $total;
            }
        }
    }

    public function updated($propertyName) {

        if (preg_match('/items\.(\d+)\.(\w+)/', $propertyName, $matches)) {
            // $index = (int) filter_var($propertyName, FILTER_SANITIZE_NUMBER_INT);
            $index = $matches[1];
            $property = $matches[2];

            if ($property == 'price' || $property == 'qty') {

                $this->calculateTotalPrice();

            }
            // dd($this->items);
        } elseif ($propertyName == 'customer.freight' || $propertyName == 'customer.discount' || $propertyName == 'customer.cgroup' || $propertyName == 'customerGroupId') {
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'selectedBCountry') {
            $this->selectedSCountry = $this->selectedBCountry;
            $this->selectedBState = null;
            $this->selectedSState = null;
        } elseif ($propertyName == 'selectedSCountry') {
            $this->selectedSState = null;
        } elseif ($propertyName == 'selectedBState') {
            $this->selectedSState = $this->selectedBState;
            $this->calculateTotalPrice();
        } elseif ($propertyName == 'customer.s_zip') {
            $address = addressFromZip($this->customer['s_zip']);

            $this->customer['s_city'] = $address['city'];
            $this->selectedSState = $address['state'];
        } elseif ($propertyName == 'customer.b_zip') {
            $address = addressFromZip($this->customer['b_zip']);

            $this->customer['b_city'] = $address['city'];
            $this->selectedSState = $address['state'];
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

    public function addItem($order='') {

        if (!$order) {
            $product = Product::find($this->newProductId);

            if ($product) {
                if ($this->newProductId==1)
                    $this->newOnHand = 1;
                else $this->newOnHand = $product->p_qty;

                if ($this->newOnHand) {
                    if (count($product->images)) {
                        $image = $product->images->first();
                        $path = '/images/thumbs/'.$image->location;
                    } else {
                        $image="/images/no-image.jpg";
                        $path = $image;
                    }

                    $this->newImage = $path;
                    $this->newProductName = $product->title;
                    $this->newCost = number_format($product->p_price,0,'.','');
                    $this->newSerial = $product->p_serial;

                    $newItem = [
                        'op_id' => 0,
                        'id' => $this->newProductId,
                        'product_name' => $this->newProductName,
                        'price' => $this->newPrice,
                        'cost' => $this->newCost,
                        'image' => $this->newImage,
                        'onhand' => $this->newOnHand,
                        'serial' => $this->newSerial,
                        'qty' => 1
                    ];


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
                    } elseif ($product->p_status == 1) {
                        $this->dispatch('itemMsg',$this->newProductId. ' is on held for Memo');
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
            if ($order['id'] != 1) {
                $existingItem = $this->items->firstWhere('id', $order['id']);
                if ($existingItem) {
                    // If an item with the same ID exists, replace it
                    $this->items = $this->items->map(function ($item) use ($order) {
                        return $item['id'] == $order['id'] ? $order : $item;
                    });
                } else {
                    $this->items->push($order);
                    $this->dispatch('itemadded', $this->newProductId);
                }
            } else {
                $this->items->push($order);
                $this->dispatch('itemadded', $this->newProductId);
            }
        }

        // Force Livewire to re-render the component
        $this->render();
    }

    public function removeSingleItemById($index)
    {
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

        $this->calculateTotalPrice();
        // Dispatch an event to alert the user
        // $this->dispatch('itemRemovedAlert', ['message' => 'Item removed successfully']);

        // Force Livewire to re-render the component
        $this->render();
    }

    public function updatedCustomerBFirstname () {
        //$order = Estimate::Where($this->customer['b_firstname'])->get();
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
        return view('livewire.order-item', ['items' => $this->items, 'paginatedItems' => $this->paginateItems()]);
    }
}
