<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Book;

new class extends Component
{
    use WithPagination;

    public $sortBy = 'title';
    public $sortDirection = 'asc';
    public string $search = '';

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function books()
    {
        return Book::query()
            ->with('category')
            ->when($this->search, fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('isbn', 'like', "%{$this->search}%")
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(5);
    }

    public function edit($id){
        $this->dispatch('edit-book', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl">Buku</flux:heading>
    <flux:subheading size="lg">Kelola data buku perpustakaan</flux:subheading>
    <flux:separator variant="subtle" />

    <div class="flex items-center gap-3">
        <flux:modal.trigger name="create-book">
            <flux:button variant="primary" icon="plus">Tambah Buku</flux:button>
        </flux:modal.trigger>

        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari judul, ISBN, atau kategori..."
            icon="magnifying-glass"
            class="max-w-sm"
        />
    </div>

    <livewire:book.create />
    <livewire:book.edit />

    <x-flash-message />

    <div class="overflow-x-auto">
        <flux:table :paginate="$this->books">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'title'" :direction="$sortDirection" wire:click="sort('title')">Judul</flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column>Stok</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->books as $book)
                    <flux:table.row :key="$book->id">
                        <flux:table.cell>{{ $book->title }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc">{{ $book->category->name }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $book->stock }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $book->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete', {id: {{ $book->id }}})">Hapus</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>