<div>    

<!-- if at home page -->
    <aside id="sidebar-multi-level-sidebar" class="fixed left-0 md:z-0 z-40 sm:pt-[9rem] w-64 transition-transform -translate-x-full sm:translate-x-0" style="height: 100%">
        <div class="h-full overflow-y-auto bg-gray-50 dark:bg-gray-800">
            <ul>
                <li x-data="{selectedCategory: @entangle('brand'), selectedModel: @entangle('model') }" :class="{'hidden': !selectedCategory}">
                    <button id="models" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full">
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">{{$brand}}</span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <ul class="pl-4 pr-4">
                        @if (!empty($models))
                        @foreach ($models as $model)
                            <li>
                                <a wire:click.prevent="setModel('{{$model->p_model}}')" class="cursor-pointer hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 block group hover:bg-gray-100 items-center p-0.5 text-gray-900 transition w-full truncate line-clamp-6">
                                    {{ $model->p_model }}
                                </a>
                            </li>
                        @endforeach
                        @endif
                    </ul>
                </li>

                <li x-data="{selectedCategory: @entangle('brand') }" :class="{'mt-2': selectedCategory}">
                    <button id="categories" class="bg-gray-100 dark:hover:bg-gray-700 duration-75 transition dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full">
                        
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Brands</span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <ul :class="{'hidden': selectedCategory}" class="pl-4 pr-4">
                        @foreach ($categories as $category)
                            <li>
                                <a wire:click.prevent="setCategory({{ $category->id }})" :class="{'text-red-500 font-bold': selectedCategory === @js($category->category_name)}" class="cursor-pointer hover:text-red-600 duration-75 transition dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 text-gray-900 transition w-full">
                                    {{ $category->category_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li>
                    <button id="conditions" class="mt-2 bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full">
                        
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Condition</span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <ul class="hidden pl-4 pr-4">
                        @foreach (Conditions() as $condition)
                            @if ($condition != 'Select condition for this product' && $condition != 'New (old stock)')
                            <?php $cat = htmlspecialchars($condition) ?>
                            
                            <li>
                                <a wire:click.prevent="setCondition('{{strtolower($cat)}}')" class="cursor-pointer hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 text-gray-900 transition w-full">
                                    {{ $condition }}
                                </a>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                </li>

                <li>
                    <button id="genders" class="mt-2 bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full">
                        
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Gender</span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <ul class="hidden pl-4 pr-4">
                        @foreach (Gender() as $gender)
                            <?php $cat = strtolower($gender) ?>
                            
                            <li>
                                <a wire:click.prevent='setGender("{{$cat}}")' class="cursor-pointer hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 text-gray-900 transition w-full">
                                    {{ $gender }}
                                </a>

                            </li>
                        @endforeach
                    </ul>
                </li>

                <li>
                    <button id="sizes" class="mt-2 bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full">
                        
                        <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Case Size</span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <ul class="hidden py-2 space-y-2 pl-4 pr-4">
                        @foreach ($this->casesizes as $casesize)
                            <?php $cat = htmlspecialchars($casesize->p_casesize); //'/search?p='.str_replace(' ','',htmlspecialchars($casesize->p_casesize)) ?>
                            
                            <li>
                                <a wire:click.prevent='setCasesize("{{$cat}}")' class="cursor-pointer hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 text-gray-900 transition w-full">
                                    {{ $casesize->p_casesize }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </div>
        </ul>
    </aside>

<script> 
    $(function() {
        dropdown = $('#dropdown-brands');
        dropdown.removeClass('hidden');

        $(document).on('click', '#sidebar-multi-level-sidebar a', function(event) {
            // Livewire.dispatch('slider-clicked')

            $('div[drawer-backdrop]').click()
            
            window.scrollTo({
                top: 0, // Y-coordinate
                behavior: 'smooth' // Smooth scroll
            });
                
        })

        $('#models, #categories, #genders, #sizes, #conditions').click(function(event) {
            event.preventDefault();

            var dropdown = $(this).next('ul')
            dropdown.animate({
                opacity: 'toggle',
                height: 'toggle'
            }, 400, function() {
                // Animation complete.
            });
        });
    })
        
</script>

    
</div>
