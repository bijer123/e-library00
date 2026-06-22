<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Models\Loan;
use App\Models\Fine;

new class extends Component
{
    public bool $isStaff = false;

    public function mount()
    {
        $this->isStaff = auth()->user()->isStaff();
    }

    #[Computed]
    public function totalBooks()
    {
        return Book::count();
    }

    #[Computed]
    public function totalCategories()
    {
        return Category::count();
    }

    #[Computed]
    public function totalUsers()
    {
        return User::where('role', 'mahasiswa')->count();
    }

    #[Computed]
    public function activeLoans()
    {
        $query = Loan::where(fn ($q) => $q->where('status', 'borrowed')->orWhere('status', 'partial'));

        if (! $this->isStaff) {
            $query->where('user_id', auth()->id());
        }

        return $query->count();
    }

    #[Computed]
    public function totalFines()
    {
        $query = Fine::where('paid', false);

        if (! $this->isStaff) {
            $query->whereHas('loanDetail.loan', fn ($q) => $q->where('user_id', auth()->id()));
        }

        return $query->sum('amount');
    }

    #[Computed]
    public function recentLoans()
    {
        $query = Loan::with(['user', 'details.book'])->latest();

        if (! $this->isStaff) {
            $query->where('user_id', auth()->id());
        }

        return $query->take(5)->get();
    }
};
?>

<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl">Dashboard</flux:heading>
    <flux:subheading size="lg">
        {{ $isStaff ? 'Statistik perpustakaan digital' : 'Ringkasan peminjaman buku kamu' }}
    </flux:subheading>
    <flux:separator variant="subtle" />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $isStaff ? 5 : 4 }} gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-2">
            <flux:text class="text-zinc-500 text-sm">Total Buku</flux:text>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->totalBooks }}</p>
            <flux:badge color="blue">Koleksi</flux:badge>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-2">
            <flux:text class="text-zinc-500 text-sm">Total Kategori</flux:text>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->totalCategories }}</p>
            <flux:badge color="zinc">Kategori</flux:badge>
        </div>

        @if ($isStaff)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-2">
                <flux:text class="text-zinc-500 text-sm">Total Mahasiswa</flux:text>
                <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->totalUsers }}</p>
                <flux:badge color="blue">Mahasiswa</flux:badge>
            </div>
        @endif

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-2">
            <flux:text class="text-zinc-500 text-sm">{{ $isStaff ? 'Peminjaman Aktif' : 'Peminjaman Aktif Saya' }}</flux:text>
            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $this->activeLoans }}</p>
            <flux:badge color="yellow">Berlangsung</flux:badge>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-2">
            <flux:text class="text-zinc-500 text-sm">{{ $isStaff ? 'Denda Belum Dibayar' : 'Denda Saya' }}</flux:text>
            <p class="text-3xl font-bold text-red-500">Rp {{ number_format($this->totalFines) }}</p>
            <flux:badge color="red">Denda</flux:badge>
        </div>
    </div>

    {{-- Recent Loans --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 space-y-4">
        <flux:heading size="md">{{ $isStaff ? 'Peminjaman Terbaru' : 'Peminjaman Saya' }}</flux:heading>
        <flux:separator variant="subtle" />

        <flux:table>
            <flux:table.columns>
                @if ($isStaff)
                    <flux:table.column>Mahasiswa</flux:table.column>
                @endif
                <flux:table.column>Buku</flux:table.column>
                <flux:table.column>Tanggal Pinjam</flux:table.column>
                <flux:table.column>Jatuh Tempo</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->recentLoans as $loan)
                    <flux:table.row>
                        @if ($isStaff)
                            <flux:table.cell>{{ $loan->user->name }}</flux:table.cell>
                        @endif
                        <flux:table.cell>
                            {{ $loan->details->pluck('book.title')->join(', ') }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $loan->loan_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $loan->due_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$loan->status === 'returned' ? 'green' : ($loan->status === 'partial' ? 'yellow' : 'red')">
                                {{ ucfirst($loan->status) }}
                            </flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ $isStaff ? 5 : 4 }}" class="text-center text-zinc-500">
                            Belum ada peminjaman
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>