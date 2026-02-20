<?php

namespace App\Livewire;

use Livewire\Component;

class PostItem extends Component
{

    public function clearFields() {
        $this->resetValidation();
        $this->reset();

    }

    public function render()
    {
        return view('livewire.post-item');
    }
}
