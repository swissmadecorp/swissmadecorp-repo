<div x-data="{ isSliderVisible: false,
            selectedRow: null,
            focusSearchBox() {
                if (!this.isSliderVisible) {
                    $refs.searchbox.focus();
                    $refs.searchbox.select();
                }
            }
                }"
    x-init="focusSearchBox()"
    @keydown.window="
    if ($event.key === '=') {

                if ($refs.searchbox !== document.activeElement && !isSliderVisible) {
                    $event.preventDefault();
                    focusSearchBox();
                }
            }">
    @section('main_header')
    <!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
    <link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stop

    @section ('footer')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
    <script src="/js/jquery.autocomplete.min.js"></script>
    <script src="/editable-select/jquery-editable-select.js"></script>
    <script src="/js/jquery.mask.js" type="text/javascript"></script>
    @stop


    <div class="overflow-x-auto relative ">
        <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" style="width: 40px" class="px-3 py-3">Product</th>
                        <th scope="col" class="px-3 py-3">Contact Name</th>
                        <th scope="col" class="px-8 py-3">Company</th>
                        <th scope="col" class="px-8 py-3">Phone</th>
                        <th scope="col" class="px-8 py-3">Date</th>

                    </tr>
                </head>
                <tbody>
                    @foreach($inquiries as $inquiry)
                    <tr x-data wire:key="{{$inquiry->id}}" class="odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-3 py-4">
                            <a href="/products/{{$inquiry->product_id}}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">{{$inquiry->product_id}}</a>
                        </td>
                        <td class="px-3 py-4">{{$inquiry->contact_name}}</td>
                        <td class="px-8 py-4">{{$inquiry->company_name}}</td>
                        <td class="px-8 py-4">{{$inquiry->phone}}</td>
                        <td class="px-8 py-4">{{$inquiry->created_at->format('m-d-Y')}}</td>
                    </tr>
                    @endforeach
                </tbody>
        </table>
    </div>

   <div class="px-6 py-3">
   {{ $inquiries->links('livewire.pagination') }}
   </div>

</div>
