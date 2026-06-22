<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LoansExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Loan::with(['user', 'details.book', 'details.fine'])->latest()->get();
    }

    public function headings(): array
    {
        return ['Nama Mahasiswa', 'Judul Buku', 'Tanggal Pinjam', 'Jatuh Tempo', 'Status', 'Denda'];
    }

    public function map($loan): array
    {
        $rows = [];
        foreach ($loan->details as $detail) {
            $rows[] = [
                $loan->user->name,
                $detail->book->title,
                $loan->loan_date->format('d/m/Y'),
                $loan->due_date->format('d/m/Y'),
                ucfirst($detail->status),
                $detail->fine ? 'Rp ' . number_format($detail->fine->amount) : '-',
            ];
        }
        return $rows;
    }
}