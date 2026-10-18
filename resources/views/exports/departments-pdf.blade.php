<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Departemen</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 16px; color: #047857; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #ecfdf5; font-weight: bold; color: #065f46; }
        tr:nth-child(even) { background: #f9fafb; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>Daftar Departemen</h1>
    <p class="meta">Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jumlah User</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $department)
                <tr>
                    <td>{{ $department->code }}</td>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->users_count }}</td>
                    <td>{{ $department->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 24px;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
