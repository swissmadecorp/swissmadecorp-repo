<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Post;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use App\SearchCriteriaTrait;

class Posts extends Component
{
    use WithPagination, SearchCriteriaTrait;

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

    public function estimateReadTime($postContent) {
        $wordCount = str_word_count(trim(preg_replace('/\s+/', ' ', strip_tags($postContent ?? ''))));

        return max(3, (int) ceil($wordCount / 220));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createNew() {
        $this->dispatch('create-new');
    }

    #[On('display-message')]
    public function displayMessage() {
        LivewireAlert::title('New Post Created')->success()->position(Position::TopEnd)->toast()->show();
    }

    // mosel, yonatan, yonatan, shalom, boris, levy, gaby, ephraim, dan, and ben
    public function render()
    {
        $columns = ['title','subtitle'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        $posts = Post::when(strlen($searchTerm) > 0, function ($query) use ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                // Use the raw search term (for the `orders` table)
                $q->whereRaw($searchTerm);
            });
        })->latest()->paginate(15);

        $posts->getCollection()->transform(function ($post) {
            $wordCount = str_word_count(
                trim(preg_replace('/\s+/', ' ', strip_tags($post->post)))
            );

            $post->read_time = max(3, (int) ceil($wordCount / 220));

            return $post;
        });

        return view('livewire.posts', ['posts' => $posts])
            ->title('Posts')
            ->layoutData(['pageName' => 'Posts']);
    }
}
