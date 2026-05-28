---
title: "Tutorial Laravel 13 + Livewire 4+ Flux UI: Panduan Lengkap CRUD Book"
description: Pelajari cara membangun fitur manajemen buku (CRUD) interaktif menggunakan Laravel 13, Livewire 4 (Form Object & Single-File Component), serta Flux UI Library dengan relasi Kategori.
pubDatetime: 2026-05-27T08:50:00Z
tags:
  - laravel
  - livewire
  - flux
  - php
draft: false
---

Kombinasi **Laravel 13**, **Livewire 4**, dan **Flux UI** merupakan salah satu stack paling modern dan efisien untuk membangun aplikasi web interaktif tanpa harus keluar dari ekosistem PHP. 

**Flux UI** adalah library komponen UI resmi yang dirancang oleh Caleb Porzio (pencipta Livewire). Dengan Flux, kita bisa membuat antarmuka yang sangat indah, responsif, dan kaya fitur (seperti modal, sidebar, tabel, dll.) dengan sintaksis HTML-like yang bersih.

Dalam panduan ini, kita akan belajar langkah demi langkah membangun fitur **CRUD Book** (Manajemen Buku) yang memiliki relasi ke tabel `categories` secara lengkap dan rapi.

---

## Prasyarat & Persiapan Awal

Sebelum memulai, pastikan Anda telah menginstal proyek Laravel baru, mengonfigurasi database, serta memasang Livewire 3 dan Flux UI. Pastikan juga Anda sudah mengikuti tutorial Manajemen Kategori (CRUD Category) karena fitur Buku ini membutuhkan data Kategori.

Jika semua sudah siap, pastikan database sudah dimigrasi dan server aset Anda berjalan:

```bash
# Menjalankan build tools (Vite/Tailwind)
npm run dev
```

---

## Langkah 1: Memeriksa Model dan Migration `Book`

Di project ini, tabel `books` dan model `Book` sudah dipersiapkan sebelumnya dengan berbagai atribut untuk keperluan perpustakaan. Mari kita lihat struktur yang ada.

### 1. Struktur Migration
Berdasarkan file migration `2026_05_26_020014_create_books_table.php`, tabel `books` memiliki struktur berikut:

```php
// database/migrations/2026_05_26_020014_create_books_table.php
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->string('isbn', 13)->unique();
    $table->string('title');
    $table->string('author');
    $table->string('publisher')->nullable();
    $table->integer('publish_year')->nullable();
    $table->text('description')->nullable();
    $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
    $table->integer('stock');
    $table->integer('total_copies');
    $table->string('cover_path')->nullable();
    $table->timestamps();
});
```

### 2. Struktur Model
File model `app/Models/Book.php` sudah disiapkan dengan properti `$fillable` dan relasi ke `Category`:

```php
// app/Models/Book.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'description',
        'category_id',
        'stock',
        'total_copies',
        'cover_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

---

## Langkah 2: Membuat Komponen Livewire Index

Untuk menampilkan daftar buku, kita akan membuat sebuah komponen Livewire khusus halaman (*Full-page Component*). Pada Livewire 4, format default yang digunakan adalah **Single-File Component (SFC)**.

Jalankan perintah berikut untuk membuatnya:

```bash
php artisan make:livewire pages::book.index
```

Perintah di atas akan menghasilkan satu file komponen tunggal:
- **File SFC**: `resources/views/livewire/pages/book/index.blade.php`

### Mendaftarkan Route
Agar halaman ini bisa diakses lewat browser, tambahkan route baru di file `routes/web.php` menggunakan directive `Route::livewire()`:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::livewire('/books', 'pages::book.index')
    ->middleware(['auth'])
    ->name('book.index');
```

---

## Langkah 3: Membuat Livewire Form Object (`BookForm`)

Praktik terbaik di Livewire 3 untuk menangani input dan validasi formulir adalah menggunakan **Form Object**.

Buat Form Object baru dengan perintah:

```bash
php artisan livewire:form BookForm
```

Buka file `app/Livewire/Forms/BookForm.php` yang dihasilkan, lalu sesuaikan isinya untuk menangani proses validasi dari berbagai field `books`:

