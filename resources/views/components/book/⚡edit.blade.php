<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Book;
use App\Models\Category;
use App\Livewire\Forms\BookForm;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public BookForm $form;

    #[Computed]
    public function categories() {
        return Category::all();
    }

    #[On('edit-book')]
    public function editBook($id) {
        $book = Book::find($id);
        $this->form->setBook($book);
        Flux::modal('edit-book')->show();
    }

    public function updateBook() {
        $this->form->update();
        Flux::modal('edit-book')->close();
        session()->flash('success', 'Book updated successfully');
        $this->redirectRoute('book.index', navigate: true);
    }

    public function resetForm() {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal 
        name="edit-book" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()"
    >
     <form class="space-y-8" wire:submit.prevent="updateBook">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Edit Book
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    Edit your book details below
                </flux:text>
            </div>

            {{-- form field --}}
            <div class="space-y-6">
                <flux:input
                    label="Title"
                    placeholder="Enter title"
                    wire:model="form.title"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="ISBN"
                    placeholder="Enter ISBN"
                    wire:model="form.isbn"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Author"
                    placeholder="Enter author name"
                    wire:model="form.author"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Publisher"
                    placeholder="Enter publisher name"
                    wire:model="form.publisher"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Year"
                    placeholder="Enter year"
                    wire:model="form.publish_year"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Description"
                    placeholder="Enter description"
                    wire:model="form.description"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Total Copies"
                    placeholder="Enter total copies"
                    wire:model="form.total_copies"
                    wire:dirty.class.text-red-500
                />

                <flux:input
                    label="Stock"
                    placeholder="Enter stock"
                    wire:model="form.stock"
                    wire:dirty.class.text-red-500
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

            <div 
                wire:show ="$dirty"
                class="text-red-500 dark:text-red-400"
            >
                you have unsaved changes
            </div>

            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

</div>