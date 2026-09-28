<div x-data="{ open: $wire.entangle('isOpen'), uploading: false }"
    @keydown.escape.window="if (open && !uploading) $wire.clearFields()"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-effect="if (open) $nextTick(() => $refs.categoryName.focus())"
    x-show="open" x-cloak class="fixed inset-0 z-50" aria-labelledby="category-editor-title" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-gray-900/60" @click="if (!uploading) $wire.clearFields()"></div>

    <section x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto bg-white p-6 shadow-xl dark:bg-gray-900">
        <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
            <h2 id="category-editor-title" class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $categoryId ? 'Edit category' : 'New category' }}
            </h2>
            <button type="button" wire:click="clearFields" :disabled="uploading" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white" aria-label="Close">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <form wire:submit="saveCategory" class="space-y-4">
            <div>
                <label for="category-name" class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Category Name</label>
                <input id="category-name" x-ref="categoryName" type="text" wire:model="category.category_name" maxlength="191" required
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @error('category.category_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category-location" class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Category Location</label>
                <input id="category-location" type="text" wire:model="category.location"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @error('category.location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category-title" class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Category Title</label>
                <input id="category-title" type="text" wire:model="category.category_title"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @error('category.category_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category-description" class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Category Description</label>
                <textarea id="category-description" rows="7" wire:model="category.category_description"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                @error('category.category_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category-photo" class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">Image</label>
                <input id="category-photo" type="file" accept="image/jpeg,image/png,image/gif,image/webp" wire:model="photo"
                    class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                @if ($photo && !$errors->has('photo') && $photo->isPreviewable())
                    <img src="{{ $photo->temporaryUrl() }}" class="mt-3 h-32 w-32 rounded border object-contain" alt="Selected category image">
                @elseif ($existingImageUrl)
                    <div class="mt-3 flex items-start gap-3">
                        <img src="{{ $existingImageUrl }}" class="h-32 w-32 rounded border object-contain" alt="Current category image">
                        <button type="button" wire:click="removeImage" class="text-sm font-medium text-red-600 hover:underline">Remove image</button>
                    </div>
                @endif
                <p x-show="uploading" role="status" class="mt-2 text-sm text-gray-500">Uploading image...</p>
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                <button type="button" wire:click="clearFields" :disabled="uploading" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" :disabled="uploading" wire:loading.attr="disabled" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 disabled:opacity-50"><i class="fas fa-save mr-1" aria-hidden="true"></i> Save Category</button>
            </div>
        </form>
    </section>
</div>
