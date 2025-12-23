<?php $image=$product->images()->first(); ?>

<div>
    <div wire:ignore.self id="offer" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-[50rem] max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t bg-gray-700 dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-white dark:text-white">
                        Product offer
                    </h3>
                    <button id="offer-close-button" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="offer">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                 <div>
                    <span class="pl-2 font-medium text-lg">{{$product->title}}</span>
                 </div>
                <div class="md:grid gap-4 mb-4 grid-cols-2 p-4">
                    <div class="col-span-2 sm:col-span-1 flex justify-center border shadow-sm">
                        @if ($image == null)
                            <img id="mainImage" src="/images/no-image.jpg" alt="Main Product Image"
                            class="rounded-lg w-auto cursor-pointer transition-opacity duration-500 ease-in-out h-[14rem]">
                        @else
                        <img id="mainImage" src="/images/{{$image->location}}" alt="Main Product Image"
                            class="rounded-lg w-auto cursor-pointer transition-opacity duration-500 ease-in-out h-[14rem]">
                        @endif
                    </div>
                    <form id="offerForm" x-data="recaptchaOfferHandler()">
                    <div class="col-span-2 sm:col-span-1">
                        <!-- <div class="mb-4"></div> -->
                        <x-input-standard model="customer.contact_name" label="fullname" text="Name" flex validation/>
                        <x-input-standard model="customer.email" label="email" text="Email" flex validation />
                        <x-input-standard model="priceoffer" placeholder="{{number_format($product->web_price,2)}}" label="amount" text="Price offer" flex validation />

                        <div class="flex justify-between">
                            <div>
                                <!-- <div class="g-recaptcha"  data-callback="handle"></div> -->
                                @if(Session::has('fail'))
                                    <span class="font-bold text-red-500" style="display: block;">
                                        <strong>{{ Session::get('fail') }}</strong>
                                    </span>
                                @endif
                                </div>
                            <div>
                                <button id="submitOfferBtn" data-sitekey="{{config('recapcha.key') }}" type="submit" @click.prevent="handleOfferRecaptcha"
                                    class="g-recaptcha text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="1" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Send offer
                                </button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('offer-close-modal', event => {
            const closeButton = document.getElementById('offer-close-button');
                if (closeButton) {
                    alert ('Your email has been sent to an appropriate department. You will be contacted soon. Thank you.')
                    closeButton.click();
                }
        })

        function recaptchaOfferHandler() {
                // Define the recaptcha handler within the Alpine.js scope
                return {
                    handleOfferRecaptcha() {
                        document.getElementById('submitOfferBtn').disabled = true; // Disable button to prevent multiple submissions

                        // Validate form fields using Livewire
                        @this.call('validateForm').then(isValid => {
                            if (isValid) {
                                // Trigger reCAPTCHA after validation passes
                                grecaptcha.ready(function() {
                                    grecaptcha.execute('{{ config('recapcha.key') }}', { action: 'submit' }).then(function(token) {
                                        @this.set('captcha', token);
                                        @this.call('sendOffer',{{$product->id}});
                                    });
                                });
                            } else {
                                document.getElementById('submitOfferBtn').disabled = false; // Re-enable button if validation fails
                            }
                        });
                    }
                };
            }

    </script>

</div>