<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class Credentials extends Component
{
    public $users = [];

    public function deleteuser($id)
    {
        $user = \App\Models\User::find($id);
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
    }

    public function loadUsers()
    {
        // 2. We convert to array for better Alpine.js compatibility
        $this->users = User::all()->toArray();
    }

    public function render()
    {
        return view('livewire.credentials')
            ->layoutData(['pageName' => 'Credentials'])
            ->title("Credentials");
    }
}
