<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\BookForm;
use App\Models\Book;
use App\Models\Category;

new class extends Component
{
    public BookForm $form;

    #[On('edit-book')]
    public function editBook($id){
        $book = Book::find($id);
        $this->form->setBook($book);
        Flux::modal('edit-book')->show();
    }

    public function updateBook() {
        $this->form->update();
        Flux::modal('edit-book')->close();
        session()->flash('success', 'Buku berhasil diperbarui');
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
        session()->flash('success', 'Buku berhasil dihapus');
        $this->redirectRoute('book.index', navigate: true);
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }
};
?>

<div>
    <flux:modal name="edit-book" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="updateBook">
            <div class="space-y-2">
                <flux:heading size="lg">Edit Buku</flux:heading>
            </div>

            <div class="space-y-6">
                <flux:input label="Judul" wire:model="form.title" />

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Kategori</label>
                    <select
                        wire:model="form.category_id"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">-- Pilih kategori --</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('form.category_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <flux:input label="ISBN" wire:model="form.isbn" />
                <flux:input type="number" label="Stok" wire:model="form.stock" />
                <flux:textarea label="Deskripsi" wire:model="form.description" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-book" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="deleteBook">
            <div class="space-y-2">
                <flux:heading size="lg">Hapus Buku</flux:heading>
                <flux:text class="text-zinc-500">Tindakan ini tidak bisa dibatalkan</flux:text>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="danger" type="submit">Hapus</flux:button>
            </div>
        </form>
    </flux:modal>
</div>