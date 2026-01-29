<div>
<div x-data="{ active: null, editingId: null }" class="p-4 relative min-h-screen">
    <style> [x-cloak] { display: none !important; } </style>

    <div class="fixed inset-0 z-0 transition-all duration-700 pointer-events-none"
         :class="active ? 'backdrop-blur-md bg-white/30' : 'backdrop-blur-none bg-transparent'">
    </div>

    <div class="relative z-10">
        <div class="grid gap-6 transition-all duration-700 ease-in-out items-start"
             :class="active ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3'">

            <div @click="active = 1; editingId = null" x-show="active === null || active === 1"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/red-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700"
                 :class="active === 1 ? 'w-full max-w-none' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">User Management</h5>
                    <button x-show="active === 1" @click.stop="active = null; editingId = null" x-cloak class="self-start text-sm bg-black/40 p-2 rounded-full px-6 text-white">✕ Close</button>
                </div>
            </div>

            <div @click="active = 2; editingId = null" x-show="active === null || active === 2"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/green-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700"
                 :class="active === 2 ? 'w-full max-w-none' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Roles Management</h5>
                    <button x-show="active === 2" @click.stop="active = null; editingId = null" x-cloak class="self-start text-sm bg-black/40 p-2 rounded-full px-6 text-white">✕ Close</button>
                </div>
            </div>

            <div @click="active = 3; editingId = null" x-show="active === null || active === 3"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/purple-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700"
                 :class="active === 3 ? 'w-full max-w-none' : 'max-w-sm w-full hover:scale-105'">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Permissions</h5>
                    <button x-show="active === 3" @click.stop="active = null; editingId = null" x-cloak class="self-start text-sm bg-black/40 p-2 rounded-full px-6 text-white">✕ Close</button>
                </div>
            </div>
        </div>

        <div x-show="active !== null" x-cloak x-transition:enter="transition ease-out duration-600 delay-400" class="mt-4">
            <div class="p-8 bg-white/90 backdrop-blur-lg border border-white shadow-2xl rounded-[50px] min-h-[400px]">

                <div x-show="active === 1">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 uppercase text-xs">
                            <tr>
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
                                <tr class="border-b">
                                    <td class="px-3 py-2" x-text="user.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.name"></span>
                                        <input x-show="editingId === user.id" x-model="user.name" class="border rounded px-1 w-full">
                                    </td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.username"></span>
                                        <input x-show="editingId === user.id" x-model="user.username" class="border rounded px-1 w-full">
                                    </td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== user.id" x-text="user.email"></span>
                                        <input x-show="editingId === user.id" x-model="user.email" class="border rounded px-1 w-full">
                                    </td>
                                    <td class="px-3 py-2" x-text="new Intl.DateTimeFormat('en-GB').format(new Date(user.created_at))"></td>
                                    <td class="px-3 py-2 flex gap-2">
                                        <button x-show="editingId !== user.id" @click="editingId = user.id" class="text-blue-600">Edit</button>
                                        <button x-show="editingId !== user.id" @click="$wire.deleteuser(user.id)" class="text-red-600">Delete</button>
                                        <button x-show="editingId === user.id" @click="$wire.saveUser(user).then(() => editingId = null)" class="text-green-600 font-bold">Save</button>
                                        <button x-show="editingId === user.id" @click="editingId = null; $wire.loadData()" class="text-gray-400">Cancel</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="active === 2">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 uppercase text-xs">
                            <tr>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Role Name</th>
                                <th class="px-3 py-3">Permissions</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="role in $wire.roles" :key="role.id">
                                <tr class="border-b">
                                    <td class="px-3 py-2" x-text="role.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== role.id" x-text="role.name"></span>
                                        <input x-show="editingId === role.id" x-model="role.name" class="border rounded px-1 w-full">
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-xs text-gray-500 italic" x-text="role.permissions_list"></span>
                                    </td>
                                    <td class="px-3 py-2" x-text="role.created_at"></td>
                                    <td class="px-3 py-2 flex gap-2">
                                        <button x-show="editingId !== role.id" @click="editingId = role.id" class="text-blue-600">Edit</button>
                                        <button x-show="editingId !== role.id" @click="$wire.deleterole(role.id)" class="text-red-600">Delete</button>
                                        <button x-show="editingId === role.id" @click="$wire.saveRole(role).then(() => editingId = null)" class="text-green-600 font-bold">Save</button>
                                        <button x-show="editingId === role.id" @click="editingId = null; $wire.loadData()" class="text-gray-400">Cancel</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="active === 3">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 uppercase text-xs">
                            <tr>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Permission Name</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="perm in $wire.permissions" :key="perm.id">
                                <tr class="border-b">
                                    <td class="px-3 py-2" x-text="perm.id"></td>
                                    <td class="px-3 py-2">
                                        <span x-show="editingId !== perm.id" x-text="perm.name"></span>
                                        <input x-show="editingId === perm.id" x-model="perm.name" class="border rounded px-1 w-full">
                                    </td>
                                    <td class="px-3 py-2" x-text="new Intl.DateTimeFormat('en-GB').format(new Date(perm.created_at))"></td>
                                    <td class="px-3 py-2 flex gap-2">
                                        <button x-show="editingId !== perm.id" @click="editingId = perm.id" class="text-blue-600">Edit</button>
                                        <button x-show="editingId !== perm.id" @click="$wire.deletepermission(perm.id)" class="text-red-600">Delete</button>
                                        <button x-show="editingId === perm.id" @click="$wire.savePermission(perm).then(() => editingId = null)" class="text-green-600 font-bold">Save</button>
                                        <button x-show="editingId === perm.id" @click="editingId = null; $wire.loadData()" class="text-gray-400">Cancel</button>
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
