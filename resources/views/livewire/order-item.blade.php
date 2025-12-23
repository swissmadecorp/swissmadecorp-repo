<div> 
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt --> 
    <div x-data wire:ignore.self id="slideover-order-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideover-order-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div @keydown.escape.prevent="closeAndClearFields()"  wire:ignore.self id="slideover-order" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll w-full" >
            <div class="bg-gray-200 p-3 text-2xl text-gray-500 dark:bg-gray-600 dark:text-gray-300">
                @if ($orderId)
                    Edit order #{{$orderId}}
                @else
                    New Item
                @endif
            </div>
            <div id="slideover-order-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>
            <div wire:ignore.self class="border-b border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#default-styled-tab-content" data-tabs-active-classes="text-purple-600 hover:text-purple-600 dark:text-purple-500 dark:hover:text-purple-500 border-purple-600 dark:border-purple-500" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">
                    <li class="me-2" role="presentation">
                        <button wire:ignore.self class="inline-block p-4 border-b-2 rounded-t-lg" id="customer-info-tab" data-tabs-target="#customer-info" type="button" role="tab" aria-selected="true" aria-controls="profile">Customer Info</button>
                    </li>
                    
                </ul>
            </div>
            
            <div id="default-styled-tab-content" class="dark:bg-gray-900">
                <div wire:loading.delay.long class="fixed z-50">
                    <div class="text-center fixed left-0 top-0 bg-black opacity-50 w-screen h-screen justify-center items-center z-50">
                        <div role="status" class="flex h-screen inline items-center justify-center">
                            <svg aria-hidden="true" class="inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>

                <div wire:ignore.self x-data="{flex: 1}" class="p-4 rounded-lg dark:bg-gray-800" id="customer-info" role="tabpanel" aria-labelledby="customer-info-tab">
                    <form>
                        <x-input-standard model="customer.created_at" label="created_at" text="Order Date" validation/>
                        
                        
                        <div class="grid gap-6 mb-2 mt-4 md:grid-cols-2">
                            <x-input-standard model="customer.po" label="po" text="PO Number" />
                            
                            <x-select-standard text="Payment Method" extraoption label="method" model="customer.method" :iterators="Payments()" validation />
                            
                        </div>

                        <div class="grid gap-6 mb-2 mb-5 md:grid-cols-2">
                            @if ($orderId)
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Order Id</label>
                                <span class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{$orderId}}</span>
                            </div>
                            @else
                            <div></div>
                            @endif
                            <x-select-standard text="Payment Options" label="payment_options" model="customer.payment_options" :iterators="PaymentsOptions()" validation />
                        </div>

                        <div x-data="{ newItemId: null }" x-init="$wire.on('itemadded', id => { newItemId = id; $nextTick(() => { 
                                let elements = document.querySelectorAll(`[data-id='${newItemId}'][data-name='price']`);
                                if (elements.length > 0) {
                                    elements[elements.length - 1].focus(); 
                                }
                            }); })" wire:ignore.self class="pl-2 pr-2 relative overflow-x-auto sm:rounded-lg">
                            <table wire:ignore.self class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Line</th>
                                        <th scope="col" class="px-4 py-3">ID</th>
                                        <th scope="col" class="px-4 py-3">Image</th>
                                        <th scope="col" class="px-4 py-3">Product Name</th>
                                        <th scope="col" class="px-4 py-3">Qty</th>
                                        <th scope="col" class="px-4 py-3">On Hand</th>
                                        <th scope="col" class="px-4 py-3">Price</th>
                                        <th scope="col" class="px-4 py-3">Org. Price</th>
                                        <th scope="col" class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $index => $item)
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                        <th class="text-center">
                                        @if ($item['id'])
                                            {{$index+1}}
                                        @endif
                                        </th>
                                        <td class="px-4 py-4">
                                            <a wire:ignore.self @if ($item['id']!=1) id="editproduct" @endif data-id="{{$item['id']}}" class="cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white"><span style="width: 65px">{{$item['id'] }}</span></a>
                                        </td>
                                        <td class="px-4 py-4"><img style='width: 80px' src="{{ $item['image'] }}" /></td>
                                        <td class="px-4 py-4"><input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model="items.{{$index}}.product_name" type="text"></td>
                                        <td class="px-4 py-4"><input max="1" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-16 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model.live="items.{{$index}}.qty" type="number" pattern="\d*"></td>
                                        <td class="px-4 py-4">{{ $item['onhand'] }}</td>
                                        <td class="px-4 py-4">
                                            <input wire:model.live="items.{{$index}}.price" data-id="{{ $item['id'] }}" data-name="price" id="price-{{ $index }}" type="text" style="width: 80px" placeholder="0.00" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" pattern="^\d*(\.\d{0,2})?$">
                                            @if ($items[$index]['id'])
                                            @error('items.' . $index . '.price')
                                                <div class="text-red-400 font-medium ">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                            @endif
                                        </td>
                                        <td x-data="{ show: false }" @mouseover="show = true" @mouseout="show = false"><span x-show="show">{{ $item['cost'] }}</span></td>
                                        <td class="px-4 py-4"><button wire:click.prevent="removeSingleItemById({{ $index }})">Remove</button></td>
                                    </tr>
                                    <tr>
                                    <td colspan="10">
                                            @error('items')
                                                <div class="text-red-400 font-medium text-center text-lg">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td></td>
                                    <td class="px-4 py-4"><input type="text" wire:model.lazy="newProductId" placeholder="Id" style="width: 65px" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" pattern="\d*" /></td>
                                    <td class="px-4 py-4"></td>
                                    <td class="px-4 py-4"><input type="text" placeholder="Product Name" wire:model="newProductName" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" /></td>
                                    <td colspan="6" class="px-4 py-4"></td>
                                    
                                </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-gray-700 text-xs uppercase">
                                    <th class="p-4" colspan="8">Items Total</th>
                                    <td colspan="2" class="font-bold text-sm text-right px-5"><span>${{number_format($totalPrice,2)}}</span></td>
                                </tfoot>
                            </table>

                            <div class="pr-4 pt-4">
                                <div class="grid gap-2 mb-2 md:grid-cols-3">
                                    <div></div>
                                    <div></div>
                                    <div class="flex pb-2.5 items-center">
                                        <label class="block text-sm font-medium text-gray-900 dark:text-white w-32">Total Profit</label>
                                        <span x-data="{ show: false }" @mouseover="show = true" @mouseout="show = false" class="h-10 text-right bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                            <span class="mr-3" x-show="show">{{$totalProfit}}</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="grid gap-2 mb-2 md:grid-cols-3">
                                    <div></div>
                                    <div></div>
                                    <div class="flex pb-2.5 items-center">
                                        <label for="freight" class="block text-sm font-medium text-gray-900 dark:text-white w-32">Freight</label>
                                        <input id="freight" type="number" wire:model.live="customer.freight" class="text-right bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    </div>
                                </div>
                                <div class="grid gap-2 mb-2 md:grid-cols-3">
                                    <div></div>
                                    <div></div>
                                    <div class="flex pb-2.5 items-center">
                                        <label for="discount" class="block text-sm font-medium text-gray-900 dark:text-white w-32">Discount</label>
                                        <input id="discount" type="number" wire:model.live="customer.discount" class="text-right bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    </div>   
                                </div>
                                @if ($customerGroupId && $selectedSState == 3956)
                                <div class="grid gap-2 mb-2 md:grid-cols-3">
                                    <div></div>
                                    <div></div>
                                    <div class="flex pb-2.5 items-center">
                                        <label for="tax" class="block text-sm font-medium text-gray-900 dark:text-white w-32">Tax</label>
                                        <input id="tax" disabled wire:model.live="customer.tax" class="text-right bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    </div>   
                                </div>
                                @endif
                                <div class="grid gap-2 mb-2 md:grid-cols-3">
                                    <div></div>
                                    <div></div>
                                    <x-input-standard position="text-right" model="grandtotal" live="live" label="total" text="Grand Total" flex />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-6">
                            <div class="w-1/2 pt-3">
                                <div class="mb-4 bg-gray-300 h-10 items-center justify-center p-2 rounded-t-xl">Billing Address</div>
                                <x-input-standard model="customer.b_firstname" label="b_firstname" text="First Name" flex copyto="s_firstname" />
                                <x-input-standard model="customer.b_lastname" label="b_lastname" text="Last Name" flex copyto="s_lastname" />
                                <x-input-standard model="customer.b_company" label="b_company" text="Company" flex copyto="s_company" validation />
                                <x-input-standard model="customer.b_address1" label="b_address1" text="Address 1" flex copyto="s_address1" validation />
                                <x-input-standard model="customer.b_address2" label="b_address2" text="Address 2" flex copyto="s_address2" validation />
                                <x-input-standard model="customer.b_phone" label="b_phone" text="Phone" flex copyto="s_phone" validation />
                                <div wire:ignore class="flex items-center pb-2.5">
                                    <label for="bcountry" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">Country</label>
                                    <select id="bcountry" wire:model.live="selectedBCountry" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        @foreach($this->countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div> 

                                <div class="flex items-center pb-2.5">
                                    <label for="bstate" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">State</label>
                                    <select id="bstate" wire:model.live="selectedBState" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option>Select option</option>
                                        @foreach($this->billingStates as $state)
                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div> 
                                <x-input-standard model="customer.b_city" label="b_city" text="City" flex copyto="s_city" validation />
                                <x-input-standard model="customer.b_zip" label="b_zip" text="Zip Code" flex copyto="s_zip" validation />
                                <x-input-standard model="customer.email" label="b_email" text="Email" flex validation />
                            </div>

                            <div class="w-1/2 pt-3">
                                <div class="mb-4 bg-gray-300 h-10 items-center justify-center p-2 rounded-t-xl">Shipping Address</div>
                                <x-input-standard model="customer.s_firstname" label="s_firstname" text="First Name" flex live="lazy" />
                                <x-input-standard model="customer.s_lastname" label="s_lastname" text="Last Name" flex />
                                <x-input-standard model="customer.s_company" label="s_company" text="Company" flex />
                                <x-input-standard model="customer.s_address1" label="s_address1" text="Address 1" flex />
                                <x-input-standard model="customer.s_address2" label="s_address2" text="Address 2" flex />
                                <x-input-standard model="customer.s_phone" label="s_phone" text="Phone" flex />
                                <div wire:ignore class="flex items-center pb-2.5">
                                    <label for="scountry" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">Country</label>
                                    <select id="scountry" wire:model.live="selectedSCountry" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        @foreach($this->countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div> 

                                <div class="flex items-center pb-2.5">
                                    <label for="sstate" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">State</label>
                                    <select id="sstate" wire:model.live="selectedSState" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option>Select option</option>
                                        @foreach($this->shippingStates as $state)
                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div> 

                                <x-input-standard model="customer.s_city" label="s_city" text="City" flex />
                                <x-input-standard model="customer.s_zip" label="s_zip" text="Zip Code" flex live="live"/>
                            </div>
                        </div>

                        
                        <?php if (!empty($customer['cc_status'])) { ?>
                        <div>
                            <label for="cc_status" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">CC Status</label>
                            <span id="cc_status" wire:model="customer.cc_status" disabled class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{$customer['cc_status']}}</span>
                        </div>
                        <?php } ?>

                        
                        <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your comments</label>
                        <textarea id="message" wire:model="customer.comments" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your comments here..."></textarea>
                        
                        @if ($orderId)
                        <div>
                            <button wire:click="saveOrder()" type="button" class="text-white mt-4 bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Update</button>
                            @role('superadmin|administrator')
                                @if (isset($customer['method']) && $customer['method'] == "On Memo")
                                <button wire:click="TransferToorder()" type="button" class="text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Make order</button>
                                @endif

                            @endrole    
                            <button type="button" id="print-order" class="float-right text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Print order</button>
                        </div>
                        @else
                            <button wire:click="saveOrder()" type="button" class="text-white mt-4 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Save order</button>
                        @endif
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
    
@script
    <script> 
        $(function() {

            var mainPath = "{{route('get.customer.byID')}}";
            function Slider() {
                debugger
                $('body').toggleClass('overflow-hidden')
                $('#slideover-order-container').toggleClass('invisible')
                $('#slideover-order-bg').toggleClass('opacity-0')
                $('#slideover-order-bg').toggleClass('opacity-20')
                $('#slideover-order').toggleClass('translate-x-full')

                if ($('#slideover-order-container').hasClass('invisible')) {
                    const productTabButton = document.getElementById('customer-info-tab');
                    if (productTabButton) {
                        productTabButton.click();
                    }
                } else 
                    setTimeout(() => {
                        $('#created_at').focus();
                    }, "400");
            }

            $wire.on('display-message', msg => {
                debugger
                if (typeof msg[0].hide === typeof undefined)
                    hide = 1
                else hide = msg[0].hide

                if ($wire.$get('fromPage') == 'Order' ) {
                    if (hide==1)
                        Slider()
                }
            });

            $wire.on('itemMsg', msg => {
                Swal.fire({
                    title: "Order",
                    text: msg,
                }).then((result) => {
                    if (result.isConfirmed) {
                        
                    }
                });
            })

            //$wire.on('itemadded', newItemId => {
                // debugger
                // setTimeout(() => {
                //     let element = document.getElementById(`price-${newItemId}`);
                //     if (element) {
                //         element.focus();
                //     }
                // }, 50); 
                // Adjust the delay as needed

            //})

            $(document).on('click', '#editorder', function(event) {
                if ($('#slideover-product-container').length > 0 && document.title != "orders") {
                    $('#slideover-product-container').css('z-index',50)
                    // const productTabButton = document.getElementById('orders-tab');
                    
                    // if (productTabButton) {
                    //     productTabButton.click();
                    // }
                } 
                // if ($('#slideover-order-container').hasClass('invisible'))
                // if (document.title != "Products")
                    Slider()
            })

            $(document).on('input propertychange', '.copy', function() {
                //$('input[copyto="'+$(this).attr('copyto')+'"]').val($(this).val());
                //$('#'+$(this).attr('copyto')).val($(this).val());
                // debugger
                $wire.$set('customer.'+$(this).attr('copyto'),$(this).val())
            })

            $("#created_at").mask("99/99/9999",
                {
                    placeholder:"mm/dd/yyyy",
                    onComplete:function(cep){
                        $wire.$set('customer.created_at',cep)
                    },
                    onChange: function(cep){
                        if (cep == "")
                            $wire.$set('customer.created_at',cep)
                    },
                });

            // $("#s_phone").mask("(999) 999-9999",
            // {
            //     onComplete:function(cep){
            //         $wire.$set('customer.s_phone',cep)
            //     },
            //     onChange: function(cep){
            //         if (cep == "")
            //             $wire.$set('customer.s_phone',cep)
            //     },
            // });
            

            // $("#b_phone").mask("(999) 999-9999",
            // {
            //     onComplete:function(cep){
            //         $wire.$set('customer.b_phone',cep)
            //     },
            //     onChange: function(cep){
            //         if (cep == "")
            //             $wire.$set('customer.b_phone',cep)
            //     },
            // });

            $(document).on('click', '#print-order', function() {
                debugger
                let id = $wire.$get("orderId");
                printWindow = window.open('/admin/orders/print/'+id, 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=300,left=500,width=800,height=600'); 
                var printAndClose = function() {
                    if (printWindow.document.readyState == 'complete') {
                        printWindow.print();
                        clearInterval(sched);
                    }
                }
                var sched = setInterval(printAndClose, 1000);
            })

            $('#b_firstname').devbridgeAutocomplete({
                serviceUrl: mainPath,
                showNoSuggestionNotice : true,
                minChars: 3,
                zIndex: 900,
                onSelect: function (suggestion) {
                    $wire.$set('customer.b_firstname',suggestion.data);
                    // $wire.$set('items.s_firstname',suggestion.value);
                }
            });

            window.closeAndClearFields = function() {
                $wire.$call('clearFields');
                Slider();
            }

            $(document).on('click', '#slideover-order-child', function() {
                closeAndClearFields();
            })

            $('#neworder').click(function() {
                Slider()
            })

      
        })
    </script>
@endscript
    
</div>