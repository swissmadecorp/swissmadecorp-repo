<div>
<div class="p-4 relative min-h-screen">
    <style>
        [x-cloak] { display: none !important; }
        .tab-active-card { width: 100% !important; max-width: none !important; box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25); }
    </style>

    <div class="fixed inset-0 z-0 transition-all duration-700 pointer-events-none {{ $activeTab ? 'backdrop-blur-md bg-white/30' : 'bg-transparent' }}"></div>

    <div class="relative z-10">
        <div class="grid gap-6 transition-all duration-700 items-start {{ $activeTab ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3' }}">

            <!-- User Card -->
            @if(is_null($activeTab) || $activeTab === 1)
            <div wire:click="setActiveTab(1)"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/red-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out {{ $activeTab === 1 ? 'tab-active-card' : 'max-w-sm w-full hover:scale-105' }}">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">User Management</h5>
                    @if($activeTab === 1)
                        <button wire:click.stop="setActiveTab(null)" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Role Card -->
            @if(is_null($activeTab) || $activeTab === 2)
            <div wire:click="setActiveTab(2)"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/green-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out {{ $activeTab === 2 ? 'tab-active-card' : 'max-w-sm w-full hover:scale-105' }}">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Roles Management</h5>
                    @if($activeTab === 2)
                        <button wire:click.stop="setActiveTab(null)" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Permission Card -->
            @if(is_null($activeTab) || $activeTab === 3)
            <div wire:click="setActiveTab(3)"
                 class="bg-neutral-primary-soft cursor-pointer p-8 border border-default shadow-xl h-40 bg-[url('/assets/purple-card.jpg')] bg-cover bg-center rounded-[45px] transition-all duration-700 ease-in-out {{ $activeTab === 3 ? 'tab-active-card' : 'max-w-sm w-full hover:scale-105' }}">
                <div class="flex flex-col h-full justify-between">
                    <h5 class="text-3xl font-bold text-heading">Permissions</h5>
                    @if($activeTab === 3)
                        <button wire:click.stop="setActiveTab(null)" class="self-start text-sm bg-black/40 backdrop-blur-md p-2 rounded-full px-6 text-white hover:bg-black/60 transition">
                            ✕ Close and Return
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($activeTab)
        <div class="mt-6 px-6">
            <div class="p-8 bg-white/90 backdrop-blur-lg border border-white shadow-2xl rounded-[50px] min-h-[450px]">

                <!-- USER TABLE -->
                @if($activeTab === 1)
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50/50">
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
                        @foreach($users as $index => $user)
                        <tr class="border-b hover:bg-white/50 transition-colors" wire:key="user-row-{{ $user['id'] }}">
                            <td class="px-3 py-2">{{ $user['id'] }}</td>

                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    <input type="text" wire:model.blur="users.{{ $index }}.name"
                                           class="bg-gray-50 border {{ $errors->has('users.'.$index.'.name') ? 'border-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @error('users.'.$index.'.name') <span class="text-[10px] text-red-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                @else
                                    {{ $user['name'] }}
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    <input type="text" wire:model.blur="users.{{ $index }}.username"
                                           class="bg-gray-50 border {{ $errors->has('users.'.$index.'.username') ? 'border-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @error('users.'.$index.'.username') <span class="text-[10px] text-red-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                @else
                                    {{ $user['username'] }}
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    <input type="email" wire:model.blur="users.{{ $index }}.email"
                                           class="bg-gray-50 border {{ $errors->has('users.'.$index.'.email') ? 'border-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @error('users.'.$index.'.email') <span class="text-[10px] text-red-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                @else
                                    {{ $user['email'] }}
                                @endif
                            </td>

                            <td class="px-3 py-2">{{ $user['created_at'] }}</td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                @if($editingId === $user['id'])
                                    <button wire:click="saveUser({{ $index }})" class="text-green-600 font-bold hover:underline mr-2 disabled:opacity-50" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveUser({{ $index }})">Save</span>
                                        <span wire:loading wire:target="saveUser({{ $index }})">...</span>
                                    </button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:underline">Cancel</button>
                                @else
                                    <button wire:click="startEdit({{ $user['id'] }})" class="text-blue-600 hover:underline mr-2">Edit</button>
                                    <button wire:click="deleteuser({{ $user['id'] }})" class="text-red-600 hover:underline">Delete</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <!-- ROLE TABLE -->
                @if($activeTab === 2)
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50/50">
                        <tr>
                            <th class="px-3 py-3">ID</th>
                            <th class="px-3 py-3">Role Name</th>
                            <th class="px-3 py-3">Permissions</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $index => $role)
                        <tr class="border-b" wire:key="role-row-{{ $role['id'] }}">
                            <td class="px-3 py-2">{{ $role['id'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $role['id'])
                                    <input type="text" wire:model.blur="roles.{{ $index }}.name"
                                           class="bg-gray-50 border {{ $errors->has('roles.'.$index.'.name') ? 'border-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @error('roles.'.$index.'.name') <span class="text-[10px] text-red-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                @else
                                    {{ $role['name'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs italic text-gray-500">{{ $role['permissions_list'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $role['id'])
                                    <button wire:click="saveRole({{ $index }})" class="text-green-600 font-bold hover:underline mr-2 disabled:opacity-50" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveRole({{ $index }})">Save</span>
                                        <span wire:loading wire:target="saveRole({{ $index }})">...</span>
                                    </button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:underline">Cancel</button>
                                @else
                                    <button wire:click="startEdit({{ $role['id'] }})" class="text-blue-600 hover:underline mr-2">Edit</button>
                                    <button wire:click="deleterole({{ $role['id'] }})" class="text-red-600 hover:underline">Delete</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                <!-- PERMISSION TABLE -->
                @if($activeTab === 3)
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50/50">
                        <tr>
                            <th class="px-3 py-3">ID</th>
                            <th class="px-3 py-3">Permission Name</th>
                            <th class="px-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions as $index => $perm)
                        <tr class="border-b" wire:key="perm-row-{{ $perm['id'] }}">
                            <td class="px-3 py-2">{{ $perm['id'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $perm['id'])
                                    <input type="text" wire:model.blur="permissions.{{ $index }}.name"
                                           class="bg-gray-50 border {{ $errors->has('permissions.'.$index.'.name') ? 'border-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @error('permissions.'.$index.'.name') <span class="text-[10px] text-red-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                                @else
                                    {{ $perm['name'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @if($editingId === $perm['id'])
                                    <button wire:click="savePermission({{ $index }})" class="text-green-600 font-bold hover:underline mr-2 disabled:opacity-50" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="savePermission({{ $index }})">Save</span>
                                        <span wire:loading wire:target="savePermission({{ $index }})">...</span>
                                    </button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:underline">Cancel</button>
                                @else
                                    <button wire:click="startEdit({{ $perm['id'] }})" class="text-blue-600 hover:underline mr-2">Edit</button>
                                    <button wire:click="deletepermission({{ $perm['id'] }})" class="text-red-600 hover:underline">Delete</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

            </div>
        </div>
        @endif
    </div>
</div>
</div>
