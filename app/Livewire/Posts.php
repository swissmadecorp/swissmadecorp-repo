<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;

class Posts extends Component
{
    use WithPagination;

    public function render()
    {
        $posts = Post::paginate(perPage: 10);

        return view('livewire.posts', ['posts' => $posts])
            ->title('Posts')
            ->layoutData(['pagename' => 'Posts']);
    }
}
