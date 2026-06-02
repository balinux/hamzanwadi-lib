<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <flux:heading size="xl" class="text-zinc-800 dark:text-white">Category</flux:heading>
    <flux:subheading size="lg" class="text-zinc-600 dark:text-zinc-400">Manage your categories</flux:subheading>
    <flux:separator variant="subtle" />

    <!-- modal -->
    <flux:modal.trigger name="create-category">
        <flux:button variant="primary" icon="plus" color="primary">Add Category</flux:button>
    </flux:modal.trigger>

     
</div>