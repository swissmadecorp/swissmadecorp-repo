<div>
    <div x-data="{ active: null }" class="p-4 relative min-h-[600px]">
        <div class="grid gap-4 transition-all duration-700 ease-in-out items-start"
            :class="active ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3'">

            <div
                x-show="active === null || active === 1"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                @click="active = 1"
                class="bg-neutral-primary-soft cursor-pointer p-6 border border-default shadow-xs h-64 bg-[url('/assets/red-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                :class="active === 1 ? 'w-full max-w-none' : 'max-w-sm w-full'"
            >
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-2xl font-semibold text-heading">User Management</h5>
                    <button x-show="active === 1" @click.stop="active = null" class="self-start text-sm bg-black/20 p-2 rounded-full px-4 text-white">✕ Back to Dashboard</button>
                </div>
            </div>

            <div
                x-show="active === null || active === 2"
                x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-12"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                @click="active = 2"
                class="bg-neutral-primary-soft cursor-pointer p-6 border border-default shadow-xs h-64 bg-[url('/assets/green-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                :class="active === 2 ? 'w-full max-w-none' : 'max-w-sm w-full'"
            >
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-2xl font-semibold text-heading">Roles Management</h5>
                    <button x-show="active === 2" @click.stop="active = null" class="self-start text-sm bg-black/20 p-2 rounded-full px-4 text-white">✕ Back to Dashboard</button>
                </div>
            </div>

            <div
                x-show="active === null || active === 3"
                x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-12"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-end="opacity-0"
                @click="active = 3"
                class="bg-neutral-primary-soft cursor-pointer p-6 border border-default shadow-xs h-64 bg-[url('/assets/purple-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                :class="active === 3 ? 'w-full max-w-none' : 'max-w-sm w-full'"
            >
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-2xl font-semibold text-heading">Permissions</h5>
                    <button x-show="active === 3" @click.stop="active = null" class="self-start text-sm bg-black/20 p-2 rounded-full px-4 text-white">✕ Back to Dashboard</button>
                </div>
            </div>
        </div>

        <div x-show="active !== null"
            x-transition:enter="transition ease-out duration-500 delay-500"
            x-transition:enter-start="opacity-0 transform translate-y-8"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="mt-6">
            <div class="p-8 bg-white border border-default rounded-[45px] shadow-lg min-h-[300px]">
                <template x-if="active === 1"><div><h2 class="text-xl font-bold">User Management Content</h2></div></template>
                <template x-if="active === 2"><div><h2 class="text-xl font-bold">Roles Configuration Content</h2></div></template>
                <template x-if="active === 3"><div><h2 class="text-xl font-bold">Permissions Details Content</h2></div></template>
            </div>
        </div>
    </div>
</div>
