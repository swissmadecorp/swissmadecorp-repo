<div>
@section('main_header')
<!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@stop

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/jquery.mask.js" type="text/javascript"></script>
<script src="/editable-select/jquery-editable-select.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
@stop

    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
    <div wire:ignore.self id="slideover-product-container" class="fixed inset-0 w-full h-full invisible z-[51]" >
        <div wire:ignore.self id="slideover-product-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div tabindex="0" wire:ignore.self id="slideover-product" class="border absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll md:w-[790px] w-[390px]">
            <div class="bg-gray-200 dark:bg-gray-600 dark:text-gray-300 p-3 text-2xl text-gray-500">
                @if ($productId && !$is_duplicate)
                    Edit Item
                @elseif ($is_duplicate)
                    Duplicate Item
                @else
                    New Item
                @endif
            </div>
            <div @click="isSliderVisible = false" id="slideover-product-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap dark:bg-gray-900 text-sm font-medium text-center" id="default-styled-tab" data-tabs-toggle="#default-styled-tab-content" data-tabs-active-classes="text-purple-600 hover:text-purple-600 dark:text-purple-500 dark:hover:text-purple-500 border-purple-600 dark:border-purple-500" data-tabs-inactive-classes="dark:border-transparent text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300" role="tablist">
                    <li class="me-2" role="presentation">
                        <button wire:ignore.self class="inline-block p-4 border-b-2 rounded-t-lg" id="product-tab" data-tabs-target="#product" type="button" role="tab" aria-selected="true" aria-controls="profile">Product</button>
                    </li>

                    <li x-data="{ ordercount: @entangle('totalorders')}" x-cloak class="me-2" :class="{'hidden': ordercount === 0}"
                        role="presentation">
                        <button wire:ignore.self class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="
                        -tab" data-tabs-target="#invoices" type="button" role="tab" aria-selected="false" aria-controls="invoices" >Invoices</button>
                    </li>
                </ul>
            </div>

            <div id="default-styled-tab-content" class="dark:bg-gray-800">
                <div class="p-4 rounded-lg dark:bg-gray-800" id="product" role="tabpanel" aria-labelledby="product-tab">
                    <form>
                        <div class="text-right text-sm text-gray-600">
                            <div class="dark:text-gray-200">
                                Date: <span>@if ($created_date) {{date("m-d-Y",strtotime($created_date))}} @endif</span>
                            </div>
                        </div>
                        <x-input-standard model="item.title" label="title" text="Title" />
                        <div class="grid gap-2 mb-2 md:grid-cols-2">
                            @if ($productId)
                            <x-input-standard model="item.id" label="pid" text="Stock #" />
                            @endif
                            <div>
                                <div wire:ignore>
                                    <label for="category" class="block text-sm font-medium text-gray-900 dark:text-white">Category</label>
                                    <select id="category" wire:model="category_selected_text" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error("category_selected_text")
                                <span class="text-red-500">{{$message}}</span>
                                @enderror
                            </div>
                            <x-input-standard model="item.p_model" label="modelname" text="Model Name" validation />
                            @if ($groupId == 0)
                            <x-select-standard text="Bezel Material" label="bezelmaterial" model="item.p_bezelmaterial" :iterators="BezelMaterials()" validation />
                            <x-select-standard text="Case Material" label="material" model="item.p_material" :iterators="Materials()" validation />
                            @else
                            <x-select-standard text="Metal Material" label="material" model="item.p_material" :iterators="MetalMaterial()" validation />
                            <div>
                                <div wire:ignore>
                                    <label for="jewelry_type" class="block text-sm font-medium text-gray-900 dark:text-white">Type</label>
                                    <select id="jewelry_type" wire:model="item.jewelry_type" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        @foreach (JewelryType() as $key => $type)
                                            <option value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error("category_selected_text")
                                <span class="text-red-500">{{$message}}</span>
                                @enderror
                            </div>
                            @endif

                            <x-select-standard text="Condition" label="condition" model="item.p_condition" :iterators="Conditions()" validation />

                            @if ($groupId == 0)
                            <x-select-standard text="Strap/Band" label="strapband" model="item.p_strap" :iterators="Strap()" validation />
                            <x-select-standard text="Clasp Type" label="classtype" model="item.p_clasp" :iterators="Clasps()" validation />
                            <x-input-standard model="item.p_casesize" label="casesize" text="Case Size" />
                            <x-input-standard model="item.p_reference" label="reference" text="Reference" />
                            <div class="relative">
                                <span class="absolute block dark:text-white right-2 text-gray-400 text-sm top-1/2">{{ isset($item['serial_code']) ? $item['serial_code'] : '' }}</span>
                                <x-input-standard model="item.p_serial" label="serial" text="Serial #" validation />
                            </div>
                            <x-input-standard model="item.p_color" label="dialcolor" text="Dial Color" validation/>
                            @else
                            <x-input-standard model="item.p_color" label="color" text="Metal Color" validation/>
                            @endif

                            <x-select-standard text="Gender" extraoption label="gender" model="item.p_gender" :iterators="Gender()" validation />
                            @if ($productId)
                                <x-input-standard model="item.slug" label="slug" text="Slug" />
                            @endif

                            <x-input-standard model="item.supplier" label="supplier" text="Supplier Name" validation  />
                            @if ($groupId == 0)
                            <x-input-standard model="item.supplier_invoice" label="supplierinvoice" text="Supplier Invoice #" />
                            @endif
                            <div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-900 dark:text-white">Status</label>
                                    <select id="status" wire:model.live="status" wire:change="status" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option value="-1"></option>
                                        @foreach (Status() as $key => $stats)
                                            <?php $exclude = [6,11] ?>
                                            @if (!in_array($key,$exclude))
                                            <option value="{{ $key }}">{{ $stats }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                @error("status")
                                <span class="text-red-500">{{$message}}</span>
                                @enderror

                                <?php if($status == '9') { ?>
                                    <!-- triggers when option is selected and $status, which is a componenet variable, is equal to 9 (repair)
                                        This is for future use.
                                        The select must be wire:model.live to component status and wire:change must be equal to status
                                        -->
                                <?php } ?>
                            </div>
                            @if ($groupId == 0)
                            <x-input-standard model="item.water_resistance" label="waterresistance" text="Water Resistance" validation/>
                            <x-input-standard model="item.p_year" label="productyear" text="Product Year" />
                            <x-input-standard model="item.bezel_features" label="bezelfeatures" text="Bezel Features" />
                            <x-select-standard text="Movement" extraoption label="movement" model="item.movement" :iterators="Movement()" validation />
                            <x-select-standard text="Dial Style" label="dialstyle" model="item.p_dial_style" :iterators="DialStyle()" validation />
                            @endif

                            <?php
                                if ($groupId == 0) {
                                    for ($i=0; $i < count($custom_columns);$i++) {
                                        $column = $custom_columns[$i];?>
                                        <div>
                                            <label for="{{$column}}-input" class="block text-sm font-medium text-gray-900 dark:text-white">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                                            <input id="{{$column}}-input" wire:model="item.{{$column}}" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  />
                                        </div>
                                    <?php }
                                }?>

                        </div>
                        <div class="grid gap-2 md:grid-cols-2">
                            <div class="flex items-center mt-3 ps-4 border border-gray-200 rounded dark:border-gray-700">

                                <input id="box" <?= (isset($item['p_box']) && $item['p_box']==1) ? 'checked' : "" ?> type="checkbox" wire:model="item.p_box" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="box" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Box</label>
                            </div>
                            <div class="flex items-center mt-3 ps-4 border border-gray-200 rounded dark:border-gray-700">
                                <input id="papers" type="checkbox" wire:model="item.p_papers" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="papers" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Papers</label>
                            </div>
                            <div>
                                <label for="smdescription" class="block text-sm font-medium text-gray-900 dark:text-white">Small Description</label>
                                <textarea id="smdescription" rows="4" wire:model="item.p_smalldescription" class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>
                            </div>
                            <div>
                                <label for="lgdescription" class="block text-sm font-medium text-gray-900 dark:text-white">Long Description</label>
                                <textarea id="lgdescription" rows="4" wire:model="item.p_longdescription" class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>
                            </div>
                        </div>
                        <div class="grid gap-2 md:grid-cols-3 mt-2">
                            <div>
                                <label for="cost" class="block text-sm font-medium text-gray-900 dark:text-white">Cost</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-1 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-100"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" id="cost" wire:model="item.p_price" class="block border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400 dark:text-white flex-1 focus:border-blue-500 focus:ring-blue-500 min-w-0 p-2.5 rounded-none text-gray-900 text-sm w-full" placeholder="0.00">
                                    <button type="button" wire:click.prevent="additionalCostDispatcher()" class="transition duration-300 ease-in-out hover:bg-[color:#FF5733] bg-gray-200 hover:text-white inline-flex items-center px-1 text-gray-900 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                                        </svg>
                                    </button>
                                </div>
                                @error("item.p_price")
                                <span class="text-red-500">{{$message}}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-900 dark:text-white">Price</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-1 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-100"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" id="price" wire:model.live="newprice" class="rounded-none rounded-e-lg border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label for="retail" class="block text-sm font-medium text-gray-900 dark:text-white">Retail</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-1 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-100"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" id="retail" wire:model="item.p_retail" class="rounded-none rounded-e-lg border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="0.00">
                                </div>
                            </div>
                            @if ($productId)
                            <div>
                                <label for="websiteprice" class="block  mt-2 text-sm font-medium text-gray-900 dark:text-white">Website Price</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-1 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-100"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" id="websiteprice" wire:model="item.web_price" class="rounded-none rounded-e-lg border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label for="c24price" class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Chrono24 Price</label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-1 text-sm text-gray-900 bg-gray-200 border rounded-e-0 border-gray-300 border-e-0 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                        <svg class="h-6 w-6 text-gray-600 dark:text-gray-100"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <input pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" id="c24price" wire:model="item.p_price3P" class="rounded-none rounded-e-lg border text-gray-900 focus:ring-blue-500 focus:border-blue-500 block flex-1 min-w-0 w-full text-sm border-gray-300 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="0.00">
                                </div>
                            </div>
                            @endif
                            <div>
                                <label for="qty" class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">On Hand</label>
                                <input id="qty" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" type="number" wire:model="item.p_qty" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="1"  />
                                @error("item.p_qty")
                                <span class="text-red-500">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="comments" class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Comments</label>
                            <textarea id="comments" rows="4" wire:model="item.p_comments" class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>
                        </div>

                        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="multiple_files">Upload multiple files</label>
                            <input style="width: 113px" wire:model="images" class="block text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/png, image/jpeg" id="multiple_files" type="file" multiple>
                        </div>
                        <div class="image-container bg-gray-500 dark:bg-gray-600" id="image-container">
                            @role('superadmin|administrator')
                            <button id="addimage"  type="button" wire:click.prevent="insertExistingImage()" class="absolute bg-gray-700 opacity-50 hover:opacity-100 rounded-b-[1rem] text-white transition-opacity p-2 z-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                            @endrole
                            <?php if ($images) {?>
                                <?php $i=0; ?>
                                @foreach ($thumbnails as $image)
                                <div class="image flex flex-col items-center justify-between relative h-full" wire:key="{{$loop->index}}">
                                    @role('superadmin|administrator')
                                    <button wire:click.prevent="removeImage({{$loop->index}})" tabindex="-1" class="delete-image self-end z-50 text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-blue-800">X</button>
                                    @endrole

                                    @if (!$image['id'])
                                        <div class="image-title text-center z-10 dark:bg-gray-600 dark:text-gray-200">Image</div>
                                            <a data-src="{{ $image['path']->temporaryUrl() }}" class="image-item flex-1 w-full flex items-center justify-center overflow-hidden">
                                                <img src="{{$image['path']->temporaryUrl()}}" class="h-full w-auto object-contain">
                                            </a>
                                    @else
                                        <div class="image-title text-center z-10 dark:bg-gray-600 dark:text-gray-200">{{$image['id']}}</div>
                                        <a data-src="{{ $image['path'] }}" class="flex-1 w-full flex items-center justify-center overflow-hidden">
                                            <img src="{{$image['path']}}" class="h-full w-auto object-contain">
                                        </a>
                                    @endif
                                    <input wire:model.live="item.position.{{$i}}" value="{{$i}}" class="border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                    <?php $i++ ?>
                                </div>
                                @endforeach
                            <?php } elseif ($thumbnails) { ?>
                                <?php $i=0; ?>
                                @role('superadmin|administrator')
                                <button id="addimage"  type="button" wire:click.prevent="insertExistingImage()" class="absolute bg-gray-700 opacity-50 hover:opacity-100 rounded-b-[1rem] text-white transition-opacity p-2 z-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                                @endrole

                                @foreach ($thumbnails as $image)
                                <div class="image flex flex-col items-center justify-between relative h-full" wire:key="{{$loop->index}}">
                                    <div class="image-title text-center z-10 dark:bg-gray-600 dark:text-gray-200">{{$image['id']}}
                                        @role('superadmin|administrator')
                                        @if ($is_duplicate)
                                            <button wire:click.prevent="removeImage({{$loop->index}})" class="delete-image text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-blue-800">X</button>
                                        @else
                                            <button wire:confirm="This image will be deleted permanently.Are you sure you want to do this?" tabindex="-1" wire:click.prevent="removeImage({{$loop->index}})" class="delete-image text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-blue-800">X</button>
                                        @endif
                                        @endrole
                                    </div>
                                    <div class="image-item flex-1 w-full flex items-center justify-center overflow-hidden" data-src="{{ $image['main'] }}">
                                        <img src="{{$image['path']}}" class="w-full h-auto object-contain " >
                                    </div>
                                    <input wire:model.live="item.position.{{$i}}" value="{{$image['position']}}" class="position-box border border-gray-300 text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                    <?php $i++ ?>
                                </div>
                                @endforeach
                            <?php } ?>
                        </div>

                        @if ($productId && $is_duplicate==0)
                        <div class="flex justify-between">
                            @role('superadmin|administrator')
                                <button wire:click="saveProduct()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Update Product</button>
                            @endrole
                            <button onclick="window.open('/admin/products/{{$productId}}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Print Tag</button>
                        </div>
                        @elseif($is_duplicate)
                            @role('superadmin|administrator')
                                <button id="duplicate" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Create Duplicate</button>
                            @endrole
                        @else
                            @role('superadmin|administrator')
                                <button wire:click="saveProduct()" type="button" class="text-white mt-4 bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-blue-800">Save Product</button>
                            @endrole
                        @endif

                    </form>
                </div>
                <div class="hidden p-4 rounded-lg dark:bg-gray-800" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        @if ($totalorders)
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Invoice Id</th>
                                    <th scope="col" class="px-6 py-3">Customer</th>
                                    <th scope="col" class="px-6 py-3">Invoice</th>
                                    <th scope="col" class="px-6 py-3">Date Sold</th>
                                    <th scope="col" class="px-6 py-3">Serial #</th>
                                    <th scope="col" class="px-6 py-3">Sold Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $invoice)
                                <?php
                                    $product=$invoice->products->find($product->id);

                                    $colColor = '';
                                    foreach ($invoice->returns as $return) {
                                        if ($return->pivot->product_id==$product->id) {
                                            $colColor = "#f5bdada6";
                                        }
                                    }
                                ?>
                                <tr x-data="{colColor: @js($colColor)}"
                                    :class="colColor!=='' ? 'bg-red-100 odd:dark:bg-red-900 border-b dark:border-red-700' : 'hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700'"

                                    class="border-b dark:border-gray-700">

                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                        <a @click="$dispatch('load-invoice', { id: {{$product->pivot->order_id}} })" data-id="{{$product->pivot->order_id}}" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600">{{$product->pivot->order_id}}</a>
                                    </th>
                                    <td class="px-6 py-4">{{ $invoice->customers->first()->company }}</td>
                                    <td class="px-6 py-4">{{ $invoice->method }}</td>
                                    <td class="px-6 py-4">{{ $invoice->created_at->format('m/d/Y')}}</td>
                                    <td class="px-6 py-4">{{ $product->pivot->serial }}</td>
                                    <td class="px-6 py-4 text-right"><?= $colColor ? "-" : '' ?>${{ number_format($product->pivot->price,2) }}</td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>

                        @else
                            <div class="font-medium p-5">No Invoice Found</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

