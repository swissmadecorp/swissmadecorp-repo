<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
    <div x-data wire:ignore.self id="slideoverpost-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideoverpost-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0 "></div>
        <div wire:ignore.self id="slideoverpost" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-900 border" style="width: 700px">
            <div class="bg-gray-200 p-3 dark:bg-gray-600 dark:text-gray-300 text-2xl text-gray-500">

            </div>
            <div id="slideoverpost-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

        </div>

    </div>

    @script
        <script>
            $(function() {
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