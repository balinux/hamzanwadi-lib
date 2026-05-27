<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Book;

new class extends Component
{
    use WithPagination;

    public $sortBy = 'title';
    public $sortDirection = 'desc';
    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    
    #[Computed]
    public function books()
    {
        return Book::query()
            ->with('category')
            ->when(
                $this->sortBy,
                fn ($q) => $q->orderBy($this->sortBy, $this->sortDirection)
            )
            ->when(
                $this->search,
                fn ($q) => $q->where('title', 'like', "%{$this->search}%")
            )
            ->paginate(5);
    }

    public function edit($id){
        $this->dispatch('edit-book', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Book</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your books</flux:subheading>
    <flux:separator variant="subtle" />

    <!-- modal -->
    <flux:modal.trigger name="create-book">
        <flux:button variant="primary" icon="plus" color="primary">Add Book</flux:button>
    </flux:modal.trigger>

    <livewire:book.create />
    <livewire:book.edit />
    <livewire:book.delete />

    <x-flash-message />

    {{-- search --}}
    <div class="flex items-center gap-4">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Search books..."
            icon="magnifying-glass"
            class="w-72"
        />
    </div>

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->books">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">Title</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'author'" :direction="$sortDirection" wire:click="sort('author')">Author</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'publisher'" :direction="$sortDirection" wire:click="sort('publisher')">Publisher</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'publish_year'" :direction="$sortDirection" wire:click="sort('publish_year')">Year</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'stock'" :direction="$sortDirection" wire:click="sort('stock')">Stock</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'category_id'" :direction="$sortDirection" wire:click="sort('category_id')">Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Created At</flux:table.column>
                <flux:table.column>Cover</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->books as $book)
                    <flux:table.row :key="$book->id">
                        
                        <flux:table.cell class="flex items-center gap-3">
                            {{ $book->title }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->author }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->publisher }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->publish_year }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->stock }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->category->name }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">{{ $book->created_at->diffForHumans() }}</flux:table.cell>

                        <flux:table.cell>
                            @if($book->cover_path)
                                <img 
                                    src="{{ asset('storage/' . $book->cover_path) }}" 
                                    alt="{{ $book->title }}"
                                    class="w-14 h-14 object-cover rounded-full"
                                >
                            @else
                                <div class="w-14 h-14 bg-zinc-200 rounded-full flex items-center justify-center text-zinc-500">
                                    No Cover
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $book->id }})">Edit</flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $book->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>