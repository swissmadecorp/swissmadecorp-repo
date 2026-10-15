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

                <div class="p-3" x-data="{ uploading: false }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-cancel="uploading = false">
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
                    Select 5 to 10 JPEG or PNG photos, up to 5 MB each. Preview and remove photos below, then submit your request to upload all remaining photos.
                    
                    <div class="py-4">
                        <button type="button" x-on:click="$refs.watchPhotos.click()" wire:loading.attr="disabled" x-bind:disabled="uploading" class="text-sm text-gray-900 border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 hover:bg-gray-200">Choose Files</button>
                        <input x-ref="watchPhotos" wire:key="watch-photo-input-{{ $imageInputVersion }}" wire:model="pendingImages" wire:loading.attr="disabled" x-bind:disabled="uploading" style="display: none;" aria-label="Choose watch photos" accept="image/png, image/jpeg" id="multiple_files" type="file" multiple>
                    </div>
                    <p x-show="uploading" x-cloak role="status">Preparing photo previews. Please wait...</p>
                    <div id="image-container" role="group" aria-label="Selected watch photos" style="display: flex; flex-wrap: wrap; gap: 16px; min-height: 190px; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; background: #f3f4f6;">
                        @foreach ($images as $index => $image)
                            <div class="border border-gray-300 rounded-lg p-2" wire:key="watch-photo-{{ $image->getFilename() }}">
                                <img src="{{ $image->temporaryUrl() }}" alt="Watch photo {{ $loop->iteration }}" class="object-contain" style="width: 150px; height: 150px;">
                                <div class="text-sm text-center">Photo {{ $loop->iteration }}</div>
                                <button type="button" wire:click="removeImage({{ $index }})" wire:loading.attr="disabled" x-bind:disabled="uploading" aria-label="Remove photo {{ $loop->iteration }}" class="mt-2 w-full text-white bg-blue-700 hover:bg-blue-800 rounded-lg text-sm px-3 py-2">Remove</button>
                            </div>
                        @endforeach
                    </div>
                    @error('images')
                        <span class="block pt-2 text-red-500">{{$message}}</span>
                    @enderror
                    @foreach (['images.*', 'pendingImages', 'pendingImages.*'] as $photoError)
                        @foreach ($errors->get($photoError) as $messages)
                            @foreach ((array) $messages as $message)
                                <span class="block pt-2 text-red-500" role="alert">{{ $message }}</span>
                            @endforeach
                        @endforeach
                    @endforeach
                    <button wire:loading.attr="disabled" x-bind:disabled="uploading" wire:click="save()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Upload photos and submit your request</button>
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