```php
<?php

namespace App\Livewire\Forms;

use App\Models\Book;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BookForm extends Form
{
    public string $isbn = '';
    public string $title = '';
    public string $author = '';
    public ?string $publisher = '';
    public ?int $publish_year = null;
    public string $description = '';
    public ?int $category_id = null;
    public int $stock = 0;
    public int $total_copies = 0;

    public ?Book $book = null;

    public function rules(): array
    {
        return [
            'isbn' => [
                'required',
                'string',
                'max:13',
                Rule::unique('books', 'isbn')->ignore($this->book?->id),
            ],
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|integer',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'total_copies' => 'required|integer|min:0',
        ];
    }

    public function setBook(Book $book): void
    {
        $this->book = $book;
        $this->isbn = $book->isbn;
        $this->title = $book->title;
        $this->author = $book->author;
        $this->publisher = $book->publisher;
        $this->publish_year = $book->publish_year;
        $this->description = $book->description ?? '';
        $this->category_id = $book->category_id;
        $this->stock = $book->stock;
        $this->total_copies = $book->total_copies;
    }

    public function store()
    {
        $this->validate();
        Book::create($this->except(['book']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->book->update($this->except(['book']));
    }
}
```
*(Catatan: Untuk penyederhanaan pada tutorial ini, kita tidak menyertakan upload file `cover_path` pada form)*

---

## Langkah 4: Menulis Logika Komponen Livewire Index

Buka file komponen utama di `resources/views/livewire/pages/book/index.blade.php`. Karena komponen ini dibuat sebagai **Single-File Component**, pertama kita akan menulis blok logika PHP (`<?php ... ?>`) di bagian paling atas file tersebut:

```php
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
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }

    public function edit($id){
        $this->dispatch('edit-book', id: $id);
    }
};
?>
```

---

## Langkah 5: Mendesain UI Modern dengan Flux UI

Tepat di bawah blok PHP (`?>`) pada file komponen `resources/views/livewire/pages/book/index.blade.php` yang sama, tuliskan kode Blade berikut untuk menyusun tampilan antarmuka (UI):

```php
<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Books</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your books collection</flux:subheading>
    <flux:separator variant="subtle" />

    <!-- modal -->
    <flux:modal.trigger name="create-book">
        <flux:button variant="primary" icon="plus" color="primary">Add Book</flux:button>
    </flux:modal.trigger>

    <livewire:book.create />
    <livewire:book.edit />

    <x-flash-message />

    {{-- table --}}
    <div class="overflow-x-auto">
       <flux:table :paginate="$this->books">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'isbn'" :direction="$sortDirection" wire:click="sort('isbn')">ISBN</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">Title</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'author'" :direction="$sortDirection" wire:click="sort('author')">Author</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'stock'" :direction="$sortDirection" wire:click="sort('stock')">Stock</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->books as $book)
                    <flux:table.row :key="$book->id">
                        
                        <flux:table.cell class="whitespace-nowrap">{{ $book->isbn }}</flux:table.cell>

                        <flux:table.cell class="flex items-center gap-3">
                            <span class="font-medium">{{ $book->title }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $book->author }}
                        </flux:table.cell>
                        
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $book->category->name ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $book->stock }} / {{ $book->total_copies }}
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
```

---

## Langkah 6: Membuat Komponen Create Book (Single-File Component)

Selanjutnya, kita akan membuat komponen untuk menambahkan buku baru. Kita juga akan mengambil data Kategori untuk ditampilkan sebagai opsi pada input `<flux:select>`.

Buatlah file baru di `resources/views/livewire/book/create.blade.php` dengan perintah:

```bash
php artisan make:livewire book.create
```

Lalu tambahkan kode berikut:

```php
<?php

use App\Livewire\Forms\BookForm;
use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public BookForm $form;

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function save()
    {
        $this->form->store();
        Flux::modal('create-book')->close();
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
    <flux:modal name="create-book" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Create Book</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Add a new book to your library</flux:text>
            </div>

            {{-- form fields --}}
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="ISBN" placeholder="Enter ISBN (Max 13)" wire:model="form.isbn" />
                    <flux:select label="Category" wire:model="form.category_id" placeholder="Choose category...">
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input label="Title" placeholder="Enter book title" wire:model="form.title" />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Author" placeholder="Enter author name" wire:model="form.author" />
                    <flux:input label="Publisher" placeholder="Enter publisher" wire:model="form.publisher" />
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <flux:input type="number" label="Year" placeholder="e.g. 2023" wire:model="form.publish_year" />
                    <flux:input type="number" label="Stock" wire:model="form.stock" />
                    <flux:input type="number" label="Total Copies" wire:model="form.total_copies" />
                </div>

                <flux:textarea label="Description" placeholder="Enter book description" wire:model="form.description" />
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
```

## Langkah 7: Membuat Komponen Edit & Delete Book (Single-File Component)

