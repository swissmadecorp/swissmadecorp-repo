<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Credentials extends Component
{
    public $users = [];
    public $roles = [];
    public $permissions = [];

    public $available_roles = [];

    public $status = '';
    public $activeTab = null;
    public $editingId = null;

    // Creation State
    public $isCreating = false;
    public $newUser = [
        'name' => '', 'username' => '', 'email' => '',
        'password' => '', 'password_confirmation' => '', 'role_ids' => []
    ];
    public $newRole = ['name' => '', 'permission_ids' => []];
    public $newPermission = ['name' => ''];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->available_roles = Role::orderBy('name')->get()->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->toArray();
        $this->loadUsers();
        $this->loadRoles();
        $this->loadPermissions();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->editingId = null;
        $this->isCreating = false; // Reset creation state when switching tabs
        $this->resetValidation();
    }

    public function resetUIErrors()
    {
        $this->resetValidation();
    }

    public function initiateCreate()
    {
        $this->isCreating = true;
        $this->editingId = null;
        $this->resetValidation();

        // Reset input arrays
        $this->newUser = ['name' => '', 'username' => '', 'email' => '', 'password' => '', 'password_confirmation' => '', 'role_ids' => []];
        $this->newRole = ['name' => '', 'permission_ids' => []];
        $this->newPermission = ['name' => ''];
    }

    public function cancelCreate()
    {
        $this->isCreating = false;
        $this->resetValidation();
    }

    public function startEdit($id)
    {
        $this->editingId = $id;
        $this->isCreating = false;
        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->loadData();
        $this->resetValidation();
    }

    public function loadUsers()
    {
        $this->users = User::with('roles')->latest()->get()->map(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'created_at' => $user->created_at->format('d/m/Y'),
                'role_ids'   => $user->roles->pluck('id')->toArray(),
                'password'   => '',
                'password_confirmation' => '',
            ];
        })->toArray();
    }

    public function loadRoles()
    {
        $this->roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id'               => $role->id,
                'name'             => $role->name,
                'permissions_list' => $role->permissions->pluck('name')->implode(', '),
                'created_at'       => $role->created_at->format('d/m/Y'),
                'permission_ids'   => $role->permissions->pluck('id')->toArray(),
            ];
        })->toArray();
    }

    public function loadPermissions()
    {
        $this->permissions = Permission::latest()->get()->map(function ($permission) {
            return [
                'id'         => $permission->id,
                'name'       => $permission->name,
                'created_at' => $permission->created_at->format('d/m/Y'),
            ];
        })->toArray();
    }

    // --- STORE METHODS (CREATE) ---

    public function storeUser()
    {

        try {
            $this->validate([
                'newUser.name' => 'required|string|min:3',
                'newUser.username' => 'required|string|unique:users,username',
                'newUser.email' => 'required|email|unique:users,email',
                'newUser.password' => 'required|min:8|confirmed',
                'newUser.role_ids' => 'nullable|array',
            ], [
                'newUser.name.required' => 'Name Required',
                'newUser.username.required' => 'Username Required',
                'newUser.email.required' => 'Email Required',
                'newUser.password.required' => 'Password Required',
            ]);

            $user = User::create([
                'name' => $this->newUser['name'],
                'username' => $this->newUser['username'],
                'email' => $this->newUser['email'],
                'password' => Hash::make($this->newUser['password']),
            ]);

            if (!empty($this->newUser['role_ids'])) {
                $user->roles()->sync(array_map('intval', $this->newUser['role_ids']));
            }

            $this->isCreating = false;
            $this->loadData();
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            if ($errors->has('newUser.name')) $this->newUser['name'] = '';
            if ($errors->has('newUser.username')) $this->newUser['username'] = '';
            if ($errors->has('newUser.email')) $this->newUser['email'] = '';
            // Clear passwords on error
            $this->newUser['password'] = '';
            $this->newUser['password_confirmation'] = '';
            throw $e;
        }
    }

    public function storeRole()
    {
        try {
            $this->validate([
                'newRole.name' => 'required|string|min:3|unique:roles,name',
                'newRole.permission_ids' => 'required|array',
            ], [
                'newRole.name.required' => 'Role Name Required',
                'newRole.name.unique' => 'Name Taken',
                'newRole.permission_ids.required' => 'Select at least one permission',
            ]);

            $role = Role::create(['name' => $this->newRole['name']]);

            if (!empty($this->newRole['permission_ids'])) {
                $role->permissions()->sync(array_map('intval', $this->newRole['permission_ids']));
            }

            $this->isCreating = false;
            $this->loadData();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('newRole.name')) {
                $this->newRole['name'] = '';
            }
            throw $e;
        }
    }

    public function storePermission()
    {
        try {
            $this->validate([
                'newPermission.name' => 'required|string|unique:permissions,name',
            ], [
                'newPermission.name.required' => 'Permission Name Required',
                'newPermission.name.unique' => 'Name Taken',
            ]);

            Permission::create(['name' => $this->newPermission['name']]);

            $this->isCreating = false;
            $this->loadData();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has('newPermission.name')) {
                $this->newPermission['name'] = '';
            }
            throw $e;
        }
    }

    // --- UPDATE METHODS ---

    public function saveUser($index)
    {
        try {
            $userId = $this->users[$index]['id'];

            $this->validate([
                "users.$index.name"     => 'required|string|max:120',
                "users.$index.username" => 'required|string|unique:users,username,' . $userId,
                "users.$index.email"    => 'required|email|unique:users,email,' . $userId,
                "users.$index.password" => 'nullable|min:8|confirmed',
                "users.$index.role_ids" => 'nullable|array',
            ], [
                "users.$index.name.required"     => 'Name Required',
                "users.$index.username.required" => 'Username Required',
                "users.$index.email.required"    => 'Email Required',
            ]);

            $userData = $this->users[$index];
            $user = User::findOrFail($userData['id']);

            $updateData = [
                'name'     => $userData['name'],
                'username' => $userData['username'],
                'email'    => $userData['email'],
            ];

            if (!empty($userData['password'])) {
                $updateData['password'] = Hash::make($userData['password']);
            }

            $user->update($updateData);

            if (!empty($userData['role_ids'])) {
                $user->roles()->sync(array_map('intval', $userData['role_ids']));
            } else {
                $user->roles()->detach();
            }

            $this->editingId = null;
            $this->loadData();
        } catch (ValidationException $e) {
            $errors = $e->validator->errors();
            if ($errors->has("users.$index.name")) $this->users[$index]['name'] = '';
            if ($errors->has("users.$index.username")) $this->users[$index]['username'] = '';
            if ($errors->has("users.$index.email")) $this->users[$index]['email'] = '';
            if ($errors->has("users.$index.password")) {
                $this->users[$index]['password'] = '';
                $this->users[$index]['password_confirmation'] = '';
            }
            throw $e;
        }
    }

    public function saveRole($index)
    {
        try {
            $roleId = $this->roles[$index]['id'];

            $this->validate([
                "roles.$index.name" => 'required|string|min:3|unique:roles,name,' . $roleId,
                "roles.$index.permission_ids" => 'required|array',
            ], [
                "roles.$index.name.required" => 'Role Name Required',
            ]);

            $roleData = $this->roles[$index];
            $role = Role::findOrFail($roleData['id']);
            $role->name = $roleData['name'];
            $role->save();

            if (isset($roleData['permission_ids'])) {
                $role->permissions()->sync(array_map('intval', $roleData['permission_ids']));
            }

            $this->editingId = null;
            $this->loadData();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has("roles.$index.name")) {
                $this->roles[$index]['name'] = '';
            }
            throw $e;
        }
    }

    public function savePermission($index)
    {
        try {
            $this->validate([
                "permissions.$index.name" => 'required|string|unique:permissions,name,' . $this->permissions[$index]['id'],
            ], [
                "permissions.$index.name.required" => 'Permission Required',
            ]);

            $permData = $this->permissions[$index];
            Permission::findOrFail($permData['id'])->update(['name' => $permData['name']]);

            $this->editingId = null;
            $this->loadData();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has("permissions.$index.name")) {
                $this->permissions[$index]['name'] = '';
            }
            throw $e;
        }
    }

    public function confirmation($title, $functionName, $id = null) {
        LivewireAlert::title('')
            ->text($title)
            ->asConfirm()
            ->withOptions([
                'background' => '#f0f0f0',
                'customClass' => [
                    'popup' => 'animate__animated animate__bounceIn',
                ],
                'allowOutsideClick' => false,
            ])
            ->onConfirm($functionName, ['id' => $id])
            ->show();
    }

    public function deleteuser($id) {
        $this->confirmation('Are you sure you want to remove user#' . $id .'?','deleteUsereById', $id);
    }

    public function deleteUsereById($data) {
        $id = $data['id'];
        User::destroy($id);
        $this->loadData();
        LivewireAlert::title('Successfully deleted user!')->success()->position(Position::TopEnd)->toast()->show();
    }

    public function deleterole($id) {
        $this->confirmation('Are you sure you want to remove role#' . $id .'?','deleteRoleById', $id);
    }

    public function deleteRoleById($data) {
        $id = $data['id'];
        Role::destroy($id);
        $this->loadData();
        LivewireAlert::title('Successfully deleted role!')->success()->position(Position::TopEnd)->toast()->show();
    }

    public function deletepermission($id) {
        $this->confirmation('Are you sure you want to remove permission#' . $id .'?','deletePermissionById', $id);
    }

    public function deletePermissionById($data) {
        $id = $data['id'];
        Permission::destroy($id);
        $this->loadData();
        LivewireAlert::title('Successfully deleted permission!')->success()->position(Position::TopEnd)->toast()->show();
    }

    public function render()
    {
        return view('livewire.credentials')
            ->layoutData(['pageName' => 'Credentials'])
            ->title("Credentials");
    }
}