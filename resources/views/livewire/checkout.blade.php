<div>
    <div wire:loading.delay class="fixed z-50">
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

    <div wire:ignore.self class="flex justify-center">
        <div x-data="{currentpage: @entangle('currentPage')}" class="md:container p-5">
            <h3 class="text-3xl uppercase">
                
                @if ($currentPage == 1)
                    Checkout
                @elseif ($currentPage == 2)
                    Billing Information
                @elseif ($currentPage == 3)
                    Payment Information
                @elseif ($currentPage == 4)
                    Verify your order
                @else 
                    Thank you for your order
                @endif
            </h3>
            <hr class="divider_bg mb-6 h-[0.1rem]">
            
            @if($products)
                @if ($currentPage < 5 )
                    <div class="stepper-wrapper">
                        <div class="stepper-item" :class="currentpage === 1 ? 'active' : 'completed'">
                            <div class="step-counter">1</div>
                            <div class="step-name">Shopping Cart</div>
                        </div>
                        <div class="stepper-item" :class="{'': currentpage < 2,'active': currentpage === 2, 'completed': currentpage > 2}" >
                            <div class="step-counter">2</div>
                            <div class="step-name">Shipping Information</div>
                        </div>
                        <div class="stepper-item" :class="{'': currentpage < 3,'active': currentpage === 3, 'completed': currentpage > 3}" >
                            <div class="step-counter">3</div>
                            <div class="step-name">Payment Information</div>
                        </div>
                        <div class="stepper-item" :class="{'': currentpage < 4,'active': currentpage === 4, 'completed': currentpage > 4}" >
                            <div class="step-counter">4</div>
                            <div class="step-name">Order Confirmation</div>
                        </div>
                    </div>
                @endif

                <div :class="{'md:flex gap-6 items-start': currentpage === 2}">
                    @if ($currentPage === 1)
                        <div class="mt-6">
                            @include ('carttemplate-new')
                        </div>
                    @elseif ($currentPage === 2)
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

                        <div class="flex-grow mt-6">
                            @include ('carttemplate-new',get_defined_vars())
                        </div>  
                    @elseif ($currentPage === 3) 
                        
                        <!-- payment options -->
                        <div class="pt-4">
                            <h3 class="text-2xl">Payment Options</h3>
                            <div class="col-6">
                                <label>
                                    <input id="cc" type="radio" checked wire:model.live="paymentOption" name="payment_options" value="1" >
                                    <strong>Credit Card</strong>
                                </label>
                            </div>
                            
                            <div class="col-6">
                                <label>
                                    <input id="wire" type="radio" wire:model.live="paymentOption" name="payment_options" value="2">
                                    <strong>Bank Wire</strong>
                                </label>
                            </div>
                        </div>

                        <div class="md:flex gap-6 mt-6">
                            <div class="flex-grow">
                                @include ('carttemplate-new')
                            </div>

                            <!-- wire transfer -->
                            @if ($paymentOption == 2)
                            <div class="bg-gray-50 border p-3 rounded">
                                <h4 class="text-xl pb-5">Our Wire Transfer Information</h4>
                                <b>SWISS MADE CORP</b><br>
                                15 West 47th Street<br>
                                Suite 503<br>
                                New York, NY 10036<br><br>

                                <b>Bank of America</b><br>
                                550 5th Avenue<br>
                                New York, NY 10036<br>
                                Routing #: 021000322<br>
                                Account#: 483082594737<br>
                                US Wire Code: 026009593<br>
                                International Swift Code (IN US DOLLARS): BOFAUS3N
                            </div>
                            @else
                            <!-- credit card -->
                            <div class="bg-gray-50 border p-3 rounded" id="payment" >
                                <div class="card-wrapper"></div>
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
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end pt-4">
                            <button wire:click.prevent="PreviousStep()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                                <i class="fas fa-angle-double-left"></i>
                                Previous Page
                            </button>
                            <button wire:click.prevent="OrderConfirmation()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                                Order confirmation
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    @elseif ($currentPage === 4)
                        @inject('countries','App\Libs\Countries')
                        
                        <div class="bg-gray-50 gap-[12rem] items-start md:flex p-2 rounded-md w-full">
                            <div>
                                <h3 class="text-lg font-bold">Billing Address</h3>
                                <?php if (isset($customer['b_company']) && $customer['b_company']) {?>
                                    {{ $customer['b_company'] }}<br>
                                <?php } ?>
                                {{ $customer['b_firstname'] }} {{ $customer['b_lastname'] }}<br>
                                {{ $customer['b_address1'] }}<br>
                                <?php if (!empty($customer['b_address2'])) {?>
                                    {{ $customer['b_address2'] }}<br>
                                <?php } ?>
                                {{ $customer['b_phone'] }}<br>
                                <?php 
                                    $zip = ""; $city="";
                                    if ($selectedCountry == 231) {
                                        $zip = $customer['b_zip'];
                                        $city = $customer['b_city'];
                                    }
                                 ?>
                                
                                {{ $city }}, {{ $countries->getStateByCode($selectedState) }} {{ $zip }}<br>
                                {{ $countries->getCountry($selectedCountry) }}<br>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold">Shipping Address</h3>
                                <?php if (!empty($customer['b_company'])) {?>
                                    {{ $customer['b_company'] }}<br>
                                <?php } ?>
                                {{ $customer['b_firstname'] }} {{ $customer['b_lastname'] }}<br>
                                {{ $customer['b_address1'] }}<br>
                                <?php if (!empty($customer['b_address2'])) {?>
                                    {{ $customer['b_address2'] }}<br>
                                <?php } ?>
                                <?php 
                                    $zip = ""; $city="";
                                    if ($selectedCountry == 231) {
                                        $zip = $customer['b_zip'];
                                        $city = $customer['b_city'];
                                    }
                                 ?>
                                {{ $customer['b_phone'] }}<br>
                                {{ $city }}, {{ $countries->getStateByCode($selectedState) }} {{ $zip }}<br>
                                {{ $countries->getCountry($selectedCountry) }}<br>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-2xl">Payment Option</h3>
                            @if ($customer['paymentOption'] == 1)
                                <?php if (!empty($customer['cardnumber'])) { ?>
                                    Credit Card ending in - {{ substr($customer['cardnumber'],-4) }}
                                <?php } ?>
                            @else 
                                Wire Transfer
                            @endif
                        </div>

                        <div class="mt-6">
                            @include ('carttemplate-new')
                        </div>
                    @elseif ($currentPage === 5) 

                    @endif
                </div>

                <!-- Buttons -->
                @if ($currentPage === 1)    
                    <button wire:click.prevent="NextStep()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">Checkout
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                @elseif ($currentPage === 2)
                    <div class="flex justify-end pt-4">
                        <button wire:click.prevent="PreviousStep()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                            <i class="fas fa-angle-double-left"></i>
                            Previous Page
                        </button>
                        <button wire:click.prevent="NextStep()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">Continue to Payment
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                @elseif ($currentPage === 4)
                    <h3 class="font-bold p-5 text-2xl text-center text-red-500">{{ Session::get('message') }}</h3>
                    <div class="flex justify-end pt-4">
                        <button wire:click.prevent="PreviousStep()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                            <i class="fas fa-angle-double-left"></i>
                            Previous Page
                        </button>
            
                        <button wire:click.prevent="finalizePurchase()" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                            Finalize your Order
                        </button>
                    </div>
                @endif
            
        </div>
    @else
        <h4 class="md:p-[150px] pt-10 text-2xl text-center">Your cart is empty.</h4>
    </div>
    @endif

@script
    <script>
        
        $(document).ready( function() {
            $wire.on('card-reinitialize', () => {
                // debugger
                setTimeout(() => {
                    initializeCard();
                },50)
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
        }) 
    </script>
@endscript

</div>