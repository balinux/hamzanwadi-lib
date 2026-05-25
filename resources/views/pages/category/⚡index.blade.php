<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Category;

new class extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'desc';

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    
    #[Computed]
    public function categories()
    {
        // return Category::latest()->paginate(2);
        
        return Category::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }

    public function edit($id){
        $this->dispatch('edit-category', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Category</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your categories</flux:subheading>
    <flux:separator variant="subtle" />

    <!-- modal -->
    <flux:modal.trigger name="create-category">
        <flux:button variant="primary" icon="plus" color="primary">Add Category</flux:button>
    </flux:modal.trigger>

    <livewire:category.create />
    <livewire:category.edit />

    <x-flash-message />

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Name</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Created At</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->categories as $category)
                    <flux:table.row :key="$category->id">
                        
                        <flux:table.cell class="flex items-center gap-3">
                            {{ $category->name }}
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $category->description ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">{{ $category->created_at->diffForHumans() }}</flux:table.cell>

                        <flux:table.cell>


                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $category->id }})">Edit</flux:menu.item>

                                    <flux:menu.separator />

                                    {{-- <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', id: $category->id)">Delete</flux:menu.item> --}}
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $category->id }}})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>


    </div>
   
</div>
