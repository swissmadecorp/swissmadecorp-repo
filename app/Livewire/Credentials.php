<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Credentials extends Component
{
    public $users = [];
    public $roles = [];
    public $permissions = [];
    public $status = '';

    public $activeTab = null;
    public $editingId = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->loadUsers();
        $this->loadRoles();
        $this->loadPermissions();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function resetUIErrors()
    {
        $this->resetValidation();
    }

    public function startEdit($id)
    {
        $this->editingId = $id;
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
        $this->users = User::latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            })
            ->toArray();
    }

    public function loadRoles()
    {
        $this->roles = Role::with('permissions')
            ->get()
            ->map(function ($role) {
                return [
                    'id'               => $role->id,
                    'name'             => $role->name,
                    'permissions_list' => $role->permissions->pluck('name')->implode(', '),
                    'created_at'       => $role->created_at->format('d/m/Y'),
                    'permission_ids'   => $role->permissions->pluck('id')->toArray(),
                ];
            })
            ->toArray();
    }

    public function loadPermissions()
    {
        $this->permissions = Permission::latest()
            ->get()
            ->map(function ($permission) {
                return [
                    'id'         => $permission->id,
                    'name'       => $permission->name,
                    'created_at' => $permission->created_at->format('d/m/Y'),
                ];
            })
            ->toArray();
    }

    public function saveUser($index)
    {
        try {
            // Validate specific fields
            $this->validate([
                "users.$index.name"     => 'required|string|min:3',
                "users.$index.username" => 'required|string',
                "users.$index.email"    => 'required|email|unique:users,email,' . $this->users[$index]['id'],
            ], [
                // Custom messages to hide "users.0.name"
                "users.$index.name.required"     => 'Name Required',
                "users.$index.name.min"          => 'Min 3 Chars',
                "users.$index.username.required" => 'Username Required',
                "users.$index.email.required"    => 'Email Required',
                "users.$index.email.email"       => 'Invalid Email',
                "users.$index.email.unique"      => 'Email Taken',
            ]);

            $userData = $this->users[$index];
            User::find($userData['id'])->update([
                'name'     => $userData['name'],
                'username' => $userData['username'],
                'email'    => $userData['email'],
            ]);

            $this->editingId = null;
            $this->loadData();
        } catch (ValidationException $e) {
            // Only clear the specific fields that failed so the placeholder error shows
            $errors = $e->validator->errors();

            if ($errors->has("users.$index.name")) {
                $this->users[$index]['name'] = '';
            }
            if ($errors->has("users.$index.username")) {
                $this->users[$index]['username'] = '';
            }
            if ($errors->has("users.$index.email")) {
                $this->users[$index]['email'] = '';
            }

            throw $e;
        }
    }

    public function saveRole($index)
    {
        try {
            $this->validate([
                "roles.$index.name" => 'required|string|min:3|unique:roles,name,' . $this->roles[$index]['id'],
            ], [
                "roles.$index.name.required" => 'Role Name Required',
                "roles.$index.name.unique"   => 'Name Taken',
            ]);

            $roleData = $this->roles[$index];
            $role = Role::findById($roleData['id']);
            $role->name = $roleData['name'];
            $role->save();

            if (isset($roleData['permission_ids'])) {
                $role->syncPermissions($roleData['permission_ids']);
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
                "permissions.$index.name.unique"   => 'Name Taken',
            ]);

            $permData = $this->permissions[$index];
            Permission::findById($permData['id'])->update(['name' => $permData['name']]);

            $this->editingId = null;
            $this->loadData();
        } catch (ValidationException $e) {
            if ($e->validator->errors()->has("permissions.$index.name")) {
                $this->permissions[$index]['name'] = '';
            }
            throw $e;
        }
    }

    public function deleteuser($id) { User::destroy($id); $this->loadData(); }
    public function deleterole($id) { Role::destroy($id); $this->loadData(); }
    public function deletepermission($id) { Permission::destroy($id); $this->loadData(); }

    public function render()
    {
        return view('livewire.credentials')
            ->layoutData(['pageName' => 'Credentials'])
            ->title("Credentials");
    }
}