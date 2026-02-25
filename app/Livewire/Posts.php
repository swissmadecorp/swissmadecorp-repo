<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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

    #[On('display-message')]
    public function displayMessage() {
        LivewireAlert::title('New Post Created')->success()->position(Position::TopEnd)->toast()->show();
    }

    public function render()
    {
        $posts = Post::latest()->paginate(15);

        return view('livewire.posts', ['posts' => $posts])
            ->title('Posts')
            ->layoutData(['pagename' => 'Posts']);
    }
}
