<?php

namespace App\Livewire\Forms;

use App\Models\Book;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BookForm extends Form
{
    public string $title = '';

    public string $isbn = '';

    public string $author = '';

    public string $publisher = '';

    public string $publish_year = '';

    public string $stock = '';

    public string $category_id = '';

    public $cover_path = '';

    public string $total_copies = '';

    public string $description = '';

    public ?Book $book = null;

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('books', 'title')->ignore($this->book?->id),
            ],
            'isbn' => [
                'required',
                'string',
                'min:3',
                'max:13',
                Rule::unique('books', 'isbn')->ignore($this->book?->id),
            ],
            'author' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'publisher' => [
                'nullable',
                'string',
                'min:3',
                'max:255',
            ],
            'publish_year' => [
                'nullable',
                'integer',
                'min:1000',
                'max:9999',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'total_copies' => [
                'required',
                'integer',
                'min:0',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'cover_path' => [
                'nullable',
                'file',
                'image',
                'max:2048',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function setBook(Book $book): void
    {
        $this->book = $book;
        $this->title = $book->title;
        $this->isbn = $book->isbn;
        $this->author = $book->author;
        $this->publisher = $book->publisher;
        $this->publish_year = $book->publish_year;
        $this->stock = $book->stock;
        $this->total_copies = $book->total_copies;
        $this->category_id = $book->category_id;
        $this->cover_path = $book->cover_path;
        $this->description = $book->description;
    }

    public function store()
    {
        $this->validate();

        // upload cover image
        if ($this->cover_path) {
            $this->cover_path = $this->cover_path->store('covers', 'public');
        }

        Book::create($this->only([
            'title',
            'isbn',
            'author',
            'publisher',
            'publish_year',
            'stock',
            'total_copies',
            'category_id',
            'cover_path',
            'description',
        ]));
        $this->reset();
    }

    // update
    public function update()
    {
        $this->validate();

        // upload cover image if new file is provided
        if ($this->cover_path && is_object($this->cover_path)) {
            // delete old cover if exists
            if ($this->book->cover_path) {
                \Storage::disk('public')->delete($this->book->cover_path);
            }
            $this->cover_path = $this->cover_path->store('covers', 'public');
        } else {
            // keep existing cover path
            $this->cover_path = $this->book->cover_path;
        }

        $this->book->update($this->only([
            'title',
            'isbn',
            'author',
            'publisher',
            'publish_year',
            'stock',
            'total_copies',
            'category_id',
            'cover_path',
            'description',
        ]));
    }
}