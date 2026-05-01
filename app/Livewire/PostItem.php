<?php

namespace App\Livewire;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Component;
use App\Models\Post;

class PostItem extends Component
{
    use WithFileUploads;

    public $photo;
    public $postId;
    public $post = [
        'title' => '',
        'subtitle' => '',
        'post' => '',
    ];

    public function clearFields() {
        $this->photo = null;

        $this->resetValidation();
        $this->reset();
    }

    function savePost() {
        $this->validate(
            [
                'photo' => ['nullable', 'image', 'max:1024'],
                'post.title' => ['required'],
                'post.post' => ['required'],
            ]
        );

        $this->post['slug'] = Str::slug($this->post['title'],'-');
        $title = $this->post['p_title'] ?? $this->post['title'];
        $filename = $this->post['slug'] . '.' . $this->photo->getClientOriginalExtension();

        if ($this->photo) {
            $this->photo->storeAs('images', $filename ,'public');
            $imageLocation = base_path()."/storage/app/public/images/";
            File::move($imageLocation.$filename, public_path("/images/posts/$filename"));

            $this->adjustImage($filename);
            $this->post['image'] = $filename;
        } else {
            if (isset($this->post['image'])) {
                $filepath = pathinfo($this->post['image']);
                $this->post['image'] = $filepath['filename'].'.'.$filepath['extension'];
            }
            // dd($filepath);
        }

        unset($this->post['content']);

        if ($this->postId) {
            $post = Post::find($this->postId);
            $post->update($this->post);
        } else {
            Post::create($this->post);
        }

        $this->dispatch('display-message');
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
