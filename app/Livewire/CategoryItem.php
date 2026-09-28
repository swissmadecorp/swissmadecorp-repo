<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CategoryItem extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $categoryId = null;

    public bool $isOpen = false;

    public bool $removeExistingImage = false;

    public array $category = [
        'category_name' => '',
        'location' => '',
        'category_title' => '',
        'category_description' => '',
    ];

    public $photo = null;

    public ?string $existingImageUrl = null;

    public function boot(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function mount(?int $initialId = null, bool $creating = false): void
    {
        if ($initialId !== null) {
            $this->setCategory($initialId);
        } elseif ($creating) {
            $this->createCategory();
        }
    }

    private function authorizeEditor(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['superadmin', 'administrator']), 403);
    }

    #[On('create-category')]
    public function createCategory(): void
    {
        $this->authorizeEditor();
        $this->clearFields();
        $this->isOpen = true;
    }

    #[On('set-category')]
    public function setCategory(int $id): void
    {
        $this->authorizeEditor();
        $this->clearFields();
        $category = Category::findOrFail($id);

        $this->categoryId = $category->id;
        $this->category = [
            'category_name' => (string) $category->category_name,
            'location' => (string) ($category->location ?? ''),
            'category_title' => (string) ($category->category_title ?? ''),
            'category_description' => (string) ($category->category_description ?? ''),
        ];
        $this->photo = null;
        $this->existingImageUrl = $this->imageUrl($category->image_name);
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function clearFields(): void
    {
        $this->resetValidation();
        $this->categoryId = null;
        $this->isOpen = false;
        $this->removeExistingImage = false;
        $this->photo = null;
        $this->existingImageUrl = null;
        $this->category = [
            'category_name' => '',
            'location' => '',
            'category_title' => '',
            'category_description' => '',
        ];
    }

    public function saveCategory(): void
    {
        $this->authorizeEditor();
        $this->category['category_name'] = trim((string) ($this->category['category_name'] ?? ''));
        $this->category['location'] = trim((string) ($this->category['location'] ?? ''));
        if ($this->category['location'] === '') {
            $this->category['location'] = Str::slug($this->category['category_name']);
        }
        $this->validate([
            'category.category_name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('categories', 'category_name')->ignore($this->categoryId),
            ],
            'category.location' => ['nullable', 'string', 'max:191'],
            'category.category_title' => ['nullable', 'string', 'max:254'],
            'category.category_description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        $category = $this->categoryId
            ? Category::findOrFail($this->categoryId)
            : new Category();

        $category->category_name = trim($this->category['category_name']);
        $category->location = trim((string) ($this->category['location'] ?: Str::slug($category->category_name)));
        $category->category_title = $this->category['category_title'] ?? null;
        $category->category_description = $this->category['category_description'] ?? null;
        $oldImage = $category->image_name;
        if ($this->removeExistingImage) {
            $category->image_name = null;
        }

        if ($this->photo) {
            $filename = Str::uuid() . '.' . $this->photo->extension();
            $directory = public_path('images/categories');
            $thumbDirectory = $directory . DIRECTORY_SEPARATOR . 'thumbs';
            File::ensureDirectoryExists($directory);
            File::ensureDirectoryExists($thumbDirectory);
            File::copy($this->photo->getRealPath(), $directory . DIRECTORY_SEPARATOR . $filename);
            File::copy($this->photo->getRealPath(), $thumbDirectory . DIRECTORY_SEPARATOR . $filename);
            $category->image_name = $filename;
        }

        $category->save();

        if ($this->removeExistingImage && $oldImage) {
            File::delete([
                public_path('images/categories/' . $oldImage),
                public_path('images/categories/thumbs/' . $oldImage),
            ]);
        }

        $this->categoryId = $category->id;
        $this->existingImageUrl = $this->imageUrl($category->image_name);
        $this->photo = null;
        $this->dispatch('category-saved');
        $this->clearFields();
    }

    public function removeImage(): void
    {
        $this->authorizeEditor();
        $this->removeExistingImage = true;
        $this->photo = null;
        $this->existingImageUrl = null;
    }

    private function imageUrl(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        $thumb = public_path('images/categories/thumbs/' . $image);
        $full = public_path('images/categories/' . $image);

        return file_exists($thumb)
            ? asset('images/categories/thumbs/' . $image)
            : (file_exists($full) ? asset('images/categories/' . $image) : null);
    }

    public function render()
    {
        return view('livewire.category_item');
    }
}
