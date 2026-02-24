<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Models\Post;

class PostItem extends Component
{
    public $postId;
    public $post = [
        'title' => '',
        'subtitle' => '',
        'post' => '',
    ];

    public function clearFields() {
        $this->resetValidation();
        $this->reset();
    }

    function savePost() {
        $this->validate(
            [
                'post.title' => ['required'],
                'post.post' => ['required'],
            ]
        );


        $this->post['slug'] = Str::slug($this->post['title'],'-');
        unset($this->post['content']);

        if ($this->postId) {
            $post = Post::find($this->postId);
            $post->update($this->post);
        } else {
            Post::create($this->post);
        }

        $this->dispatch('refresh-posts');
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
