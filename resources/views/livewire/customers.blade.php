<div x-data="{ isSliderVisible: false,
        focusSearchBox() {
            if (!this.isSliderVisible) {
                $refs.customersearchbox.focus();
                $refs.customersearchbox.select();
            }
        }
            }"
x-init="focusSearchBox()"
@keydown.window="
if ($event.key === '=') {

            if ($refs.customersearchbox !== document.activeElement && !isSliderVisible) {
                $event.preventDefault();
                focusSearchBox();
            }
        }">
    {{-- The whole world belongs to you. --}}
        <!-- Page Header -->
@section('main_header')
<!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
 <style>
.drop-target-highlight {
    background-color: #e0f2fe; /* Light blue background */
    border: 2px solid #3b82f6; /* Blue border */
}
.dragged-item-outline {
    border: 2px dashed #000; /* A dashed black border */
}
</style>
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@stop

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/jquery.mask.js" type="text/javascript"></script>
<script src="/editable-select/jquery-editable-select.js"></script>
@stop

    <div wire:loading.delay.longest class="fixed z-50">
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

    @if (session()->has('message'))
        <div id="alert-bcustomer-1" class="flex items-center p-4 mb-4 text-blue-800 bcustomer-t-4 bcustomer-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:bcustomer-blue-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('message') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-bcustomer-1" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @elseif (session()->has('error'))
        <div id="alert-bcustomer-1" class="flex items-center p-4 mb-4 text-red-800 bcustomer-t-4 bcustomer-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:bcustomer-red-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"  data-dismiss-target="#alert-bcustomer-2" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @endif

    <div x-data class="flex bg-gray-200 dark:bg-gray-600 rounded-lg items-center p-1.5">
        <div class="w-full rounded-lg">
            <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white">{{$pageName}}</h1>
        </div>
    </div>

    <div class="relative sm:rounded-lg">
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900 p-2">
                <!-- <form action="customers/create"> -->
                    @role('superadmin|administrator')
                        <button wire:click.prevent="createNewCustomer()" class="editcustomer bg-sky-500 hover:bg-[#0284c7] text-white font-bold text-sm px-3 py-1.5 rounded">Create New</button>
                    @endrole
                <!-- </form> -->
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" x-ref="customersearchbox" wire:model.live.debounce.150ms="search" id="table-search" class="block h-10 ps-10 text-gray-900 bcustomer bcustomer-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:bcustomer-blue-500 dark:bg-gray-700 dark:bcustomer-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:bcustomer-blue-500 px-3" placeholder="Search for items">
                </div>
            </div>

            <livewire:customer-item />
            <div x-data="{ draggedId: null, dropTargetId: null }">
                <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-3 py-3">ID</th>
                            <th scope="col" class="px-3 py-3">Company</th>
                            <th scope="col" class="px-3 py-3"></th>
                        </tr>
                    </head>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr wire:key="{{$customer->id}}"
                                    x-ref="row{{ $customer->id }}"
                                    draggable="true"
                                    @dragstart="
                                        draggedId = {{ $customer->id }};
                                        $refs.row{{ $customer->id }}.classList.add('dragged-item-outline');
                                        event.dataTransfer.setDragImage($refs.row{{ $customer->id }}, 0, 0);
                                    "
                                    @drop.prevent="
                                        $wire.transferCustomer(draggedId, {{ $customer->id }});
                                        dropTargetId = null;
                                    "
                                    @dragover.prevent="dropTargetId = {{ $customer->id }}"
                                    @dragleave.prevent="dropTargetId = null"
                                    @dragend.prevent="
                                        $refs.row{{ $customer->id }}.classList.remove('dragged-item-outline');
                                        draggedId = null;
                                        dropTargetId = null;
                                    "
                                    :class="{ 'drop-target-highlight': dropTargetId === {{ $customer->id }} }"
                                    class="odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">

                                    <td class="px-3 py-2.5">
                                        <a href="" wire:click.prevent="invokeCustomerId({{$customer->id}},'{{$customer->company}}')" class="editcustomer">{{$customer->id}}</a>
                                    </td>

                                    <td class="px-3 py-2.5">{{$customer->company}}</td>

                                    <td class="text-center w-24">
                                        <button type="button" wire:confirm="Are you sure you want to delete customer #{{$customer->id}}?" wire:click.prevent="deleteCustomer({{$customer->id}})" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1 me-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    <div class="px-6 py-3">
   {{ $customers->links('livewire.pagination') }}
   </div>

   <script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('confirm-transfer', (data) => {
            const source = data[0].source;
            const target = data[0].target;

            Swal.fire({
                title: 'Are you sure?',
                html: "Do you want to transfer all data from **" + source.company + " (" + source.id + ")** to **" + target.company + " (" + target.id + ")**? This cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, transfer it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.dispatch('confirm-and-transfer', { sourceId: source.id, targetId: target.id });
                }
            });
        });

        Livewire.on('transfer-success', () => {
            Swal.fire(
                'Transferred!',
                'All data has been successfully moved.',
                'success'
            );
        });
    });
</script>

</div>
