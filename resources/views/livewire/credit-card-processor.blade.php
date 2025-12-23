<div class="flex justify-center">
<style>
@keyframes my-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.my-spin { animation: my-spin 1s linear infinite; }
</style>

@if ($order->code && $status !== "Success")
    <div class="container p-5">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <div class="text-3xl">Products</div>
            </div>

            <div>
                <table class="table cart w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr >
                        <th class="py-3 px-6">Image</th>
                        <th class="py-3 px-6">Name</th>
                        <th class="py-3 px-6">Quantity</th>
                        <th class="py-3 px-6">Price</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php $currentRoute = Route::current()->getName();
                        $isUpdateCartVisibile = false;?>

                        @foreach ($order->products as $product)
                        <?php
                            $p_image = $product->images->toArray();
                            if (!empty($p_image)) {
                                $image=$p_image[0]['location'];
                        } else $image = '../no-image.jpg';?>


                        <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                            <td data-label="Image" class="px-8 py-4" style="width: 120px">
                                <img src="/images/{{$image}}" style="width: 50px" />
                            </td>
                            <td data-label="Product Name:" class="py-3 px-6">{{ $product->pivot->product_name }}</td>
                            <td class="align-middle py-3 px-6" data-label="Quantity:">1</td>
                            <td class="align-middle text-right py-3 px-2" data-label="Price:">${{ number_format($product->pivot->price,2) }}</td>
                        </tr>

                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right py-2 px-6">Sub Total:</th>
                            <td  class="text-right py-2 px-2">${{ number_format($orderSum,2) }}</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right py-2 px-6">Tax: </th>
                            <td  class="text-right py-2 px-2">{{$tax}}%</td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right py-2 px-6">Shipping: </th>
                            @if (empty($order->freight))
                            <td  class="text-right freightfield py-2 px-2">$0.00</td>
                            @else
                            <td  class="text-right py-2 px-2">${{ number_format($order->freight,2) }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th colspan="3" class="text-right py-2 px-6">Grand Total:</th>
                            @if (empty($tax))
                                @if (empty($freight))
                                    <td  class="text-right totalfield py-2 px-2">${{ number_format($totalPrice,2) }}</td>
                                @else
                                    <td  class="text-right py-3 px-2">${{ $totalPrice }}</td>

                                @endif
                            @else
                                @if (empty($freight))
                                    <?php $totalPrice ?>
                                @else
                                    <?php $totalPrice ?>
                                @endif
                            <td  class="text-right py-2 px-2">$<?= $totalPrice ?></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>

            </div>

            <div class="mt-6">
                <div class="text-3xl">Payment Information</div>
            </div>
            <div class="md:flex gap-6 mt-6">
                <div class="flex-grow">
                    <div>
                        <x-input-standard model="customer.email" label="email" text="Email Address" validation />
                        <div class="flex gap-2">
                            <x-input-standard model="customer.b_firstname" classMain="w-1/2" label="firstname" text="First Name" validation />
                            <x-input-standard model="customer.b_lastname" classMain="w-1/2" label="lastname" text="Last Name" validation />
                        </div>
                        <x-input-standard model="customer.b_company" label="company" text="Company" />
                        <x-input-standard model="customer.b_address1" label="address1" text="Address 1" validation />
                        <x-input-standard model="customer.b_address2" label="address2" text="Address 2" />
                        <x-input-standard model="customer.b_phone" label="phone" text="Phone" validation />
                        <div wire:ignore class="items-center mb-3">
                            <label for="country" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">Country</label>
                            <select id="country" wire:model.live="selectedCountry" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                @foreach($this->countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <x-input-standard model="customer.b_city" label="city" text="City" classMain="w-1/2"/>
                            <div class="items-center w-1/2">
                                <label for="state" class="block w-32 text-sm font-medium text-gray-900 dark:text-white">State</label>
                                <select id="state" wire:model.live="selectedState" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option></option>
                                    @foreach($this->shippingStates as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-standard model="customer.b_zip" label="zip" text="Zip" classMain="w-1/2"/>
                        </div>

                    </div>
                </div>

                <!-- credit card -->
                <div class="bg-gray-50 border p-3 rounded flex flex-col justify-between min-h-[500px]" id="payment">
                    <div wire:ignore class="card-wrapper"></div>
                    <div class="">
                        <label for="cardname">Name on Card</label>
                        <input type="text" id="cardname" wire:model="customer.cardname" type="text" name="name" class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" autofocus required>
                        @error('customer.cardname')
                        <span class="text-red-500">{{$message}}</span>
                        @enderror
                    </div>
                    <div class="">
                        <label for="cardnumber">Card Number</label>
                        <input name="number" id="cardnumber" placeholder="•••• •••• •••• ••••" wire:model="customer.cardnumber" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                        @error('customer.cardnumber')
                        <span class="text-red-500">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label for="expiry">Expiration date</label>
                            <input placeholder="mm/yy" aria-label="Expiration date, month and year" maxlength="7" name="expiry" id="expiry" type="text" wire:model="customer.cardexp" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                            @error('customer.cardexp')
                            <span class="text-red-500">{{$message}}</span>
                            @enderror
                        </div>
                        <div class="w-1/2">
                            <label for="securitycode">Security code</label>
                            <input placeholder="cvc" type="number" id="securitycode" name="cvc" type="text" wire:model="customer.cardcvc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                            @error('customer.cardcvc')
                            <span class="text-red-500">{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <span wire:loading wire:target="processPayment" class="flex items-center justify-center gap-2 mb-2 text-red-700">
                            <div class="flex">
                                <svg class="my-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                <span>Please wait, processing your card...</span>
                            </div>
                        </span>

                        <input
                            type="button"
                            wire:click="processPayment"
                            class="w-full text-white cursor-pointer bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                            value="Submit Payment"
                        />
                    </div>
                </div>

            </div>
            @error('paymentResponse')
                <span class="mt-1 bg-red-100 block p-3 text-red-500">{{$message}}</span>
            @enderror
        </div>

        @script
            <script>
                $(document).ready( function() {

                    initializeCard()
                    $wire.on('card-reinitialize', () => {
                        // debugger
                        setTimeout(() => {
                            initializeCard();
                        },50)
                    })

                    $wire.on('response', e => {
                        // debugger
                        alert(e);
                    })

                    function initializeCard() {
                        new Card({
                            form: document.getElementById('payment'),
                            container: '.card-wrapper',
                            width: 250,
                            cardSelectors: {
                                cardContainer: '.jp-card-container',
                                card: '.jp-card',
                                numberDisplay: '.jp-card-number',
                                expiryDisplay: '.jp-card-expiry',
                                cvcDisplay: '.jp-card-cvc',
                                nameDisplay: '.jp-card-name'
                            },
                            messages: {
                                validDate: 'expire\ndate',
                                monthYear: 'mm/yy'
                            }
                        });
                    }
                });
            </script>
        @endscript
    </div>
@elseif ($status === "Success")
    <div class="container p-5 ">
        <h3 class="text-3xl uppercase">
                Payment Successful
        </h3>
        <hr class="divider_bg mb-6 h-[0.1rem]">

        <div class="md:p-[150px] pt-10 text-2xl text-center">
            Your payment went through successfully. Please check your email for your invoice receipt.
        </div>
@else
    <div class="container p-5 ">
        <h3 class="text-3xl uppercase">
                Invalid Invoice
        </h3>
        <hr class="divider_bg mb-6 h-[0.1rem]">

        <div class="md:p-[150px] pt-10 text-2xl text-center">
            The invoice you are trying to access is invalid.
        </div>
    </div>
@endif

</div>
