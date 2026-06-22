<?php

namespace App\Livewire\Forms;

use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoanForm extends Form
{
    public ?int $user_id = null;
    public array $book_ids = [];

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'book_ids' => ['required', 'array', 'min:1', 'max:3'],
            'book_ids.*' => ['exists:books,id'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        // Rule: maksimal total 3 buku aktif per mahasiswa
          $activeCount = \DB::table('loan_details')
    ->where('loan_details.status', 'borrowed')
    ->join('loans', 'loans.id', '=', 'loan_details.loan_id')
    ->where('loans.user_id', $this->user_id)
    ->count();

        if ($activeCount + count($this->book_ids) > 3) {
            throw ValidationException::withMessages([
                'book_ids' => "Mahasiswa ini sudah meminjam {$activeCount} buku. Maksimal total 3 buku aktif.",
            ]);
        }

        // Rule: cek stok semua buku yang dipilih
        $books = Book::whereIn('id', $this->book_ids)->get();
        foreach ($books as $book) {
            if ($book->stock < 1) {
                throw ValidationException::withMessages([
                    'book_ids' => "Stok buku \"{$book->title}\" habis.",
                ]);
            }
        }

        DB::transaction(function () use ($books) {
            $loan = Loan::create([
                'user_id' => $this->user_id,
                'processed_by' => auth()->id(),
                'loan_date' => now(),
                'due_date' => now()->addDays(7), // Rule: maks 7 hari
                'status' => 'borrowed',
            ]);

            foreach ($books as $book) {
                $loan->details()->create([
                    'book_id' => $book->id,
                    'status' => 'borrowed',
                ]);
                $book->decrement('stock');
            }
        });

        $this->reset();
    }
}
