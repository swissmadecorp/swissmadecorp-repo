<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $search = '';

    #[Locked]
    public ?int $initialCategoryId = null;

    #[Locked]
    public bool $creating = false;

    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function mount(?int $id = null): void
    {
        $this->initialCategoryId = $id;
        $this->creating = request()->routeIs('categories.create');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function invokeCategoryId(int $id): void
    {
        Category::findOrFail($id);

        $this->dispatch('set-category', id: $id)->to(CategoryItem::class);
    }

    public function createNew(): void
    {
        $this->dispatch('create-category')->to(CategoryItem::class);
    }

    public function deleteCategory(int $id): void
    {
        abort_unless(auth()->user()->hasAnyRole(['superadmin', 'administrator']), 403);
        $category = Category::findOrFail($id);
        $category->delete();

        $this->resetPage();
        session()->flash('message', "Successfully deleted {$category->category_name} category!");
    }

    #[On('category-saved')]
    public function categorySaved(): void
    {
        $this->resetPage();
        session()->flash('message', 'Category saved successfully.');
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search !== '', function ($query) {
                $query->where('category_name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.categories', compact('categories'))
            ->title('Categories')
            ->layoutData(['pageName' => 'Categories']);
    }
}
