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
                <div class="grid gap-2 mt-2 md:grid-cols-2">
                    <x-input-standard model="post.title" label="title" text="Title" />
                    <x-input-standard model="post.subtitle" label="subtitle" text="Subtitle" />
                </div>

                <div class="mb-2">
                    <label for="post" class="block mt-2 text-sm font-medium text-gray-900 dark:text-white">Post</label>
                    <textarea id="post" rows="4" wire:model="post.post" wire:ignore class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>
                </div>
            </div>

        </div>

    </div>

    @section ('footer')
            <script src='https://cdn.tiny.cloud/1/d2eyrcjk5emsow4ou6uvrn3bwn2y91axba4csr8wlm940lzj/tinymce/5/tinymce.min.js'></script>

    @endsection


    @script
        <script>
            $(function() {
                // Function to initialize TinyMCE
                function initTinyMCE() {
                    debugger
                    tinymce.init({
                        selector: '#post', // Matches the textarea ID
                        setup: function (editor) {
                            editor.on('blur', function (e) {
                                // Update the Livewire property 'content' on blur
                                @this.set('content', editor.getContent());
                            });
                        }
                    });
                }

                // Initialize on page load
                document.addEventListener('livewire:navigated', () => {
                    initTinyMCE();
                });

                // Reinitialize if the component is dynamically added/updated
                document.addEventListener('livewire:load', () => {
                    initTinyMCE();
                });

                function Slider() {
                    // debugger
                    $('body').toggleClass('overflow-hidden')
                    $('#slideoverpost-container').toggleClass('invisible')
                    $('#slideoverpost-bg').toggleClass('opacity-0')
                    $('#slideoverpost-bg').toggleClass('opacity-75')
                    $('#slideoverpost').toggleClass('translate-x-full')
                }

                $(document).on('click', '.editpost', function() {
                    $wire.$call('clearFields');
                    Slider()
                })

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