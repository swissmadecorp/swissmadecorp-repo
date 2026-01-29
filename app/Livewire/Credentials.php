<?php

namespace App\Livewire;

use Livewire\Component;

class Credentials extends Component
{
    public function render()
    {
        return view('livewire.credentials')
            ->layoutData(['pageName' => 'Credentials'])
            ->title("Credentials");
    }
}
