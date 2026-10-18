<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Models\TestProgram;
use App\Models\TestProgramAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestProgramAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@app.com')->first();

        // ============================================================
        // Assignment 1: DASS-42 — Active (mulai 7 hari lalu)
        // Supaya minggu 1 sudah selesai (unlock) & minggu 2 sedang aktif.
        // ============================================================
        $dassProgram = TestProgram::where('name', 'DASS-42 Stress Assessment')->first();

        if (! $dassProgram) {
            return;
        }

        TestProgramAssignment::firstOrCreate(
            [
                'test_program_id' => $dassProgram->id,
                'start_date' => Carbon::today()->subDays(7),
            ],
            [
                'label' => 'Periode Batch 1 — '.now()->format('F Y'),
                'end_date' => Carbon::today()->subDays(7)->addWeeks(4),
                'status' => AssignmentStatus::Active,
                'created_by' => $admin?->id ?? 1,
            ]
        );

        // ============================================================
        // Assignment 2: DASS-42 — Scheduled (mulai bulan depan)
        // ============================================================
        TestProgramAssignment::firstOrCreate(
            [
                'test_program_id' => $dassProgram->id,
                'start_date' => Carbon::today()->addMonth()->startOfMonth(),
            ],
            [
                'label' => 'Periode Batch 2 — '.Carbon::today()->addMonth()->startOfMonth()->format('F Y'),
                'end_date' => Carbon::today()->addMonth()->startOfMonth()->addWeeks(4),
                'status' => AssignmentStatus::Scheduled,
                'created_by' => $admin?->id ?? 1,
            ]
        );

        // ============================================================
        // Assignment 3: Burnout Assessment — Scheduled (draft program)
        // ============================================================
        $burnoutProgram = TestProgram::where('name', 'Burnout Assessment')->first();

        if ($burnoutProgram) {
            TestProgramAssignment::firstOrCreate(
                [
                    'test_program_id' => $burnoutProgram->id,
                    'start_date' => Carbon::today()->addMonths(2)->startOfMonth(),
                ],
                [
                    'label' => 'Pilot Run — '.Carbon::today()->addMonths(2)->startOfMonth()->format('F Y'),
                    'end_date' => Carbon::today()->addMonths(2)->startOfMonth()->addWeeks(4),
                    'status' => AssignmentStatus::Scheduled,
                    'created_by' => $admin?->id ?? 1,
                ]
            );
        }
    }
}
