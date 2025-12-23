<!-- if at home page -->
 
@if (!Request::is('/'))
<button data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-400 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
   <span class="sr-only">Open sidebar</span>
   <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
   <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
   </svg>
</button>

<aside id="sidebar-multi-level-sidebar" class="fixed left-0 z-40 md:pt-[4.9rem] lg:pt-[6.6rem] pt-[5rem] w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full overflow-y-auto bg-gray-50 dark:bg-gray-800">
        <ul class="space-y-2">
            @if (count($routes)>1)
            <li>
                <button type="button" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full" aria-controls="dropdown-models" data-collapse-toggle="dropdown-models">
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Models</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-models">
                    @if (!empty($models))
                    @foreach ($models as $model)
                        <?php $cat = "/watch-products/$model->category_id/".strtolower($model->category_name) ?>
                        <?php if (strpos($cat,' ') !==false) {
                            $cat = str_replace(' ','-',$cat);
                        } ?>
                        
                        <li>
                            <a @click="$dispatch('category-set', { value: 'Hello World!' })" href="<?php echo $cat . '/'.strtolower(str_replace(' ','-',$model->p_model)) ?>" class="dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 pl-[1.5rem] text-gray-900 transition w-full">
                                {{ $model->p_model }}
                            </a>
                        </li>
                    @endforeach
                    @endif
                </ul>
            </li>
            @endif
            <li>
                <button type="button" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full" aria-controls="dropdown-brands" data-collapse-toggle="dropdown-brands">
                    
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Brands</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-brands" class="{{ !empty($models) ? 'hidden' : '' }}">
                    @foreach ($brands as $category)
                        <?php $cat = '/watch-products/'.$category->id ?>
                        <?php if (strpos($cat,' ') !==false) {
                            $cat = str_replace(' ','-',$cat);
                        } ?>
                        
                        <li>
                            <a href="<?php echo $cat . '/'.strtolower($category->location) ?>" class="hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 pl-[1.5rem] text-gray-900 transition w-full">
                                {{ $category->category_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
            <li>
                <button type="button" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full" aria-controls="dropdown-condition" data-collapse-toggle="dropdown-condition">
                    
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Condition</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-condition" class="hidden">
                    @foreach (Conditions() as $condition)
                        @if ($condition != 'Select condition for this product' && $condition != 'New (old stock)')
                        <?php $cat = '/search?p='.htmlspecialchars($condition) ?>
                        
                        <li>
                            <a href="{{strtolower($cat)}}" class="hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 pl-[1.5rem] text-gray-900 transition w-full">
                                {{ $condition }}
                            </a>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </li>

            <li>
                <button type="button" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full" aria-controls="dropdown-gender" data-collapse-toggle="dropdown-gender">
                    
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Gender</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-gender" class="hidden">
                    @foreach (Gender() as $gender)
                        <?php $cat = '/search?p='.$gender ?>
                        
                        <li>
                            <a href="{{$cat}}" class="hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 pl-[1.5rem] text-gray-900 transition w-full">
                                {{ $gender }}
                            </a>
                            <hr>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li>
                <button type="button" class="bg-gray-100 dark:hover:bg-gray-700 dark:text-white duration-75 flex font-medium group items-center p-2 text-base text-gray-900 transition w-full" aria-controls="dropdown-size" data-collapse-toggle="dropdown-size">
                    
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Case Size</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-size" class="hidden">
                    @foreach ($casesizes as $casesize)
                        <?php $cat = '/search?p='.str_replace(' ','',htmlspecialchars($casesize->p_casesize)) ?>
                        
                        <li>
                            <a href="{{$cat}}" class="hover:text-red-600 dark:hover:bg-gray-700 dark:text-white duration-75 flex group hover:bg-gray-100 items-center p-0.5 pl-[1.5rem] text-gray-900 transition w-full">
                                {{ $casesize->p_casesize }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        </div>
    </ul>
</aside>

@endif