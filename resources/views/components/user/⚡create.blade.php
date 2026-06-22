<?php

use App\Livewire\Forms\UserForm;
use Livewire\Component;

new class extends Component
{
    public UserForm $form;

    public function save()
    {
        $this->form->store();
        Flux::modal('create-user')->close();
        session()->flash('success', 'User berhasil ditambahkan');
        $this->redirectRoute('user.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }
};
?>

<div>
    <flux:modal name="create-user" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg">Tambah User</flux:heading>
            </div>

            <div class="space-y-6">
                <flux:input label="Nama" placeholder="Masukkan nama" wire:model="form.name" />
                <flux:input label="Email" type="email" placeholder="Masukkan email" wire:model="form.email" />
                <flux:input label="Password" type="password" placeholder="Masukkan password" wire:model="form.password" />

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
                <flux:button variant="primary" type="submit">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>