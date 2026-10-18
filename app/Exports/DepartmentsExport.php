<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?string $search = null,
        private ?string $statusFilter = null
    ) {}

    public function collection()
    {
        $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Department::withCount('users')
            ->when($this->search, function ($q) use ($operator) {
                $q->where(function ($q) use ($operator) {
                    $q->where('code', $operator, "%{$this->search}%")
                        ->orWhere('name', $operator, "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($q) {
                $q->where('is_active', (bool) $this->statusFilter);
            })
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama',
            'Jumlah User',
            'Status',
            'Dibuat Pada',
        ];
    }

    public function map($department): array
    {
        return [
            $department->code,
            $department->name,
            $department->users_count,
            $department->is_active ? 'Aktif' : 'Tidak Aktif',
            $department->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
