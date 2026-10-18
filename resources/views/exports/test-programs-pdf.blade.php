<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Program Test</title>
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
    <h1>Daftar Program Test</h1>
    <p class="meta">Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Minggu</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programs as $program)
                <tr>
                    <td>{{ $program->name }}</td>
                    <td>{{ $program->weeks_count }}</td>
                    <td>{{ $program->status->label() }}</td>
                    <td>{{ $program->creator?->name ?? '-' }}</td>
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