@script
    <script>
        $(function() {

            var mainPath = "{{route('get.customer.byID')}}";
            // var isSliderVisible = false;

            function Slider() {
                $('body').toggleClass('overflow-hidden')
                $('#slideover-product-container').toggleClass('invisible')
                $('#slideover-product-bg').toggleClass('opacity-0')
                $('#slideover-product-bg').toggleClass('opacity-20')
                $('#slideover-product').toggleClass('translate-x-full')
                if (!$('#slideover-product-container').hasClass('invisible')) {
                    setTimeout(() => {
                        $('#title').focus();
                    }, "400");

                } else {
                    $('#table-search').focus();
                }
            }

            $wire.on('swalAddToCost', msg => {
                Swal.fire({
                    title: msg[0].msg,
                    html:
                    '<div class="space-y-2"><input value="'+msg[0].input1+'" id="swal-input1" type="number" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Additional Cost">' +
                    '<input id="swal-input2" value="'+msg[0].input2+'" class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Notes"></div>',
                    toast: true,
                    showCancelButton: true,
                    confirmButtonText: 'Apply',
                    preConfirm: () => {
                        const input1Value = document.getElementById('swal-input1').value;
                        const input2Value = document.getElementById('swal-input2').value;

                        if (!input1Value.trim()) {
                            Swal.showValidationMessage('Cost value cannot be empty.');
                            return false;
                        }
                        const confirmed = confirm(`Do you want to add "${input1Value}" to the cost?`);

                        if (confirmed) {
                            return [input1Value, input2Value];
                        } else {
                            return [null, input2Value]; // Return null for input1Value
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.dispatch('additional-cost', {input1: result.value[0],input2: result.value[1]});
                    }
                });
            });

            $wire.on('swalInput', msg => {
            debugger
                Swal.fire({
                    title: msg,
                    input: 'text',
                    toast: true,
                    showCancelButton: true,
                    confirmButtonText: 'Apply',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Please enter id number or cancel.';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.dispatch('input-confirmed', {data: result.value, from: 'insertimage'});
                    }
                });
            });

            $('#status').change( function() {
                if ($(this).val() == '9'){
                    productId =  $wire.$get('productId')

                    Livewire.dispatch('current-productid', { id: productId })
                    $wire.$set('productId', productId)
                    displayModalBox()
                }
            })

            $(document).on('click', '#duplicate', function(e) {
                if (checkforBoxPapers())
                    $wire.$call('saveProduct');
                else {
                    if (confirm("Box or Papers have not been selected. Are you sure you want to proceed?"))
                        $wire.$call('saveProduct');
                    else e.preventDefault()
                }
            })

            function checkforBoxPapers() {
                var box = $('#box').is(':checked');
                var papers = $('#papers').is(':checked');

                if (!box && !papers)
                    return false;
                else return true;
            }

            $wire.on('display-message', msg => {
                // Clears editable select. There is no native function so here's a workaround
                if (!$wire.$get('category_selected_id')) {
                    $wire.$set('category_selected_text', $('#category').val())
                }
                $('#category').val('');
                $('.es-list li').show();
                $('.es-list li').removeClass('selected')
                $('.es-list li').addClass('es-visible')
                $('.es-list li:first').addClass('es-visible selected')
                // debugger

                if (typeof msg[0].hide === typeof undefined)
                    hide = 1
                else hide = msg[0].hide

                if (hide==1)
                    Slider()

                if (msg[0].is_duplicate) {
                    $wire.$dispatch('set-onhand-page');
                    $('#radio-onhand1').prop('checked',1)
                    if (confirm("Would you like to print a label?")) {
                        window.open('/admin/products/'+msg[0].id+'/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;
                    }
                }
            });

            $(document).on('click', '.editproduct', function() {
                if ($('#slideover-product-container').hasClass('invisible')==true) {
                    $wire.$call("editItem", $(this).attr('data-id'))
                    if ($(this).attr('data-duplicate'))
                        $wire.$set('is_duplicate', $(this).attr('data-duplicate'))

                    Slider()

                    setTimeout(() => {
                            lightGallery(document.getElementById('image-container'), {
                            plugins: [lgZoom, lgThumbnail],
                            licenseKey: '07A02847-376B-466B-993D-72DCB1E03226',
                            selector: '.image-item',
                        });
                    }, 1000);

                    if (!$('#slideover-invoice-container').hasClass('invisible')) {
                        $('#slideover-product-container').css('z-index',51)
                            $('#invoices-tab').hide();
                    } else {
                        $('#invoices-tab').show();
                        const productTabButton = document.getElementById('product-tab');
                        if (productTabButton) {
                            productTabButton.click();
                        }
                    }
                }
            })

            window.closeFields = function() {
                // This insures that the light gallery is destroyed when the slider is closed
                // because it creates multiple instances.
                if ($('[id^=lg-container-]').length) {
                    $('[id^=lg-container-]').remove();
                }
                Slider()
                $wire.$call('clearFields')
                const productTabButton = document.getElementById('product-tab');
                if (productTabButton) {
                    productTabButton.click();
                }
            }

            $('#slideover-product-child').click(function() {
                closeFields();
            })

            $('.newproduct').click(function() {
                if ($(this).text() == "Watch")
                    groupId = 0
                else if ($(this).text() == "Jewelry")
                    groupId = 1
                else groupId = 2

                $wire.$set('groupId',groupId);
                Slider()
            })

            $('#supplier').devbridgeAutocomplete({
                serviceUrl: mainPath,
                showNoSuggestionNotice : true,
                minChars: 3,
                zIndex: 900,
                params:{addParam: 'justCompany'},
                onSelect: function (suggestion) {
                    $wire.$set('item.supplier',suggestion.value);
                }
            });

            $('#category').editableSelect({ effects: 'fade' })
                .on('select.editable-select', function (e, li) {
                    $wire.$set('category_selected_id',li.val());
                    $wire.$set('category_selected_text',li.text());
            });

        })
    </script>
@endscript

</div>