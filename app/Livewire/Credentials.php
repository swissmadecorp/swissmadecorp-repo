<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class Credentials extends Component
{
    public $users = [];
    public $roles = [];

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

    public function mount()
    {
        $this->loadUsers();
        $this->loadRoles();
    }

    public function loadUsers()
    {
        // 2. We convert to array for better Alpine.js compatibility
        $this->users = User::latest()->get()->toArray();
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
                'created_at' => $role->created_at,
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
