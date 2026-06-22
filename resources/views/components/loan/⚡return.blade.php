<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\LoanDetail;
use App\Models\Fine;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public ?LoanDetail $detail = null;
    public int $previewFine = 0;

    #[On('process-return')]
    public function openReturn($id)
    {
        $this->detail = LoanDetail::with(['loan', 'book'])->find($id);

        $dueDate = $this->detail->loan->due_date;
        $lateDays = now()->gt($dueDate) ? now()->diffInDays($dueDate) : 0;
        $this->previewFine = $lateDays * 1000; // Rule: denda Rp1.000/hari

        Flux::modal('return-loan')->show();
    }

    public function confirmReturn()
    {
        DB::transaction(function () {
            $returnedAt = now();
            $dueDate = $this->detail->loan->due_date;
            $lateDays = $returnedAt->gt($dueDate) ? $returnedAt->diffInDays($dueDate) : 0;
            $fineAmount = $lateDays * 1000;

            $this->detail->update([
                'returned_at' => $returnedAt,
                'status' => 'returned',
            ]);

            if ($fineAmount > 0) {
                Fine::create([
                    'loan_detail_id' => $this->detail->id,
                    'amount' => $fineAmount,
                    'paid' => false,
                ]);
            }

            $this->detail->book->increment('stock');

            $loan = $this->detail->loan;
            $allReturned = $loan->details()->where('status', 'borrowed')->doesntExist();
            $loan->update(['status' => $allReturned ? 'returned' : 'partial']);
        });

        Flux::modal('return-loan')->close();
        session()->flash('success', 'Buku berhasil dikembalikan');
        $this->redirectRoute('loan.index', navigate: true);
    }
};
?>

<div>
    <flux:modal name="return-loan" class="md:w-150">
        @if ($detail)
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">Konfirmasi Pengembalian</flux:heading>
                    <flux:text class="text-zinc-500">{{ $detail->book->title }} — {{ $detail->loan->user->name }}</flux:text>
                </div>

                <div class="space-y-1 text-sm">
                    <p>Jatuh tempo: {{ $detail->loan->due_date->format('d M Y') }}</p>
                    <p>Tanggal kembali: {{ now()->format('d M Y') }}</p>
                </div>

                @if ($previewFine > 0)
                    <div class="bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 p-3 rounded-lg">
                        ⚠️ Terlambat! Denda: <strong>Rp {{ number_format($previewFine) }}</strong>
                    </div>
                @else
                    <div class="bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400 p-3 rounded-lg">
                        ✅ Tepat waktu, tidak ada denda
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <flux:modal.close>
                        <flux:button variant="outline" color="neutral">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="confirmReturn">Konfirmasi Kembali</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>