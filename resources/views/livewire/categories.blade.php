<div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $categories->total() }} categories</p>
        <div class="flex flex-wrap items-center gap-3">
            <label class="sr-only" for="category-search">Search categories</label>
            <input id="category-search" type="search" wire:model.live.debounce.250ms="search"
                class="block h-10 w-full sm:w-64 rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Search categories">
            @role('superadmin|administrator')
            <button type="button" wire:click="createNew" wire:loading.attr="disabled"
                class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Create New
            </button>
            @endrole
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" role="status">
            {{ session('message') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th scope="col" class="px-4 py-3">Id</th>
                    <th scope="col" class="px-4 py-3">Category Name</th>
                    <th scope="col" class="px-4 py-3">Location</th>
                    <th scope="col" class="px-4 py-3">Updated</th>
                    <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr wire:key="category-{{ $category->id }}" class="border-b last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                        <td class="whitespace-nowrap px-4 py-3">{{ $category->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            @role('superadmin|administrator')
                            <button type="button" wire:click="invokeCategoryId({{ $category->id }})" class="text-left hover:text-blue-600 hover:underline">
                                {{ $category->category_name }}
                            </button>
                            @else
                                {{ $category->category_name }}
                            @endrole
                        </td>
                        <td class="px-4 py-3 break-all">{{ $category->location ?: '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ optional($category->updated_at)->format('M j, Y') ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            @role('superadmin|administrator')
                            <button type="button" wire:click="deleteCategory({{ $category->id }})" wire:confirm="Delete this category?" wire:loading.attr="disabled" title="Delete category" aria-label="Delete {{ $category->category_name }}"
                                class="h-8 w-8 rounded text-red-600 hover:bg-red-50 dark:text-red-400"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            @endrole
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="py-4">
        {{ $categories->links('livewire.pagination') }}
    </div>

    <livewire:category-item :initial-id="$initialCategoryId" :creating="$creating" />
</div>
