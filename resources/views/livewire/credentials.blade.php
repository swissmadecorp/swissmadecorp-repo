<div>
    <div x-data="{ active: null }" class="p-4 relative min-h-screen transition-all duration-500">

        <div
            class="fixed inset-0 z-0 transition-all duration-700 pointer-events-none"
            :class="active ? 'backdrop-blur-md bg-white/30' : 'backdrop-blur-none bg-transparent'"
        ></div>

        <div class="relative z-10">
            <div class="grid gap-6 transition-all duration-700 ease-in-out items-start"
                :class="active ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3'">

                <div
                    x-show="active === null || active === 1"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click="active = 1"
                    class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl bg-[url('/assets/red-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                    :class="active === 1 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'"
                >
                    <div class="flex flex-col h-full justify-between">
                        <h5 class="text-3xl font-bold text-heading">User Management</h5>
                        <button x-show="active === 1" @click.stop="active = null" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    </div>
                </div>

                <div
                    x-show="active === null || active === 2"
                    x-transition:enter="transition ease-out duration-500 delay-200"
                    x-transition:enter-start="opacity-0 translate-y-20"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click="active = 2"
                    class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl bg-[url('/assets/green-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                    :class="active === 2 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'"
                >
                    <div class="flex flex-col h-full justify-between">
                        <h5 class="text-3xl font-bold text-heading">Roles Management</h5>
                        <button x-show="active === 2" @click.stop="active = null" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    </div>
                </div>

                <div
                    x-show="active === null || active === 3"
                    x-transition:enter="transition ease-out duration-500 delay-200"
                    x-transition:enter-start="opacity-0 translate-y-20"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click="active = 3"
                    class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl bg-[url('/assets/purple-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                    :class="active === 3 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'"
                >
                    <div class="flex flex-col h-full justify-between">
                        <h5 class="text-3xl font-bold text-heading">Permissions</h5>
                        <button x-show="active === 3" @click.stop="active = null" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="active !== null"
                x-transition:enter="transition ease-out duration-600 delay-400"
                x-transition:enter-start="opacity-0 translate-y-12"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-8">
                <div class="p-12 bg-white/80 backdrop-blur-lg border border-white shadow-2xl rounded-[50px] min-h-[400px]">
                    <div x-show="active === 1">

                        <p class="text-lg text-gray-600">
                            <table x-data = "{status: @entangle('status')}" class="w-full text-sm text-left rtl:text-right dark:text-white-400">
                                <thead
                                    :class="status == 0 ? 'bg-red-300' : 'bg-gray-50'"
                                    class="text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" style="width: 40px" class="px-3 py-3"></th>
                                        <th scope="col" class="px-3 py-3">ID</th>
                                        <th scope="col" class="px-3 py-3">Name</th>
                                        <th scope="col" class="px-3 py-3">User Name</th>
                                        <th scope="col" class="px-3 py-3">Email</th>
                                        <th scope="col" class="px-3 py-3">Date</th>
                                        <th scope="col" class="px-3 py-3"></th>
                                    </tr>
                                </head>
                                <tbody>
                                    <template x-for="user in $wire.users" :key="user.id">
                                        <tr class="border-b hover:bg-gray-50 transition-colors">
                                            <td class="px-3 py-2">
                                                <input type="checkbox" class="rounded">
                                            </td>
                                            <td class="px-3 py-2" x-text="user.id"></td>
                                            <td class="px-3 py-2" x-text="user.name"></td>
                                            <td class="px-3 py-2" x-text="user.username"></td>
                                            <td class="px-3 py-2" x-text="user.email"></td>
                                            <td class="px-3 py-2" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                                            <td class="px-3 py-2">
                                                <button @click="$wire.deleteuser(user.id)" class="text-red-600 hover:underline">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </p>
                    </div>
                    <div x-show="active === 2">
                        <h2 class="text-4xl font-extrabold mb-4">Roles & Security</h2>
                        <p class="text-lg text-gray-600">Configure global access levels and security groups...</p>
                    </div>
                    <div x-show="active === 3">
                        <h2 class="text-4xl font-extrabold mb-4">Fine-grained Permissions</h2>
                        <p class="text-lg text-gray-600">Adjust specific action-level permissions...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
