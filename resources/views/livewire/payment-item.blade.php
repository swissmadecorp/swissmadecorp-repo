<div> 
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt --> 
    <div wire:ignore.self id="slideover-payment-container" class="fixed inset-0 w-full h-full invisible z-[51]" >
        <div wire:ignore.self id="slideover-payment-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div @keydown.escape.prevent="closeAndClearProductFields()" tabindex="0" wire:ignore.self id="slideover-payment" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-600" style="width: 790px">
            <div class="bg-gray-200 p-3 text-2xl text-gray-500">
               Payments
            </div>
            <div id="slideover-payment-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
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
                $('body').toggleClass('overflow-hidden')
                $('#slideover-payment-container').toggleClass('invisible')
                $('#slideover-payment-bg').toggleClass('opacity-0')
                $('#slideover-payment-bg').toggleClass('opacity-20')
                $('#slideover-payment').toggleClass('translate-x-full')
                if (!$('#slideover-payment-container').hasClass('invisible')) {
                    setTimeout(() => {
                        $('#title').focus();
                    }, "400");

                }
            }

            $('#slideover-payment-child').click(function() {
                Slider()''
            })
        })
    </script>
@endscript
    
</div>