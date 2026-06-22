<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Loan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoansExport;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component
{
    use WithPagination;

    #[Computed]
    public function loans()
    {
        $query = Loan::query()
            ->with(['user', 'details.book', 'details.fine'])
            ->latest();

        if (! auth()->user()->isStaff()) {
            $query->where('user_id', auth()->id());
        }

        return $query->paginate(5);
    }

    public function exportExcel()
    {
        return Excel::download(new LoansExport, 'laporan-peminjaman.xlsx');
    }

    public function exportPdf()
    {
        $loans = Loan::with(['user', 'details.book', 'details.fine'])->latest()->get();
        $pdf = Pdf::loadView('exports.loans-pdf', compact('loans'));
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-peminjaman.pdf'
        );
    }
};
?>

<div class="max-w-7xl mx-auto space-y-4">
    <flux:heading size="xl">Peminjaman</flux:heading>
    <flux:subheading size="lg">
        {{ auth()->user()->isStaff() ? 'Kelola transaksi peminjaman & pengembalian buku' : 'Riwayat peminjaman buku kamu' }}
    </flux:subheading>
    <flux:separator variant="subtle" />

    <div class="flex items-center gap-3">
        <flux:modal.trigger name="create-loan">
            <flux:button variant="primary" icon="plus">Pinjam Buku</flux:button>
        </flux:modal.trigger>

        @if (auth()->user()->isAdmin())
            <flux:button wire:click="exportExcel" icon="table-cells">Export Excel</flux:button>
            <flux:button wire:click="exportPdf" icon="document-text">Export PDF</flux:button>
        @endif
    </div>

    <livewire:loan.create />
    <livewire:loan.return />

    <x-flash-message />

    <div class="space-y-4">
        @forelse ($this->loans as $loan)
            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="sm">{{ $loan->user->name }}</flux:heading>
                        <flux:text class="text-zinc-500">
                            Pinjam: {{ $loan->loan_date->format('d M Y') }} ·
                            Jatuh tempo: {{ $loan->due_date->format('d M Y') }}
                        </flux:text>
                    </div>
                    <flux:badge :color="$loan->status === 'returned' ? 'green' : ($loan->status === 'partial' ? 'yellow' : 'red')">
                        {{ ucfirst($loan->status) }}
                    </flux:badge>
                </div>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Buku</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Denda</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($loan->details as $detail)
                            <flux:table.row>
                                <flux:table.cell>{{ $detail->book->title }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$detail->status === 'returned' ? 'green' : 'zinc'">
                                        {{ ucfirst($detail->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    {{ $detail->fine ? 'Rp ' . number_format($detail->fine->amount) : '-' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($detail->status === 'borrowed' && auth()->user()->isStaff())
                                        <flux:button size="sm" variant="primary" wire:click="$dispatch('process-return', { id: {{ $detail->id }} })">
                                            Kembalikan
                                        </flux:button>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        @empty
            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg p-8 text-center text-zinc-500">
                Belum ada riwayat peminjaman
            </div>
        @endforelse
    </div>

    {{ $this->loans->links() }}
</div>