<div>
    <div x-data wire:ignore.self id="slideover-inquiry-container" class="fixed inset-0 w-full h-full invisible z-50">
        <div wire:ignore.self id="slideover-inquiry-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0 "></div>
        <div wire:ignore.self id="slideover-inquiry" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-900 border" style="width: 700px">
            <div class="bg-gray-200 p-3 dark:bg-gray-600 dark:text-gray-300 text-2xl text-gray-500">
                Inquiry Details
            </div>
            <div id="slideover-inquiry-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div class="p-6">
                <x-select-standard text="Customer Group" label="group" model="customer.cgroup" :iterators="['Dealer','Customer']" />
                <x-input-standard model="customer.company" label="company" text="Company" class="pt-2" validation/>
                <div class="grid gap-2 mt-2 md:grid-cols-2">
                    <x-input-standard model="inquiry.contact_name" label="contact_name" text="Contact Name" />
                    <x-input-standard model="inquiry.company_name" label="company_name" text="Company" />
                    <x-input-standard model="inquiry.email" label="email" text="Email" />
                    <x-input-standard model="inquiry.phone" label="phone" text="Phone" />

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                        <textarea id="notes" rows="4" wire:model="inquiry.notes" class="shadow-sm border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"></textarea>                    </div>
                </div>

                <div class="max-h-[340px] min-h-[auto] overflow-y-auto shadow-lg overflow-x-hidden">
                    <table wire:ignore.self class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Order ID</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Date</th>
                                <th scope="col" class="px-4 py-3">Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>

    </div>
@script
    <script>
        $(function() {
            function Slider() {
                // debugger
                $('body').toggleClass('overflow-hidden')
                $('#slideover-inquiry-container').toggleClass('invisible')
                $('#slideover-inquiry-bg').toggleClass('opacity-0')
                $('#slideover-inquiry-bg').toggleClass('opacity-75')
                $('#slideover-inquiry').toggleClass('translate-x-full')
            }

            $(document).on('click', '#slideover-inquiry-child', function() {
                Slider()
            })

            $wire.on('display-message', msg => {
                Slider()
            });

        })
    </script>
@endscript
</div>
