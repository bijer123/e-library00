<?php

use App\Livewire\Forms\LoanForm;
use App\Models\Book;
use App\Models\User;
use App\Models\LoanDetail;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public LoanForm $form;

    public function mount()
    {
        if (auth()->user()->isMahasiswa()) {
            $this->form->user_id = auth()->id();
        }
    }

    public function save()
    {
        $this->form->store();
        Flux::modal('create-loan')->close();
        session()->flash('success', 'Peminjaman berhasil dibuat');
        $this->redirectRoute('loan.index', navigate: true);
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->form->reset();
    }

    public function getMahasiswaListProperty()
    {
        return User::where('role', 'mahasiswa')->orderBy('name')->get();
    }

    public function getAvailableBooksProperty()
    {
        return Book::where('stock', '>', 0)->orderBy('title')->get();
    }

    public function getActiveCountProperty()
    {
        if (!$this->form->user_id) return 0;

        return LoanDetail::where('loan_details.status', 'borrowed')
            ->join('loans', 'loans.id', '=', 'loan_details.loan_id')
            ->where('loans.user_id', $this->form->user_id)
            ->count();
    }

    public function getSisaKuotaProperty()
    {
        return max(0, 3 - $this->activeCount);
    }
};
?>

<div>
    <flux:modal name="create-loan" class="md:w-150" x-on:close="$wire.resetForm()">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg">Pinjam Buku</flux:heading>
                <flux:text class="text-zinc-500">Maksimal 3 buku aktif per mahasiswa, masa pinjam 7 hari</flux:text>
            </div>

            <div class="space-y-6">
                @if (auth()->user()->isStaff())
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Mahasiswa</label>
                        <select
                            wire:model.live="form.user_id"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Pilih mahasiswa --</option>
                            @foreach ($this->mahasiswaList as $mhs)
                                <option value="{{ $mhs->id }}">{{ $mhs->name }}</option>
                            @endforeach
                        </select>
                        @error('form.user_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror

                        @if ($this->form->user_id)
                            @if ($this->sisaKuota === 0)
                                <div class="mt-2 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-lg">
                                    🚫 Mahasiswa ini sudah meminjam 3 buku aktif. Tidak bisa meminjam lagi.
                                </div>
                            @else
                                <div class="mt-2 text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-lg">
                                    ℹ️ Pinjaman aktif: {{ $this->activeCount }} buku. Sisa kuota: <strong>{{ $this->sisaKuota }} buku</strong>.
                                </div>
                            @endif
                        @endif
                    </div>
                @endif

                @if (!$this->form->user_id || $this->sisaKuota > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Pilih Buku (maks {{ $this->form->user_id ? $this->sisaKuota : 3 }})
                            </label>
                            <span class="text-xs text-zinc-400">Dipilih: <span x-data x-text="$wire.form.book_ids.length"></span>/{{ $this->form->user_id ? $this->sisaKuota : 3 }}</span>
                        </div>
                        <div class="border border-zinc-300 dark:border-zinc-600 rounded-lg divide-y divide-zinc-100 dark:divide-zinc-700 max-h-60 overflow-y-auto">
                            @foreach ($this->availableBooks as $book)
                                <label class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model="form.book_ids"
                                        value="{{ $book->id }}"
                                        class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                                        x-bind:disabled="!$el.checked && $wire.form.book_ids.length >= {{ $this->form->user_id ? $this->sisaKuota : 3 }}"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $book->title }}</p>
                                        <p class="text-xs text-zinc-500">Stok: {{ $book->stock }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div
                            x-data
                            x-show="$wire.form.book_ids.length >= {{ $this->form->user_id ? $this->sisaKuota : 3 }}"
                            class="mt-2 text-xs text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 px-3 py-2 rounded-lg"
                        >
                            ⚠️ Batas maksimal tercapai. Buku lain tidak bisa dipilih.
                        </div>
                        @error('form.book_ids') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="outline" color="neutral">Batal</flux:button>
                </flux:modal.close>
                @if (!$this->form->user_id || $this->sisaKuota > 0)
                    <flux:button variant="primary" type="submit">Pinjam</flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
</div>