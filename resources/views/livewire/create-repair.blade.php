<div x-data>

    <div wire:ignore.self x-cloak id="modal-dialog" class="fixed left-0 top-0 bg-black opacity-50 w-screen h-screen justify-center items-center z-60 opacity-0 hidden transition-opacity duration-500">
        <div class="bg-white rounded shadow-md p-3 w-[40%]">
            <h1 class="bg-gray-200 font-semibold mb-4 text-2xl">Repair
            <p class="text-gray-400 text-xs">Create or update the product for repair.</p>
            </h1>
            <div class="mb-2">
                <label for="assigned-to-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Watchmaker</label>
                <input type="text" id="assigned-to-input" wire:model="fields.assignTo" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light" placeholder="Michael" required />
                @error("fields.assignTo")
                <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="mb-2">
                <label for="jobs-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assigned Jobs</label>
                <input type="text" id="jobs-input" wire:model="fields.jobs" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light" placeholder="Overhaul, polish, ..." required />
                @error("fields.jobs")
                    <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="mb-2">
                <label for="notes-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                <textarea id="notes-input" rows="4" wire:model="fields.notes" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light" required></textarea>
                @error("fields.notes")
                <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="mb-2">
                <label for="cost-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Repair Cost</label>
                <input type="number" id="cost-input" step=".01" wire:model="fields.cost" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light" placeholder="This cost will be added to the product cost" required />
                @error("fields.cost")
                    <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="flex items-start mb-2">
                <div class="flex items-center h-5">
                    <input id="terms" type="checkbox" wire:model="fields.completed" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" />
                </div>
                <label for="terms" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Mark as completed</label>
            </div>
            <button wire:click="saveProductRepair()" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
            <button wire:click.prevent="clearRepairDialogBox()" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Cancel</button>
        </div>
    </div>

</div>


<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('display-message', (event) => {
            hideModalBox()
            //Livewire.dispatch('process-product-item-messages', { msg: 'reset-repair' })
        });
    });

    function displayModalBox() {
        el = document.getElementById('modal-dialog');
        el.classList.remove('hidden')
        el.classList.add('flex')
        setTimeout(()=> {
            el.classList.add('opacity-100')

        },50)
        document.getElementById('assigned-to-input').focus();
    }

    function hideModalBox() {
        el = document.getElementById('modal-dialog');
        el.classList.add('opacity-0')
        el.classList.remove('opacity-100')
        setTimeout(()=> {
            el.classList.add('hidden')
            el.classList.add('flex')

        },500)
    }
</script>
