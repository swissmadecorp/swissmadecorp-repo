<div>

    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
    <div x-data wire:ignore.self id="slideover-invoice-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideover-invoice-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div wire:ignore.self id="slideover-invoice" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll md:w-full w-[390px] dark:bg-gray-900" >
            <div class="bg-gray-200 p-3 dark:bg-gray-600 dark:text-gray-300 text-2xl text-gray-500">
                @if ($invoiceId)
                    Edit Invoice #{{$invoiceId}}
                @else
                    New Item
                @endif
            </div>
            <div id="slideover-invoice-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>
            <div wire:ignore.self class="border-b border-gray-200 dark:border-gray-700 dark:bg-black">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#default-styled-tab-content" data-tabs-active-classes="text-purple-600 hover:text-purple-600 dark:text-purple-500 dark:hover:text-purple-500 border-purple-600 dark:border-purple-500" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">
                    <li class="me-2" role="presentation">
                        <button wire:ignore.self class="inline-block p-4 border-b-2 rounded-t-lg" id="customer-info-tab" data-tabs-target="#customer-info" type="button" role="tab" aria-selected="true" aria-controls="profile">Customer Info</button>
                    </li>

                    <li x-data="{ invoiceid: @entangle('invoiceId'), invoicename: @entangle('invoiceName')}" x-cloak class="me-2" :class="{'hidden': invoiceid === 0 || invoicename === 'On Memo'}" role="presentation">
                        <button wire:ignore.self class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="payments-tab" data-tabs-target="#payments" type="button" role="tab" aria-selected="false" aria-controls="profile" >Payments</button>
                    </li>
                </ul>
            </div>

            <div id="default-styled-tab-content">
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
                        <div class="grid md:gap-16 mb-6 md:grid-cols-3 p-3 pb-5 bg-gray-100 rounded-lg dark:bg-gray-600">
                            <x-input-standard model="customer.created_at" label="created_at" text="Order Date" validation/>
                            <x-select-standard text="Purchased From" label="purchased_from" model="customer.purchased_from" :iterators="$this->purchasedFrom" validation />
                            <div>
                                <label for="cgroup" class="block text-sm font-medium text-gray-900 dark:text-white">Customer Group</label>
                                <select id="cgroup" wire:model.live="customerGroupId" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    @foreach($this->customerGroup as $index => $group)
                                        <option value="{{ $index }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-6 mt-4 md:grid-cols-2">
                            <x-input-standard model="customer.po" label="po" text="PO Number" />
                            @if (isset($customer['method']) && $customer['method'] == "On Memo")
                                <div class=" pb-2.5">
                                    <div>
                                        <label for="method" class="block font-medium text-sm text-gray-900 dark:text-white ">Payment Method</label>
                                        <input id="method" wire:model="customer.method" disabled class="bg-gray-50 border border-gray-300 text-gray-400 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    </div>
                                </div>
                            @else
                                <x-select-standard text="Payment Method" extraoption label="method" model="customer.method" :iterators="Payments()" validation />
                            @endif

                        </div>

                        <div class="grid gap-6 mb-5 md:grid-cols-2">
                            @if ($invoiceId)
                            <div>
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">Invoice Id</label>
                                <span class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{$invoiceId}}</span>
                            </div>
                            @else
                            <div></div>
                            @endif
                            <x-select-standard text="Payment Options" label="payment_options" model="customer.payment_options" :iterators="PaymentsOptions()" validation />
                        </div>

                        <!-- Popup Menu (Hidden Initially) -->
                        <!-- <div id="items-popup-menu" class="hidden z-50 absolute bg-gray-200 dark:bg-gray-800 shadow-lg rounded-lg border border border-gray-300 w-32">
                            <ul class="divide-gray-300">
                                <li class="items-menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white return">Return</li>
                                <li class="items-menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white remove">Remove</li>

                            </ul>
                        </div> -->

                        <div x-data="{ newItemId: null }" x-init="$wire.on('itemadded', id => { newItemId = id; $nextTick(() => {
                                let elements = document.querySelectorAll(`[data-id='${newItemId}'][data-name='price']`);
                                if (elements.length > 0) {
                                    elements[elements.length - 1].focus();
                                }
                            }); })" class="pl-2 pr-2 relative overflow-x-auto sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Line</th>
                                        <th scope="col" class="px-4 py-3"></th>
                                        <th scope="col" class="px-4 py-3">ID</th>
                                        <th scope="col" class="px-4 py-3">Image</th>
                                        <th scope="col" class="px-4 py-3">Product Name</th>
                                        <th scope="col" class="px-4 py-3">Qty</th>
                                        <th scope="col" class="px-4 py-3">On Hand</th>
                                        <th scope="col" class="px-4 py-3">Price</th>
                                        <th scope="col" class="px-4 py-3">Org. Price</th>
                                        <th scope="col" class="px-4 py-3">Serial</th>
                                        <!-- <th scope="col" class="px-4 py-3">Action</th> -->
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
                                        <td class="text-center">
                                            <input wire:model.live="productSelections.{{ $item['op_id'] }}" model: type="checkbox" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </td>
                                        <td class="px-4 py-4">
                                            <a wire:ignore.self @if ($item['id']!=1) @endif data-id="{{$item['id']}}" class="cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white editproduct"><span style="width: 65px">{{$item['id'] }}</span></a>
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
                                        <td class="text-center" x-data="{ show: false }" @mouseover="show = true" @mouseout="show = false"><span x-show="show">{{ $item['cost'] }}</span></td>
                                        <td class="px-4 py-4">{{ $item['serial'] }}</td>
                                        <!-- <td class="px-4 py-4"> -->
                                            <!-- <button data-dropdown-toggle="dropdown" data-id="{{$item['id']}}" data-index="{{$index}}" class="items-menu-btn inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button">Options <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                                </svg>
                                            </button> -->
                                            <!-- <a href="#" wire:confirm="Are you sure you want to delete this product?" wire:click.prevent="removeSingleItemById({{ $index }})" class="dark:hover:text-gray-300 font-medium inline-flex items-center justify-center text-red-500 text-sm">remove</a> -->
                                        <!-- </td> -->
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
                                    <td></td>
                                    <td class="px-4 py-4"><input type="text" wire:model.lazy="newProductId" placeholder="Id" style="width: 65px" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" type="text" pattern="\d*" /></td>
                                    <td class="px-4 py-4"></td>
                                    <td class="px-4 py-4"><input type="text" placeholder="Product Name" wire:model="newProductName" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" /></td>
                                    <td colspan="6" class="px-4 py-4"></td>

                                </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-gray-700 text-xs uppercase">
                                    <th class="p-4" colspan="8">Items Total</th>
                                    <td colspan="3" class="font-bold text-sm text-right px-5"><span>${{number_format($totalPrice,2)}}</span></td>
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
                                        <label for="additional_fee" class="block text-sm font-medium text-gray-900 dark:text-white w-32">Additional Fee</label>
                                        <input id="additional_fee" type="number" wire:model.live="customer.additional_fee" class="text-right bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
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

                        <div class="md:flex md:gap-6">
                            <div class="md:w-1/2 pt-3">
                                <div id="billing-collapse" data-accordion="collapse" >
                                    <h2 id="accordion-collapse-heading-3">
                                        <button type="button" class="flex items-center justify-between md:bg-gray-100 rounded-t-xl w-full p-5 font-medium rtl:text-right text-gray-500 border border-gray-200 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" data-accordion-target="#accordion-collapse-body-3" aria-expanded="false" aria-controls="accordion-collapse-body-3">
                                        <span>Billing Address</span>
                                        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                        </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-3" class="hidden md:block" aria-labelledby="accordion-collapse-heading-3">
                                        <div class="p-5 border border-t-0 border-gray-200 dark:border-gray-700">
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
                                                        <option value="{{ $state->id }}" {{ (string) $state->id === (string) $this->selectedBState ? 'selected' : '' }}>
                                                            {{ $state->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <x-input-standard model="customer.b_city" label="b_city" text="City" flex copyto="s_city" validation />
                                            <x-input-standard model="customer.b_zip" label="b_zip" text="Zip Code" flex copyto="s_zip" validation />
                                            <x-input-standard model="customer.email" label="b_email" text="Email" flex validation />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="md:w-1/2 pt-3">
                                <div id="shipping-collapse" data-accordion="collapse" >
                                    <h2 id="accordion-collapse-heading-2">
                                        <button type="button" class="flex items-center justify-between md:bg-gray-100 rounded-t-xl w-full p-5 font-medium rtl:text-right text-gray-500 border border-gray-200 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                        <span>Shipping Address</span>
                                        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                        </svg>
                                        </button>
                                    </h2>
                                    <div id="accordion-collapse-body-2" class="hidden md:block" aria-labelledby="accordion-collapse-heading-2">
                                        <div class="p-5 border border-gray-200 dark:border-gray-700">
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
                                                        <option value="{{ $state->id }}" {{ (string) $state->id === (string) $this->selectedSState ? 'selected' : '' }}>
                                                            {{ $state->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <x-input-standard model="customer.s_city" label="s_city" text="City" flex />
                                            <x-input-standard model="customer.s_zip" label="s_zip" text="Zip Code" flex live="live"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <?php if (!empty($customer['cc_status'])) { ?>
                        <div class="mt-4">
                            <label for="cc_status" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">CC Status</label>
                            <span id="cc_status" wire:model="customer.cc_status" disabled class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{$customer['cc_status']}}</span>
                        </div>
                        <?php } ?>

                        <x-input-standard model="customer.tracking" label="tracking" text="Tracking Number" class="pt-4" />

                        <label for="message" class="block text-sm font-medium text-gray-900 dark:text-white">Your comments</label>
                        <textarea id="message" wire:model="customer.comments" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write your comments here..."></textarea>

                        @if ($invoiceId)
                        <div>
                            @role('superadmin|administrator')
                                <button wire:click="saveInvoice()" type="button" class="text-white mt-4 bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Update</button>
                                @if (isset($customer['method']) && $customer['method'] == "On Memo")
                                <button wire:click="TransferToInvoice()" type="button" class="text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Make Invoice</button>
                                @endif
                            @endrole
                            <button type="button" id="print-invoice" class="float-right text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Print Invoice</button>
                            <button type="button" wire:click="ccPage()" class="float-right text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 mr-2">Credit Card Page</button>
                        </div>
                        @else
                            @role('superadmin|administrator')
                                <button wire:click="saveInvoice()" type="button" class="text-white mt-4 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Save Invoice</button>
                            @endrole
                        @endif
                    </form>
                </div>
                <div wire:ignore.self class="hidden rounded-lg dark:bg-gray-800" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                    @if ($invoice)
                    <table x-ref="table" class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-3 py-3">Amount</th>
                                <th scope="col" class="px-3 py-3">Ref #</th>
                                <th scope="col" class="px-3 py-3">Payment</th>
                                <th scope="col" class="px-3 py-3"></th>
                                <th scope="col" class="px-3 py-3">Date</th>
                                <th scope="col" class="px-3 py-3">Action</th>
                                <th scope="col" class="px-3 py-3"></th>
                            </tr>
                        </head>
                        <tbody>

                            <?php $totalLeft = $invoice->total ?>
                            <?php $calc = $invoice->total ?>

                            @if (count($invoice->payments))
                            @foreach ($invoice->payments as $payment)
                            <?php $totalLeft = $totalLeft - $payment->amount ?>
                            <tr class="odd:bg-white hover:bg-gray-50 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <td class="px-4 py-4">${{ number_format($calc,2) }}</td>
                                <td class="px-4 py-4">{{ $payment->ref }}</td>
                                <td class="text-right">${{ number_format($payment->amount,2) }}</td>
                                <td></td>
                                <td class="px-3 py-2 text-center">{{ $payment->created_at->format('m/d/Y') }}</td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" wire:confirm="Are you sure you want to delete this payment?" class="rounded-full hover:bg-red-600 bg-red-500 p-2 text-white" wire:click="deletePayment({{$payment->id}})">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </td>
                                <td></td>
                            </tr>
                            <?php $calc = $calc - $payment->amount ?>
                            @endforeach
                            @if ($totalLeft > 0)
                            <tr>
                            <td class="align-baseline px-4 py-4"><span class="font-bold">Total Owed:</span>
                                <input type="hidden" name="totalLeft" value="{{$totalLeft}}">
                                </td>
                            <td class="align-baseline px-4 py-4">
                                ${{ number_format($totalLeft,2) }}
                                <input type="hidden" value="{{$totalLeft}}" name="fullamount" class="fullamount">
                            </td>
                            <td class="align-baseline px-4 py-4" colspan='4'>
                                <div class="flex gap-4">
                                    <input type="text" style="width: 120px" wire:model="paymentRef" class="payment_option bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Reference" required>
                                    @error('paymentRef')
                                    <span class="block text-red-400">{{$message}}</span>
                                    @enderror

                                    <input type="text" style="width: 140px" wire:model="paymentAmount" class="payment bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="$ Amount" required>
                                    @error('paymentAmount')
                                    <span class="block text-red-400">{{$message}}</span>
                                    @enderror
                                </div>

                            </td>
                            <td class="align-baseline px-4 py-4">
                                <button type="button" wire:click.prevent="$set('paymentAmount',{{$totalLeft}})" class="rounded align-middle border-solid ease-in-out duration-300 border border-gray-600 p-1 hover:bg-gray-200" aria-label="Left Align">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                    </button>
                                    <button type="button" class="rounded align-middle border-solid ease-in-out duration-300	border border-gray-600 p-1 hover:bg-gray-200" wire:click.prevent="savePayment({{$totalLeft}})" aria-label="Left Align">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @else
                        <td class="align-baseline px-4 py-4" colspan="6" style="text-align: center; color: green">Order has been paid fully</td>
                        @endif
                        @else
                    @if ($invoice->status == 0)
                    <tr>
                        <td class="align-baseline px-4 py-4">
                            ${{ number_format($invoice->total,2) }}
                            <input type="hidden" value="{{$invoice->total}}" name="fullamount" class="fullamount">
                        </td>
                        <td class="align-baseline px-4 py-4">
                        <input type="text" style="width: 120px" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model="paymentRef" placeholder="Reference" required>
                            @error('paymentRef')
                            <span class="text-red-400 block">{{$message}}</span><br>
                            @enderror
                        </td>
                        <td class="align-baseline px-4 py-4">
                            <input type="text" style="width: 140px" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" wire:model="paymentAmount" x-ref="xref" class="payment" placeholder="$ Amount" required>
                            @error('paymentAmount')
                            <span class="text-red-400 block">{{$message}}</span><br>
                            @enderror

                        </td>
                        <td class="align-baseline px-4 py-4">
                            <button type="button" wire:click.prevent="$set('paymentAmount',{{$totalLeft}})" class="rounded align-middle border-solid ease-in-out duration-300 border border-gray-600 p-1 hover:bg-gray-200" aria-label="Left Align">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                            </svg>
                            </button>
                            <button type="button" class="rounded align-middle border-solid ease-in-out duration-300	border border-gray-600 p-1 hover:bg-gray-200" wire:click.prevent="savePayment({{$totalLeft}})" aria-label="Left Align">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @else
                        <td class="align-baseline px-4 py-4" colspan="5" style="text-align: center; color: green">Order has been paid fully</td>
                    @endif
                    @endif
                        </tbody>
                    </table>

                    @if (session()->has('message'))
                        <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-white-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
                            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                            </svg>
                            <div class="ms-3 text-sm font-medium">
                                {{ session('message') }}
                            </div>
                            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-white-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
                            <span class="sr-only">Dismiss</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            </button>
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

@script
    <script>
        $(function() {

            var mainPath = "{{route('get.customer.byID')}}";

            function Slider() {
                //debugger
                $('body').toggleClass('overflow-hidden')
                $('#slideover-invoice-container').toggleClass('invisible')
                $('#slideover-invoice-bg').toggleClass('opacity-0')
                $('#slideover-invoice-bg').toggleClass('opacity-20')
                $('#slideover-invoice').toggleClass('translate-x-full')

                // debugger
                if ($('#slideover-invoice-container').hasClass('invisible')) {
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
                // debugger
                if (typeof msg[0].hide === typeof undefined)
                    hide = 1
                else hide = msg[0].hide

                if ($wire.$get('fromPage') == 'Invoice' ) {
                    if (hide==1)
                        Slider()
                }
            });

            $wire.on('itemMsg', msg => {
                Swal.fire({
                    title: "Invoice",
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

            $(document).on('click', '.editinvoice', function(event) {
                // debugger
                if ($('#slideover-product-container').length > 0 && document.title != "Invoices") {
                    $('#slideover-product-container').css('z-index',50)
                } else if ($('#slideover-payment-container').length > 0 && document.title != "Invoices") {
                    $('#slideover-invoice-container').css('z-index',51)
                    $('#slideover-payment-container').css('z-index',50)
                }
                // if ($('#slideover-invoice-container').hasClass('invisible'))
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

            $("#s_phone").mask("(999) 999-9999",
            {
                onComplete:function(cep){
                    $wire.$set('customer.s_phone',cep)
                },
                onChange: function(cep){
                    if (cep == "")
                        $wire.$set('customer.s_phone',cep)
                },
            });


            $("#b_phone").mask("(999) 999-9999",
            {
                onComplete:function(cep){
                    $wire.$set('customer.b_phone',cep)
                },
                onChange: function(cep){
                    if (cep == "")
                        $wire.$set('customer.b_phone',cep)
                },
            });

            $(document).on('click', '#print-invoice', function() {
                let id = $wire.$get("invoiceId");
                printWindow = window.open('/admin/orders/'+id+'/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=300,left=500,width=800,height=600');
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

            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    if (!$('#slideover-invoice-container').hasClass('invisible')) {
                        if ($wire.$get('invoiceId') != 0 ) {
                            closeAndClearFields();
                        }
                    } if (!$('#slideover-product-container').hasClass('invisible')) {
                        debugger
                        if (typeof closeFields === "function") {
                            closeFields();
                        }
                    }
                }
            });

            $(document).on('click', '#slideover-invoice-child', function() {
                closeAndClearFields();
            })

            $('#newinvoice').click(function() {
                Slider()
            })


            // This function contains all your dynamic attribute setting logic.
            $('.items-menu-btn').popupMenu({
                menuSelector: "#items-popup-menu",
                selectors: {
                    menuItem: ".items-menu-item"
                },

                secondaryCloseSelector: "#slideover-invoice-bg",
                // onMenuOpen now receives the full data object!
                onMenuOpen: (data, menu) => {
                    // The 'data' object contains all attributes from the button,
                    // e.g., data.id, data.invoiceid, data.sku, etc.

                    // Example Button HTML: <button data-orderid="123" data-customername="Jane" data-lineindex="5" ...>
                    const id = data.id;
                    const index = data.index;

                    // Your logic is now entirely custom:
                    const openWindow = (url) => `window.open('${url}', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`;

                    menu.find('li.remove').attr({
                        "wire:click.prevent": `returnItemById(${index})`,
                        "wire:confirm": `Return ${id} line # ${index + 1 } back in stock?`
                    });

                    // menu.find('li.remove').attr({
                    //     "wire:click.prevent": `removeSingleItemById(${index})`,
                    //     "wire:confirm": `Remove line # ${index + 1 }?`
                    // });

                    //<a href="#" wire:confirm="Are you sure you want to delete this product?" wire:click.prevent="removeSingleItemById({{ $index }})" class="dark:hover:text-gray-300 font-medium inline-flex items-center justify-center text-red-500 text-sm">remove</a>
                    console.log("Menu logic executed with:", data);
                }
            });

        })
    </script>
@endscript

</div>