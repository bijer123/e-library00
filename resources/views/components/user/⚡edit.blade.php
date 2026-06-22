<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Livewire\Forms\UserForm;
use App\Models\User;

new class extends Component
{
    public UserForm $form;

    #[On('edit-user')]
    public function editUser($id)
    {
        $this->form->setUser(User::find($id));
        Flux::modal('edit-user')->show();
    }

    public function updateUser()
    {
        $this->form->update();
        Flux::modal('edit-user')->close();
        session()->flash('success', 'User berhasil diperbarui');
        $this->redirectRoute('user.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    #[On('confirm-delete-user')]
    public function confirmDelete($id)
    {
        $this->form->setUser(User::find($id));
        Flux::modal('delete-user')->show();
    }

    public function deleteUser()
    {
        $this->form->user->delete();
        Flux::modal('delete-user')->close();
        session()->flash('success', 'User berhasil dihapus');
        $this->redirectRoute('user.index', navigate: true);
    }
};
?>

<div>
    <flux:modal name="edit-user" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="updateUser">
            <div class="space-y-2">
                <flux:heading size="lg">Edit User</flux:heading>
            </div>

            <div class="space-y-6">
                <flux:input label="Nama" wire:model="form.name" />
                <flux:input label="Email" type="email" wire:model="form.email" />
                <flux:input label="Password Baru" type="password" placeholder="Kosongkan jika tidak diubah" wire:model="form.password" />

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Role</label>
                    <select wire:model="form.role" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                    </select>
                    @error('form.role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Update</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-user" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="deleteUser">
            <div class="space-y-2">
                <flux:heading size="lg">Hapus User</flux:heading>
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