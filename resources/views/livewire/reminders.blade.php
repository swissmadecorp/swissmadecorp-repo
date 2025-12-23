<div>

    <div x-data class="flex bg-gray-200 dark:bg-gray-900 rounded-lg h-[3rem]">
        <div class="w-full inline-flex rounded-lg shadow">
            <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white">Reminders</h1>
        </div>
    </div>
    
    <!-- Page Header -->
    @if (session()->has('message'))
        <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                @if (is_array(session('message')))
                    {{ session('message')['msg'] }}
                @else
                    {{ session('message') }}
                @endif
            </div>
            <button type="button" @click="dismiss()" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @elseif (session()->has('error'))
        <div id="alert-border-1" class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:border-red-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button type="button" @click="dismiss()" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"  data-dismiss-target="#alert-border-2" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @endif

    <div class="relative sm:rounded-lg" id="reminder_container">
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
            <button wire:click="newReminder()" id="new_reminder" type="button" class="m-2 text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">New</button>

            <div class="relative mt-1">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" x-ref="searchbox" wire:model.live.debounce.150ms="search" id="table-search" class="block h-10 ps-10 text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search for items">
                </div>
            
        </div>
            
        <!-- wire:poll.15s.visible -->
        <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" style="width: 40px" class="px-3 py-3">Page</th>
                    <th scope="col" class="cursor-pointer px-3 py-3">Criteria</th>
                    <th scope="col" class="px-3 py-3">Assigned To</th>
                    <th scope="col" class="px-3 py-3">Location</th>
                    <th scope="col" class="px-3 py-3">Action</th>
                    <th scope="col" class="px-3 py-3"></th>
                </tr>
            </head>
            <tbody>
            
            <?php $counter = 0 ?>
            
            @foreach($reminders as $reminder)
            <tr wire:key="reminder-{{ $reminder->id }}" wire:click.prevent="loadReminder({{$reminder->id}})" class="cursor-pointer relative odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                <td class="px-3 py-2">
                    {{ $reminder->pagename }}
                    
                </td>
                <td class="px-3 py-2 w-full">{{ $reminder->criteria }}</td>
                <td class="px-3 py-2 w-24">{{ $reminder->assigned_to }}</td>
                <td class="px-3 py-2 w-24">{{ $reminder->location }}</td>
                <td class="px-3 py-2 w-24">{{ $reminder->action }}</td>
                <td class="px-3 py-2 w-24">
                    <button wire:confirm="Are you sure you want to delete this reminder?" onclick="event.stopPropagation()" type="button" wire:click.prevent="deleteReminder({{$reminder->id}})" class="focus:outline-none text-white bg-red-800 hover:bg-red-600 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Remove</button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>    
    
    </div>
    
    <div wire:ignore id="child-box" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <!-- Background overlay -->
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <!-- Modal content -->
        <div class="relative z-10 w-full flex justify-center">
            <!-- Your actual modal box goes here -->
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl">
            <div class="bg-gray-100 mb-3 p-2 text-2xl dark:text-gray-200 dark:bg-gray-600"><h3>Edit Reminder</h3></div>
                    <x-input-standard model="reminder.pagename" label="pagename" text="Page Name" flex customValidation/>
                    <x-input-standard model="reminder.criteria" label="criteria" text="Criteria" flex customValidation />
                    <x-input-standard model="reminder.assigned_to" label="assigned_to" text="Assign To" flex customValidation />
                    <x-input-standard model="reminder.location" label="location" text="Request From" flex customValidation />
                    <x-input-standard model="reminder.action" label="action" text="Action" flex customValidation />
                    
                    <div class="pb-2.5">
                        <div class="flex items-center">
                            <label for="condition" class="block font-medium text-sm text-gray-900 dark:text-white w-32">Condition</label>
                            <select id="condition" class="chosen-select" wire:model="reminder.product_condition" multiple>
                                @foreach (Conditions() as $key => $condition)
                                <option value="{{ $key }}">{{ $condition }}</option>
                                @endforeach
                            </select>
                            
                        </div>
                    </div>

                    <div class="pb-2.5">
                        <div class="flex items-center">
                            <label for="boxpapers" class="block font-medium text-sm text-gray-900 dark:text-white w-32">Box / Papers</label>
                            <select id="boxpapers" class="chosen-select" wire:model="reminder.boxpapers" multiple>
                                <?php foreach (['Box','Papers'] as $key => $boxpaper) {?>
                                    <option value="{{ $boxpaper }}">{{ $boxpaper }}</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="close_reminder" class="m-2 text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Close</button>
                        <button wire:click="saveReminder()" type="button" class="m-2 text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Save</button>
                    </div>
            </div>
        </div>
    </div>

    
    <div class="px-6 py-3">
        {{ $reminders->links('livewire.pagination') }}
    </div>

    @script
        <script> 
            $(function() {

                var isDelete;
                var config = {
                    '.chosen-select'           : {},
                    '.chosen-select-deselect'  : { allow_single_deselect: true },
                    '.chosen-select-no-single' : { disable_search_threshold: 10 },
                    '.chosen-select-no-results': { no_results_text: 'Oops, nothing found!' },
                    '.chosen-select-rtl'       : { rtl: true },
                    '.chosen-select-width'     : { width: '95%' },
                    'no_results_text'          : "No result found. Press enter to add "
                }

                initChosen()
                function initChosen() {
                    for (var selector in config) {
                        $(selector).chosen(config[selector]);
                    }
                }

                $('#child-box').click( function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                })

                function resetTableSize() {
                    $('table tr').each( function() {
                        $(this).removeClass('h-[340px]');
                    })
                }

                $(document).on('click','table tr', function(e) {
                    if ($(this).closest('thead').length) {
                        return; // Exit the function if clicked in thead
                    }

                    $('#child-box').removeClass('hidden');
                    setTimeout(() => {
                        $('#condition').trigger("chosen:updated");
                        $('#boxpapers').trigger("chosen:updated");
                    }, 200);


                })

                $("#boxpapers").chosen().change( function(el) {
                    const key = $(this).val();
                    $wire.$call("syncSelectedBoxPapers",key);
                    
                });

                $("#condition").chosen().change( function(el) {
                    const key = $(this).val();
                    $wire.$call("syncSelectedCondition",key);
                    
                });

                function close() {
                    resetTableSize();
                    resetValidation();
                    $('#child-box').addClass('hidden');
                    $('.chosen-select').chosen("destroy");
                    initChosen()
                }

                function resetValidation() {
                    $('.error').each(function() {
                        if ($(this).hasClass('hidden') == false) {
                            $(this).addClass('hidden')
                        }
                    })
                }

                $('#new_reminder').click( function(e) {
                    resetTableSize()
                    $('.chosen-select').chosen("destroy");
                    setTimeout(() => {
                        initChosen()
                        
                    },170)

                    // debugger
                    setTimeout(() => {
                        $('#child-box').removeClass('hidden h-[340px]');
                        $('#child-box').children().first().next().find('input').focus();
                    },150)

                })

                $wire.on('close', msg => {
                    resetValidation()

                    if (msg[0]) {
                        $.each(msg[0], function(i,val) {
                            const newKey = i.replace("reminder.", "");
                            $("."+newKey).text(val.toString());
                            $("."+newKey).removeClass('hidden');
                        })
                    } else {
                        close();
                    }
                })

                $('#close_reminder').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    close();
                })
            })
        </script>
    @endscript
    
</div>