Untuk melengkapi fungsionalitas CRUD secara menyeluruh, kita akan membuat satu komponen Single-File Component lagi yang bertugas menangani aksi pengubahan (Edit) dan penghapusan (Delete) buku.

Buatlah file komponen baru di `resources/views/livewire/book/edit.blade.php` dengan perintah:

```bash
php artisan make:livewire book.edit
```

Lalu isi kode sebagai berikut:

```php
<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\BookForm;
use App\Models\Book;
use App\Models\Category;

new class extends Component
{
    public BookForm $form;

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[On('edit-book')]
    public function editBook($id){
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

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete')]
    public function confirmDelete($id)
    {
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
};
?>

<div>
    <flux:modal name="edit-book" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="updateBook">
            {{-- header --}}
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Edit Book</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">Edit your book details below</flux:text>
            </div>

            {{-- form fields --}}
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="ISBN" wire:model="form.isbn" wire:dirty.class.text-red-500 />
                    <flux:select label="Category" wire:model="form.category_id">
                        @foreach ($this->categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input label="Title" wire:model="form.title" wire:dirty.class.text-red-500 />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Author" wire:model="form.author" wire:dirty.class.text-red-500 />
                    <flux:input label="Publisher" wire:model="form.publisher" wire:dirty.class.text-red-500 />
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <flux:input type="number" label="Year" wire:model="form.publish_year" wire:dirty.class.text-red-500 />
                    <flux:input type="number" label="Stock" wire:model="form.stock" wire:dirty.class.text-red-500 />
                    <flux:input type="number" label="Total Copies" wire:model="form.total_copies" wire:dirty.class.text-red-500 />
                </div>

                <flux:textarea label="Description" wire:model="form.description" wire:dirty.class.text-red-500 />
            </div>

            <div wire:show="$dirty" class="text-red-500 dark:text-red-400">you have unsaved changes</div>
    
            {{-- footer --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- delete modal --}}
    <flux:modal name="delete-book" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="deleteBook">
            <div class="space-y-2">
                <flux:heading size="lg" class="text-zinc-900 dark:text-white">Delete Book</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">this action cannot be undone</flux:text>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Cancel</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Delete</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
```

## Langkah 8: Memperbarui Sidebar Navigasi

Tambahkan baris berikut di dalam komponen sidebar Anda:

```php
<flux:sidebar.item icon="book-open" 
	:href="route('book.index')" 
	:current="request()->routeIs('book.index')" 
	wire:navigate
>

{{ __('Books') }}

</flux:sidebar.item>
```

---

## Tips Tambahan: Membuat Komponen Notifikasi (Flash Message) Kustom

Jika Anda ingin notifikasi sukses yang tampil lebih modular di berbagai halaman, Anda bisa membuat komponen Blade kustom:

```bash
php artisan make:component FlashMessage --view
```

Buka file komponen di `resources/views/components/flash-message.blade.php` dan buatlah komponen notifikasi yang cantik:

```php
@foreach (['success','error','warning','info'] as $type)
    @php
        $bgColor = match($type) {
            'success' => 'bg-green-600 text-white',
            'error' => 'bg-red-600 text-white',
            'warning' => 'bg-yellow-600 text-white',
            'info' => 'bg-blue-600 text-white',
        };
    @endphp

    @if (session()->has($type))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4"
            class="{{ $bgColor }} fixed top-4 right-4 z-50 p-4 mb-4 text-sm rounded-lg" role="alert">
            <span class="font-medium">{{ ucfirst($type) }}!</span> {{ session($type) }}
        </div>
    @endif
@endforeach
```

Sekarang, Anda cukup meletakkan `<x-flash-message />` di layout utama aplikasi (`app.blade.php`), dan setiap notifikasi sukses yang dikirim lewat `session()->flash('success')` akan muncul secara elegan dengan animasi halus di pojok kanan bawah!

---

## Ringkasan Alur Kerja

Dengan kombinasi ini, alur kerja pengembangan Anda menjadi sangat efisien:
1. **Model & Migration** untuk mendefinisikan database schema.
2. **Form Object (`BookForm`)** untuk merapikan urusan validasi formulir dan binding data.
3. **Livewire Component (`Index`)** sebagai pengendali logika halaman.
4. **Flux UI Components** untuk membangun antarmuka premium secara deklaratif tanpa pusing menulis CSS tambahan atau library JS eksternal.

Selamat mencoba membangun aplikasi hebat Anda berikutnya dengan Laravel, Livewire, dan Flux UI!
