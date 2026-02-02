<div class="p-4 relative min-h-screen">
    <!-- 1. CSS for UI State & Transitions -->
    <style>
        [x-cloak] { display: none !important; }
        .tab-active-card {
            width: 100% !important;
            max-width: none !important;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }
        /* Smooth fade for the drawer */
        .drawer-enter { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <!-- 2. Background Blur Backdrop -->
    <div class="fixed inset-0 z-0 transition-all duration-700 pointer-events-none {{ $activeTab ? 'backdrop-blur-md bg-white/30' : 'bg-transparent' }}"></div>

    <div class="relative z-10">
        <!-- 3. Animated Card Grid -->
        <div class="grid gap-6 transition-all duration-700 items-start {{ $activeTab ? 'grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3' }}">

            <!-- User Management Card -->
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

            <!-- Roles Management Card -->
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

            <!-- Permissions Card -->
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

        <!-- 4. Expanded Data Tables Section -->
        @if($activeTab)
        <div class="mt-6 px-6">
            <div class="p-8 bg-white/90 backdrop-blur-lg border border-white shadow-2xl rounded-[50px] min-h-[450px]">

                <!-- USER TABLE -->
                @if($activeTab === 1)
                <table class="w-full text-sm text-left border-collapse">
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
                        <tr class="border-b hover:bg-white/50 transition-colors {{ $editingId === $user['id'] ? 'bg-gray-50 border-b-0' : '' }}" wire:key="user-row-{{ $user['id'] }}">
                            <td class="px-3 py-2">{{ $user['id'] }}</td>

                            <!-- Name Field -->
                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    @php $hasNameErr = $errors->has('users.'.$index.'.name'); @endphp
                                    <input type="text"
                                           wire:model.blur="users.{{ $index }}.name"
                                           placeholder="{{ $hasNameErr ? $errors->first('users.'.$index.'.name') : 'Enter Name' }}"
                                           class="bg-white border {{ $hasNameErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @else
                                    {{ $user['name'] }}
                                @endif
                            </td>

                            <!-- Username Field -->
                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    @php $hasUserErr = $errors->has('users.'.$index.'.username'); @endphp
                                    <input type="text"
                                           wire:model.blur="users.{{ $index }}.username"
                                           placeholder="{{ $hasUserErr ? $errors->first('users.'.$index.'.username') : 'Enter Username' }}"
                                           class="bg-white border {{ $hasUserErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @else
                                    {{ $user['username'] }}
                                @endif
                            </td>

                            <!-- Email Field -->
                            <td class="px-3 py-2">
                                @if($editingId === $user['id'])
                                    @php $hasEmailErr = $errors->has('users.'.$index.'.email'); @endphp
                                    <input type="email"
                                           wire:model.blur="users.{{ $index }}.email"
                                           placeholder="{{ $hasEmailErr ? $errors->first('users.'.$index.'.email') : 'Enter Email' }}"
                                           class="bg-white border {{ $hasEmailErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @else
                                    {{ $user['email'] }}
                                @endif
                            </td>

                            <td class="px-3 py-2">{{ $user['created_at'] }}</td>

                            <td class="px-3 py-2 whitespace-nowrap">
                                @if($editingId === $user['id'])
                                    <button wire:click="saveUser({{ $index }})" class="text-green-600 font-bold hover:underline mr-2" wire:loading.attr="disabled">
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

                        <!-- USER DRAWER ROW -->
                        @if($editingId === $user['id'])
                        <tr class="bg-gray-50 border-b border-gray-200 drawer-enter" wire:key="user-drawer-{{ $user['id'] }}">
                            <td colspan="6" class="px-4 pb-6 pt-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-200 pt-4 mt-2">
                                    <!-- User Roles -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Assign Roles</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($available_roles as $role)
                                            <label class="inline-flex items-center space-x-2 cursor-pointer bg-white p-2 rounded border border-gray-200 hover:border-blue-400 transition-colors">
                                                <input type="checkbox"
                                                       value="{{ $role['id'] }}"
                                                       wire:model="users.{{ $index }}.role_ids"
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <span class="text-sm text-gray-700 font-medium">{{ $role['name'] }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <!-- User Password -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Reset Password</h4>
                                        <div class="space-y-3">
                                            <div>
                                                @php $hasPassErr = $errors->has('users.'.$index.'.password'); @endphp
                                                <input type="password"
                                                       wire:model.blur="users.{{ $index }}.password"
                                                       placeholder="{{ $hasPassErr ? $errors->first('users.'.$index.'.password') : 'New Password' }}"
                                                       class="bg-white border {{ $hasPassErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            </div>
                                            <div>
                                                <input type="password"
                                                       wire:model.blur="users.{{ $index }}.password_confirmation"
                                                       placeholder="Confirm Password"
                                                       class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            </div>
                                            <p class="text-xs text-gray-400 italic">Leave these fields blank if you don't want to change the password.</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
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
                        <tr class="border-b hover:bg-white/50 transition-colors {{ $editingId === $role['id'] ? 'bg-gray-50 border-b-0' : '' }}" wire:key="role-row-{{ $role['id'] }}">
                            <td class="px-3 py-2">{{ $role['id'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $role['id'])
                                    @php $hasRoleErr = $errors->has('roles.'.$index.'.name'); @endphp
                                    <input type="text"
                                           wire:model.blur="roles.{{ $index }}.name"
                                           placeholder="{{ $hasRoleErr ? $errors->first('roles.'.$index.'.name') : 'Enter Role Name' }}"
                                           class="bg-white border {{ $hasRoleErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @else
                                    {{ $role['name'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs italic text-gray-500">{{ $role['permissions_list'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $role['id'])
                                    <button wire:click="saveRole({{ $index }})" class="text-green-600 font-bold hover:underline mr-2" wire:loading.attr="disabled">
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

                        <!-- ROLE DRAWER ROW -->
                        @if($editingId === $role['id'])
                        <tr class="bg-gray-50 border-b border-gray-200 drawer-enter" wire:key="role-drawer-{{ $role['id'] }}">
                            <td colspan="4" class="px-4 pb-6 pt-2">
                                <div class="border-t border-gray-200 pt-4 mt-2">
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Assign Permissions to {{ $role['name'] }}
                                        </h4>
                                        <!-- Show error if no permission is selected -->
                                        @if($errors->has("roles.$index.permission_ids"))
                                            <span class="text-red-500 text-xs font-bold uppercase tracking-wider">
                                                {{ $errors->first("roles.$index.permission_ids") }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        <!-- Iterate over ALL available permissions -->
                                        @foreach($permissions as $perm)
                                        <label class="inline-flex items-center space-x-2 cursor-pointer bg-white p-2 rounded border border-gray-200 hover:border-blue-400 transition-colors {{ in_array($perm['id'], $roles[$index]['permission_ids']) ? 'border-blue-200 bg-blue-50' : '' }}">
                                            <input type="checkbox"
                                                   value="{{ $perm['id'] }}"
                                                   wire:model="roles.{{ $index }}.permission_ids"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="text-sm text-gray-700 font-medium">{{ $perm['name'] }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
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
                        <tr class="border-b hover:bg-white/50 transition-colors" wire:key="perm-row-{{ $perm['id'] }}">
                            <td class="px-3 py-2">{{ $perm['id'] }}</td>
                            <td class="px-3 py-2">
                                @if($editingId === $perm['id'])
                                    @php $hasPermErr = $errors->has('permissions.'.$index.'.name'); @endphp
                                    <input type="text"
                                           wire:model.blur="permissions.{{ $index }}.name"
                                           placeholder="{{ $hasPermErr ? $errors->first('permissions.'.$index.'.name') : 'Enter Permission Name' }}"
                                           class="bg-white border {{ $hasPermErr ? 'border-red-500 placeholder:text-red-500' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @else
                                    {{ $perm['name'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @if($editingId === $perm['id'])
                                    <button wire:click="savePermission({{ $index }})" class="text-green-600 font-bold hover:underline mr-2" wire:loading.attr="disabled">
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