<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Book;
use App\Livewire\Forms\BookForm;

new class extends Component
{
    public BookForm $form;

    #[On('confirm-delete')]
    public function confirmDelete($id) {
        $book = Book::find($id);
        $this->form->setBook($book);
        Flux::modal('delete-book')->show();
    }

    public function deleteBook() {
        $this->form->book->delete();
        Flux::modal('delete-book')->close();
        session()->flash('success', 'Book deleted successfully');
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
        name="delete-book" 
        class="md:w-150" 
        x-on:close="$wire.resetForm()"
    >
     <form class="space-y-8" wire:submit.prevent="deleteBook">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">
                    Delete Book
                </flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    This action cannot be undone
                </flux:text>
            </div>

            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Delete</flux:button>
            </div>
        </form>
    </flux:modal>

</div>