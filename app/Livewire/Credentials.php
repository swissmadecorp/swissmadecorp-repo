<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class Credentials extends Component
{
    public $users = [];
    public $roles = [];
    public $permissions = [];
    public $status = '';

    public function deleteuser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            session()->flash('message', 'User deleted successfully.');
        } else {
            session()->flash('error', 'User not found.');
        }
    }

    public function resetUIErrors() {
        $this->resetErrorBag();
    }

    public function loadData()
    {
        $this->loadUsers();
        $this->loadRoles();
        $this->loadPermissions();
    }

    public function mount()
    {
        $this->loadData();
    }

    public function saveUser($data) {
        $this->resetErrorBag();

        $validator = Validator::make($data, [
            'id'       => 'required|exists:users,id',
            'name'     => 'required|string|min:3',
            'username' => 'required|string',
            'email'    => 'required|email|unique:users,email,' . $data['id'],
        ], [
            'name.required' => 'The name cannot be empty.',
        ]);

        $validator->validate();

dd($data);
        $user = User::find($data['id']);
        $user->update($data);
        $this->loadData();
    }

    public function saveRole($data) {
        $role = Role::find($data['id']);

        $validatedData = Validator::make($data, [
            'name' => 'required|string|min:3|unique:roles,name,' . $data['id'],
            // Since permissions in your table are just a list string,
            // we validate that the data exists.
            'permissions_list' => 'required',
        ])->validate();


        $input = $request->except(['permissions']);
        $permissions = $request['permissions'];
        $role->fill($input)->save();

        $p_all = Permission::all();

        foreach ($p_all as $p) {
            $role->revokePermissionTo($p);
        }

        foreach ($permissions as $permission) {
            $p = Permission::where('id', $permission)->firstOrFail();
            $role->givePermissionTo($p);
            //dd($p);
        }

        $this->loadData();
    }

    public function savePermission($data) {
        Permission::find($data['id'])->update(['name' => $data['name']]);
        $this->loadData();
    }

    public function loadUsers()
    {
        // 2. We convert to array for better Alpine.js compatibility
        $this->users = User::latest()
        ->get()
        ->map(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                // Format directly in PHP
                'created_at' => $user->created_at->format('d/m/Y'),
            ];
        })
        ->toArray();
    }

    public function loadRoles()
    {
        // 2. We convert to array for better Alpine.js compatibility
        $this->roles = Role::with('permissions')
        ->get()
        ->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_list' => $role->permissions->pluck('name')->implode(', '),
                'created_at' => $role->created_at->format('d/m/Y'),
                // Flatten the permissions into a comma-separated string here
            ];
        })
        ->toArray();
    }

    public function loadPermissions()
    {
        // 2. We convert to array for better Alpine.js compatibility
        $this->permissions = Permission::latest()
        ->get()
        ->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'created_at' => $permission->created_at->format('d/m/Y'),
                // Flatten the permissions into a comma-separated string here
            ];
        })
        ->toArray();
    }

    public function render()
    {
        return view('livewire.credentials')
            ->layoutData(['pageName' => 'Credentials'])
            ->title("Credentials");
    }
}
