<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p { text-align: center; color: #666; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #4f46e5; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
    </style>
</head>
<body>
    <h2>Laporan Peminjaman Buku</h2>
    <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama Mahasiswa</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
                @foreach ($loan->details as $detail)
                    <tr>
                        <td>{{ $loan->user->name }}</td>
                        <td>{{ $detail->book->title }}</td>
                        <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                        <td>{{ $loan->due_date->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($detail->status) }}</td>
                        <td>{{ $detail->fine ? 'Rp ' . number_format($detail->fine->amount) : '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>