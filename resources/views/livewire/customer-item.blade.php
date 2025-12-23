<div> 
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt --> 
    <div x-data wire:ignore.self id="slideover-customer-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideover-customer-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0 "></div>
        <div wire:ignore.self id="slideover-customer" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-900 border" style="width: 700px">
            <div class="bg-gray-200 p-3 dark:bg-gray-600 dark:text-gray-300 text-2xl text-gray-500">
                @if ($customerId)
                    Edit customer
                @else
                    New customer
                @endif
                
            </div>
            <div id="slideover-customer-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>
            
            <div class="p-6">
                <x-select-standard text="Customer Group" label="group" model="customer.cgroup" :iterators="['Dealer','Customer']" />
                <x-input-standard model="customer.company" label="company" text="Company" class="pt-2" validation/>
                <div class="grid gap-2 mt-2 md:grid-cols-2">
                    <x-input-standard model="customer.firstname" label="firstname" text="First Name" />
                    <x-input-standard model="customer.lastname" label="lastname" text="Last Name" />
                    <x-input-standard model="customer.address1" label="address1" text="Address1" />
                    <x-input-standard model="customer.address1" label="address2" text="Address2" />
                    <x-input-standard model="customer.phone" label="phone" text="Phone" />
                    <x-input-standard model="customer.city" label="city" text="City" />

                    <div wire:ignore class="items-center pb-2.5">
                        <label for="bcountry" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">Country</label>
                        <select id="bcountry" wire:model.live="selectedBCountry" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="0">Select Country</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="items-center pb-2.5">
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

                    <x-input-standard model="customer.zip" label="zip" text="Zip Code" validation/>
                    <x-input-standard model="customer.email" label="email" text="Email" validation/>
                </div>

                <div class="flex justify-end">
                    @if ($customerId)
                        <button wire:click="saveCustomer()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Update Customer</button>
                    @else
                        <button wire:click="saveCustomer()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Save Customer</button>
                    @endif
                </div>

                <hr>
                <livewire:invoice-item />
                @if (!empty($previousOrders))

                <div class="p-2">
                    <div class="flex justify-between">
                        <h1 class="dark:text-gray-200">Previous Invoices</h1>
                        <input type="text" x-ref="searchbox" wire:model.live.debounce.5s="search" id="supplier-search" class="focus:ring-0 bg-gray-50 border-0 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-10 p-0 rounded-lg text-gray-900 w-52 px-3" placeholder="Search for items">
                    </div>

                    <div class="max-h-[340px] min-h-[auto] overflow-y-auto shadow-lg overflow-x-hidden">
                        <table wire:ignore.self class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Order ID</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3">Date</th>
                                    <th scope="col" class="px-4 py-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($previousOrders as $order)
                                    <?php 
                                        $status = $order->status;
                                        if ($status == 0)
                                            $style = 'odd:bg-red-100 even:bg-red-50 hover:bg-red-200 even:bg-red-50';
                                        elseif ($status == 1) 
                                            $style = 'odd:bg-gray-100 hover:bg-gray-200 even:bg-gray-50'; 
                                        elseif ($status == 2)
                                            $style = 'odd:bg-blue-100 hover:bg-blue-200 even:bg-blue-50'; 
                                        ?>
                                    <tr class="<?= $style ?> text-gray-700">
                                        <td class="px-4 py-4">
                                            <a @click="$dispatch('load-invoice', { id: {{$order->id}} })" data-id="{{$order->id}}" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600">{{$order->id}}</a>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($order->status == 0)
                                            {{ "Unpaid" }}
                                            @elseif ($order->status == 1)
                                            {{ "Paid" }}
                                            @elseif ($order->status == 2)
                                            {{ "Return" }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">{{$order->created_at->format('m/d/Y') }}</td>
                                        <td class="px-4 py-4">${{number_format($order->total,2)}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $previousOrders->links('livewire.pagination') }}
                </div>
                @endif


                <div class="p-2">
                    <div class="flex justify-between">
                        <h1 class="dark:text-gray-200">As Supplier</h1>
                        <input type="text" x-ref="searchbox" wire:model.live.debounce.5s="searchSupplier" id="search" class="focus:ring-0 bg-gray-50 border-0 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-10 p-0 rounded-lg text-gray-900 w-52 px-3" placeholder="Search for items">
                    </div>
                    <div class="max-h-[340px] min-h-[auto] overflow-y-auto shadow-lg overflow-x-hidden">
                        <table wire:ignore.self class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Order ID</th>
                                    <th scope="col" class="px-4 py-3">Name</th>
                                    <th scope="col" class="px-4 py-3">Date</th>
                                    <th scope="col" class="px-4 py-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!empty($supplierProducts))
                                    @foreach ($supplierProducts as $product)
                                        <tr class="odd:bg-gray-100 hover:bg-gray-200 even:bg-gray-50 text-gray-700">
                                            <td class="px-4 py-4">
                                                <a @click="$dispatch('load-invoice', { id: {{$product->id}} })" data-id="{{$product->id}}" class="editproduct cursor-pointer dark:hover:text-white text-sky-600">{{$product->id}}</a>
                                            </td>
                                            <td class="px-4 py-4">{{$product->title}}</td>
                                            <td class="px-4 py-4">{{ $product->created_at->format('m/d/Y') }}</td>
                                            <td class="px-4 py-4">${{ number_format($product->p_price,2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                <tr><td>No Invoices found</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if (!empty($supplierProducts))
                        {{ $supplierProducts->links('livewire.pagination') }}
                    @endif
                </div>

            </div>
        </div>

    </div>
@script
    <script> 
        $(function() {
            function Slider() {
                // debugger
                $('body').toggleClass('overflow-hidden')
                $('#slideover-customer-container').toggleClass('invisible')
                $('#slideover-customer-bg').toggleClass('opacity-0')
                $('#slideover-customer-bg').toggleClass('opacity-75')
                $('#slideover-customer').toggleClass('translate-x-full')
            }

            $(document).on('click', '.editcustomer', function() {
                $wire.$call('clearFields');
                Slider()
            })

            window.addEventListener('keydown', function(event) {
                debugger
                if (event.key === 'Escape') {
                    if (!$('#slideover-customer-container').hasClass('invisible')) {
                        $wire.$call('clearFields');
                        Slider()
                    } if (!$('#slideover-customer-container').hasClass('invisible')) {
                        $wire.$call('clearFields');
                        Slider()
                    }
                }
            });

            $(document).on('click', '#slideover-customer-child', function() {
                Slider()
            })

            $wire.on('display-message', msg => {
                Slider()
            });

        })
    </script>
@endscript
    
</div>