<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Post;

class PostItem extends Component
{
    public $postId;
    public $post;

    public function clearFields() {
        $this->resetValidation();
        $this->reset();
    }


    #[On('set-post')]
    public function setPost($id) {
        $this->postId = $id;
        $this->post = Post::find($this->postId)->toArray();
    }

    public function render()
    {
        return view('livewire.post-item');
    }
}
