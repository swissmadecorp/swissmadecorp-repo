<div>
    @if(!request()->query('saved'))
        <div class="flex justify-center">
            <div class="container p-5">
                <h1 class="text-3xl uppercase pb-2">Sell Your Watch</h1>
                <hr>
                <h3 class="text-xl text-center mt-4 mb-4">
                    Sell and/or Trade-in Your Luxury Watch with Swiss Made Corp.
                </h3>

                <div class="bg-gray-400 text-white uppercase p-2 rounded">About you</div>
                <div class="mx-auto grid grid-cols-3 gap-4 p-3">
                    <x-input-standard model="contact.name" label="contact" text="Name" labelfont="text-lg" validation/>
                    <x-input-standard model="contact.email" label="email" text="Email" labelfont="text-lg" validation/>
                    <x-input-standard model="contact.phone" label="phone" text="Phone" labelfont="text-lg" validation/>
                </div>

                <div class="bg-gray-400 text-white uppercase p-2 rounded">About your watch</div>
                <div class="mx-auto grid grid-cols-2 gap-4 p-3">
                    <x-input-standard model="contact.brand" label="brand" text="Brand Name" labelfont="text-lg" validation/>
                    <x-input-standard model="contact.model" label="model" text="Model Number" labelfont="text-lg" validation/>
                </div>

                <div class="p-3">
                    <ul class="grid w-full gap-6 md:grid-cols-2">
                        <x-sell-trade-li label="sell-trade" id="sell-yes" text="Would you like to sell or trade-in your watch?" model="contact.selltrade.{{1}}" subtext="sell my watch" validation />
                        <x-sell-trade-li label="sell-trade" id="sell-no" text="&nbsp;" subtext="trade in" model="contact.selltrade.{{2}}" validation />
                        
                        <x-sell-trade-li label="box" id="box-yes" text="Do you have the original box?" subtext="yes" model="contact.boxpapers.{{1}}" validation />
                        <x-sell-trade-li label="box" id="box-no" text="&nbsp;" subtext="no"  model="contact.boxpapers.{{2}}" validation />

                        <x-sell-trade-li label="certificate" id="cert-yes" text="Do you have the original certificate and/or a warranty card?" subtext="yes" model="contact.cert.{{1}}" validation />
                        <x-sell-trade-li label="certificate" id="cert-no" text="&nbsp;<br>&nbsp;" subtext="no" model="contact.cert.{{2}}" validation />

                        <x-sell-trade-li label="purchased" id="purchased-yes" text="Was it purchased from Swiss Made Corp?" subtext="yes" model="contact.purchased.{{1}}" validation />
                        <x-sell-trade-li label="purchased" id="purchased-no" text="&nbsp;" subtext="no" model="contact.purchased.{{2}}" validation />

                        <x-sell-trade-li label="proof" id="proof-yes" text="Do you have proof of purchase?" subtext="yes" model="contact.proof.{{1}}" validation />
                        <x-sell-trade-li label="proof" id="proof-no" text="&nbsp;" subtext="no" model="contact.proof.{{2}}" validation />

                        <x-sell-trade-li label="unworn" id="unworn-yes" text="Is your watch unworn?" subtext="yes" model="contact.unworn.{{1}}" validation />
                        <x-sell-trade-li label="unworn" id="unworn-no" text="&nbsp;" subtext="no" model="contact.unworn.{{2}}" validation />

                    </ul>

                    <div class="mt-3">
                        <div class="text-lg">How old is the watch?</div>
                        <div for="age" class="inline-flex items-center justify-between w-full text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                            <div class="block w-full">
                                <div class="w-full text-lg font-semibold uppercase">
                                    <select wire:model="contact.age" name="" id="age" class="border-0 focus:ring-0 w-full">
                                        <option value="Less than 2 years">Less than 2 years old</option>
                                        <option value="2 to 3 years">2 to 3 years old</option>
                                        <option value="3 to 5 years">3 to 5 years old</option>
                                        <option value="5 to 10 years">5 to 10 years old</option>
                                        <option value="Over 10 years">Over 10 years old</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-lg">Desired selling amount?</div>
                        <div for="amount" class="inline-flex items-center justify-between w-full text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                            
                            <div class="w-full text-lg font-semibold uppercase flex">
                                <span class="inline-flex items-center px-1 text-sm text-white bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                                <input type="text" id="amount" wire:model="contact.amount" class="block border border-0 flex-1 focus:ring-0 min-w-0 p-2.5 text-gray-900 text-sm w-full">
                            </div>
                        </div>
                        @error('contact.amount')
                            <span class="text-red-500">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="mt-3  mb-4">
                        <div class="text-lg">Any additional information you would like to add?</div>
                        <div for="comment" class="inline-flex items-center justify-between w-full text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
                            
                            <div class="w-full text-lg font-semibold uppercase flex">
                                <textarea id="comment" wire:model="contact.comment" cols="2" rows="5" class="block border border-0 flex-1 focus:ring-0 min-w-0 p-2.5 text-gray-900 text-sm w-full"></textarea>
                            </div>
                            
                        </div>
                    </div>

                    <div class="bg-gray-400 text-white uppercase p-2 rounded">Images of the Watch (Required)</div>
                    Upload up to 8 images of your watch. Each file must not be more than 4MB in size.
                    
                    <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
                        <label class="block mb-2 text-sm font-medium text-white dark:text-white" for="multiple_files">Upload multiple files</label>
                        <input style="width: 113px" wire:model="images" class="block text-sm text-white border border-gray-300 rounded-lg cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/png, image/jpeg" id="multiple_files" type="file" multiple>
                    </div>
                    <div class="image-container" id="image-container">
                        <?php if ($images) {?>
                            @foreach ($images as $image)
                            
                            <div class="image" wire:key="{{$loop->index}}">
                                <button wire:click.prevent="removeImage({{$loop->index}})" tabindex="-1" class="delete-image text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">X</button>
                                <div class="image-title">Image {{ $loop->index + 1 }}</div>
                                <a data-src="{{ $image->temporaryUrl() }}" class="image-item">
                                    <img src="{{$image->temporaryUrl()}}" style="margin: 0 auto;max-width: 150px">
                                </a>
                            </div>
                            @endforeach
                        <?php } ?>
                    </div>
                    @error('images')
                        <span class="block pt-2 text-red-500">{{$message}}</span>
                    @enderror
                    <button wire:loading.attr="disabled" wire:click="save()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Submit your request</button>
                    <div wire:loading class="font-bold text-lg">Processing ...</div>
                </div>
            </div>

        </div>
    @else
        <div class="flex justify-center h-screen">
            <div class="container">
                <h1 class="text-3xl uppercase pb-2">Sell Your Watch</h1>
                <hr>
                <h3 class="text-xl mt-4 mb-4 p-[150px]">
                    Thank you for submitting your information. Someone will contact you soon. In the meantime, you can browse our watch collection.
                </h3>

                <a href="/watch-products" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">← Back to our watch collection</a>

            </div>
        </div>
    @endif

    @section ('jquery')
    <script>
        $(document).ready( function() {

        })

    </script>

    @endsection

</div>
