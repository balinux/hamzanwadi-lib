<?php

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function categories(){
        // cara debugging agar kita bisa melihat data sebelum di tampilkan
        // dd(Category::all()->toArray());

        return Category::paginate(2);
    }

};
?>

<div>
    
<flux:table :paginate="$this->categories">

    <flux:table.columns>
        <flux:table.column>Name</flux:table.column>
        <flux:table.column>Description</flux:table.column>
        <flux:table.column>Action</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($this->categories as $category)
        <flux:table.row>
            <flux:table.cell>{{$category->name}}</flux:table.cell>
            <flux:table.cell>{{$category->description}}</flux:table.cell>
            <flux:table.cell>
               
            </flux:table.cell>
        </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
</div>