<?php

namespace App\Livewire;

use Livewire\Component;

class Posts extends Component
{
    public $posts;
    public function render()
    {
        $this->posts = Post::all();
        return view('livewire.posts');
    }
}
