<?php

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\BookForm;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public BookForm $form;

    #[Computed]
    public function categories()
    {
        return Category::all();
    }

    public function save()
    {
        $this->form->store();
        Flux::modal('create-book')->close();

        // session
        session()->flash('success', 'Book created successfully');

        $this->redirectRoute('book.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal 
        name="create-book" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()" 
    >
        <form class="space-y-8" wire:submit.prevent="save">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Create Book
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Add a new book to your library
                </flux:text>
            </div>

            {{-- form field --}}
            <div class="space-y-6">
                <flux:input
                    label="Title"
                    placeholder="Enter title"
                    wire:model="form.title"
                />

                <flux:input
                    label="ISBN"
                    placeholder="Enter ISBN"
                    wire:model="form.isbn"
                />

                <flux:input
                    label="Author"
                    placeholder="Enter author name"
                    wire:model="form.author"
                />

                <flux:input
                    label="Publisher"
                    placeholder="Enter publisher name"
                    wire:model="form.publisher"
                />

                <flux:input
                    label="Year"
                    placeholder="Enter year"
                    wire:model="form.publish_year"
                />

                <flux:input
                    label="Description"
                    placeholder="Enter description"
                    wire:model="form.description"
                />

                <flux:input
                    label="Total Copies"
                    placeholder="Enter total copies"
                    wire:model="form.total_copies"
                />

                <flux:input
                    label="Stock"
                    placeholder="Enter stock"
                    wire:model="form.stock"
                />

                <flux:select label="Category" wire:model="form.category_id" placeholder="Choose category...">
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    label="Cover"
                    placeholder="Enter cover"
                    wire:model="form.cover_path"
                    type="file"
                    accept="image/*"
                />
            </div>
    
            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>