<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public function sort($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->tap(fn ($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q)
            ->paginate(10);
    }

    public function edit($id)
    {
        $this->dispatch('edit-user', id: $id);
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl">Manajemen User</flux:heading>
    <flux:subheading size="lg">Kelola akun admin, petugas, dan mahasiswa</flux:subheading>
    <flux:separator variant="subtle" />

    <flux:modal.trigger name="create-user">
        <flux:button variant="primary" icon="plus">Tambah User</flux:button>
    </flux:modal.trigger>

    <livewire:user.create />
    <livewire:user.edit />

    <x-flash-message />

    <div class="overflow-x-auto">
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Nama</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection" wire:click="sort('email')">Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>{{ $user->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="match($user->role) {
                                'admin' => 'red',
                                'petugas' => 'yellow',
                                'mahasiswa' => 'blue',
                            }">
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"></flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="edit({{ $user->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete-user', {id: {{ $user->id }}})">Hapus</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>