<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;

class Posts extends Component
{
    use WithPagination;

    public $page = 1;
    public $search = '';
    protected $queryString = [
        'page',
        'search',
    ];

    public function invokePostId($id) {
        $this->postId = $id;
        $this->post = Post::find($this->postId);
        $this->dispatch('set-post', $id);
    }

    public function createNew() {
        $this->dispatch('create-new');
    }


    public function render()
    {
        $posts = Post::paginate(perPage: 10);

        return view('livewire.posts', ['posts' => $posts])
            ->title('Posts')
            ->layoutData(['pagename' => 'Posts']);
    }
}
