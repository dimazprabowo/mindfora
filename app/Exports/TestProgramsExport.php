<?php

namespace App\Exports;

use App\Models\TestProgram;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TestProgramsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?string $search = null,
        private ?string $statusFilter = null
    ) {}

    public function collection()
    {
        $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return TestProgram::with(['creator'])
            ->withCount('weeks')
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('name', $operator, "%{$this->search}%")
                        ->orWhere('description', $operator, "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Deskripsi',
            'Jumlah Minggu',
            'Status',
            'Dibuat Oleh',
            'Dibuat Pada',
        ];
    }

    public function map($program): array
    {
        return [
            $program->name,
            $program->description ?? '',
            $program->weeks_count,
            $program->status->label(),
            $program->creator?->name ?? '-',
            $program->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
