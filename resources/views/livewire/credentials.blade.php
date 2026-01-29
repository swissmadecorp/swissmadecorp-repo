<div>
<div x-data="{ active: null, editingId: null }" class="p-4 relative min-h-screen">
    <style> [x-cloak] { display: none !important; } </style>

    <div class="fixed inset-0 z-0 transition-all duration-700 pointer-events-none"
         :class="active ? 'backdrop-blur-md bg-white/30' : 'backdrop-blur-none bg-transparent'">
    </div>

    <div class="relative z-10">
        <div class="grid gap-6 transition-all duration-700 ease-in-out items-start"
             :class="active ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3'">

            <div x-show="active === null || active === 1"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click="active = 1; editingId = null"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/red-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                 :class="active === 1 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">User Management</h5>
                    <button x-show="active === 1" @click.stop="active = null; editingId = null" x-cloak
                            class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                        ✕ Close and Return
                    </button>
                </div>
            </div>

            <div x-show="active === null || active === 2"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 translate-y-20"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click="active = 2; editingId = null"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/green-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                 :class="active === 2 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Roles Management</h5>
                    <button x-show="active === 2" @click.stop="active = null; editingId = null" x-cloak
                            class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                        ✕ Close and Return
                    </button>
                </div>
            </div>

            <div x-show="active === null || active === 3"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 translate-y-20"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click="active = 3; editingId = null"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/purple-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out"
                 :class="active === 3 ? 'w-full max-w-none shadow-2xl' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Permissions</h5>
                    <button x-show="active === 3" @click.stop="active = null; editingId = null" x-cloak
                            class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                        ✕ Close and Return
                    </button>
                </div>
            </div>
        </div>

        <div x-show="active !== null" x-cloak
             x-transition:enter="transition ease-out duration-600 delay-400"
             x-transition:enter-start="opacity-0 translate-y-12"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-4 pl-6 pr-6">

            <div class="p-6 bg-white/80 backdrop-blur-lg border border-white shadow-2xl rounded-[50px] min-h-[400px]">

                <div x-show="active === 1">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                            <tr>
                                <th class="px-3 py-3 w-10"></th>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Name</th>
                                <th class="px-3 py-3">User Name</th>
                                <th class="px-3 py-3">Email</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="user in $wire.users" :key="user.id">
                                <tr class="border-b hover:bg-white/50 transition-colors">
                                    <td class="px-3 py-2"><input type="checkbox" class="rounded"></td>
                                    <td class="px-3 py-2" x-text="user.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.name"></span>
                                        <input x-show="editingId === user.id" x-model="user.name" class="w-full border rounded px-1">
                                    </td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.username"></span>
                                        <input x-show="editingId === user.id" x-model="user.username" class="w-full border rounded px-1">
                                    </td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.email"></span>
                                        <input x-show="editingId === user.id" x-model="user.email" class="w-full border rounded px-1">
                                    </td>
                                    <td class="px-3 py-2" x-text="new Intl.DateTimeFormat('en-GB').format(new Date(user.created_at))"></td>
                                    <td class="px-3 py-2 flex gap-3">
                                        <div x-show="editingId !== user.id" class="flex gap-3">
                                            <button @click="editingId = user.id" class="text-blue-600 hover:underline">Edit</button>
                                            <button @click="$wire.deleteuser(user.id)" class="text-red-600 hover:underline">Delete</button>
                                        </div>
                                        <div x-show="editingId === user.id" class="flex gap-3" x-cloak>
                                            <button @click="$wire.saveUser({ ...user }).then(() => editingId = null)" class="text-green-600 font-bold hover:underline">Save</button>
                                            <button @click="editingId = null; $wire.loadData()" class="text-gray-400 hover:underline">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="active === 2">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                            <tr>
                                <th class="px-3 py-3 w-10"></th>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Name</th>
                                <th class="px-3 py-3">Permissions</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="role in $wire.roles" :key="role.id">
                                <tr class="border-b hover:bg-white/50 transition-colors">
                                    <td class="px-3 py-2"><input type="checkbox" class="rounded"></td>
                                    <td class="px-3 py-2" x-text="role.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== role.id" x-text="role.name"></span>
                                        <input x-show="editingId === role.id" x-model="role.name" class="w-full border rounded px-1">
                                    </td>
                                    <td class="px-3 py-2" x-text="role.permissions_list"></td>
                                    <td class="px-3 py-2" x-text="new Intl.DateTimeFormat('en-GB').format(new Date(role.created_at))"></td>
                                    <td class="px-3 py-2 flex gap-3">
                                        <div x-show="editingId !== role.id" class="flex gap-3">
                                            <button @click="editingId = role.id" class="text-blue-600 hover:underline">Edit</button>
                                            <button @click="$wire.deleterole(role.id)" class="text-red-600 hover:underline">Delete</button>
                                        </div>
                                        <div x-show="editingId === role.id" class="flex gap-3" x-cloak>
                                            <button @click="$wire.saveRole({ ...role }).then(() => editingId = null)" class="text-green-600 font-bold hover:underline">Save</button>
                                            <button @click="editingId = null; $wire.loadData()" class="text-gray-400 hover:underline">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="active === 3">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50/50">
                            <tr>
                                <th class="px-3 py-3 w-10"></th>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Name</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="permission in $wire.permissions" :key="permission.id">
                                <tr class="border-b hover:bg-white/50 transition-colors">
                                    <td class="px-3 py-2"><input type="checkbox" class="rounded"></td>
                                    <td class="px-3 py-2" x-text="permission.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== permission.id" x-text="permission.name"></span>
                                        <input x-show="editingId === permission.id" x-model="permission.name" class="w-full border rounded px-1">
                                    </td>
                                    <td class="px-3 py-2" x-text="new Intl.DateTimeFormat('en-GB').format(new Date(permission.created_at))"></td>
                                    <td class="px-3 py-2 flex gap-3">
                                        <div x-show="editingId !== permission.id" class="flex gap-3">
                                            <button @click="editingId = permission.id" class="text-blue-600 hover:underline">Edit</button>
                                            <button @click="$wire.deletepermission(permission.id)" class="text-red-600 hover:underline">Delete</button>
                                        </div>
                                        <div x-show="editingId === permission.id" class="flex gap-3" x-cloak>
                                            <button @click="$wire.savePermission({ ...permission }).then(() => editingId = null)" class="text-green-600 font-bold hover:underline">Save</button>
                                            <button @click="editingId = null; $wire.loadData()" class="text-gray-400 hover:underline">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</div>
