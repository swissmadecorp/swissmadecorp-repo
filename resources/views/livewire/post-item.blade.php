<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
    <div x-data wire:ignore.self id="slideoverpost-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideoverpost-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0 "></div>
        <div wire:ignore.self id="slideoverpost" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-900 border" style="width: 700px">
            <div class="bg-gray-200 p-3 dark:bg-gray-600 dark:text-gray-300 text-2xl text-gray-500">
                @if ($postId)
                    Edit post
                @else
                    New post
                @endif

            </div>
            <div id="slideoverpost-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div class="p-6">
                <x-input-standard model="post.title" label="title" text="Title" validation />
                <x-input-standard model="post.subtitle" label="subtitle" text="Subtitle" />

                <div class="mb-2" wire:ignore>
                    <label for="post" class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Post</label>
                    <textarea id="post" rows="8" wire:model="post.post" class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>
                </div>

                @error('post.post')
                    <span class="block text-red-400">{{$message}}</span>
                @enderror

                <div class="flex justify-end">
                    @if ($postId)
                        <button wire:click="savePost()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Update Post</button>
                    @else
                        <button wire:click="savePost()" type="button" class="text-white mt-4 bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800">Save Post</button>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @section ('footer')
        <script src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>

    @endsection


    @script
        <script>
            $(function() {

                // 1. Define the function to safely reset and load content
                function refreshEditor() {
                    // If an instance already exists, destroy it completely
                    if (tinymce.get('post')) {
                        tinymce.get('post').remove();
                    }

                    // Re-initialize
                    tinymce.init({
                        selector: '#post',
                        height: 500,
                        plugins: [
                            'advlist autolink lists link image charmap print preview anchor',
                            'searchreplace visualblocks code fullscreen',
                            'insertdatetime media table contextmenu paste code',
                        ],
                        setup: function (editor) {
                            // Update Livewire on change
                            editor.on('blur', function (e) {
                                $wire.set('post.post', editor.getContent());
                            });

                            // CRITICAL: Pull the fresh data from Livewire once the editor is ready
                            editor.on('init', function () {
                                const freshContent = $wire.get('post.post') || '';
                                editor.setContent(freshContent);
                            });
                        }
                    });
                }

                $(document).on('click', '.editpost', function() {
                    $wire.clearFields().then(() => {
                        // Open the slider
                        Slider();
                        // Wait a tiny bit for the DOM/Transition to settle, then refresh TinyMCE
                        setTimeout(() => {
                            refreshEditor();
                        }, 100);
                    });
                });

                function Slider() {
                    // debugger
                    $('body').toggleClass('overflow-hidden')
                    $('#slideoverpost-container').toggleClass('invisible')
                    $('#slideoverpost-bg').toggleClass('opacity-0')
                    $('#slideoverpost-bg').toggleClass('opacity-75')
                    $('#slideoverpost').toggleClass('translate-x-full')
                    if (!$('#slideoverpost-container').hasClass('invisible')) {
                        // Give the CSS transition a moment to finish, then init/refresh
                        setTimeout(() => initTinyMCE(), 100);
                    }
                }

                window.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        if (!$('#slideoverpost-container').hasClass('invisible')) {
                            $wire.$call('clearFields');
                            Slider()
                        } if (!$('#slideoverpost-container').hasClass('invisible')) {
                            $wire.$call('clearFields');
                            Slider()
                        }
                    }
                });

                $(document).on('click', '#slideoverpost-child', function() {
                    Slider()
                })

                $wire.on('display-message', msg => {
                    Slider()
                });

            })
        </script>
    @endscript

</div>