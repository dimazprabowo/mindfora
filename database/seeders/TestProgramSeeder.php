<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Enums\TestProgramStatus;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\TestProgram;
use App\Models\TestProgramWeek;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestProgramSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@app.com')->first();

        // ============================================================
        // Program 1: DASS-42 (Active) — 4 minggu, skala Likert 0-3
        // ============================================================
        $dassProgram = TestProgram::firstOrCreate(
            ['name' => 'DASS-42 Stress Assessment'],
            [
                'description' => 'Skala Depresi, Kecemasan, dan Stres (DASS-42) — 42 pertanyaan dibagi 4 minggu untuk mengukur tingkat stres psikologis karyawan.',
                'status' => TestProgramStatus::Active,
                'created_by' => $admin?->id ?? 1,
            ]
        );

        // Hanya seed weeks jika program belum punya weeks (idempotent)
        if ($dassProgram->weeks()->count() > 0) {
            return;
        }

        $this->seedDassWeeks($dassProgram);

        // ============================================================
        // Program 2: Burnout Assessment (Draft) — template, belum aktif
        // ============================================================
        TestProgram::firstOrCreate(
            ['name' => 'Burnout Assessment'],
            [
                'description' => 'Mengukur tingkat burnout karyawan berdasarkan indikator kelelahan emosional, depersonalisasi, dan pencapaian personal.',
                'status' => TestProgramStatus::Draft,
                'created_by' => $admin?->id ?? 1,
            ]
        );
    }

    /**
     * Seed 4 minggu untuk program DASS-42.
     * Setiap minggu punya 10-11 soal Likert (skala 0-3: Tidak Pernah, Kadang, Sering, Selalu).
     * Threshold: rendah <= 10, sedang <= 20, tinggi > 20 (per minggu, max skor = 30-33).
     */
    private function seedDassWeeks(TestProgram $program): void
    {
        $weeksData = [
            [
                'week_number' => 1,
                'title' => 'Minggu 1 — Indikator Depresi',
                'threshold_low_max' => 10,
                'threshold_medium_max' => 20,
                'questions' => $this->getLikertQuestions(1, [
                    'Saya merasa sulit untuk semangat melakukan sesuatu',
                    'Saya merasa kehilangan minat pada hampir segala hal',
                    'Saya merasa tidak ada harapan untuk masa depan',
                    'Saya merasa sedih dan murung',
                    'Saya merasa tidak berharga sebagai manusia',
                    'Saya merasa hidup tidak bermakna',
                    'Saya merasa sulit untuk berkonsentrasi',
                    'Saya merasa kehilangan energi dan kelelahan',
                    'Saya merasa sulit untuk membuat keputusan',
                    'Saya merasa tidak peduli dengan apa pun',
                ]),
            ],
            [
                'week_number' => 2,
                'title' => 'Minggu 2 — Indikator Kecemasan',
                'threshold_low_max' => 10,
                'threshold_medium_max' => 20,
                'questions' => $this->getLikertQuestions(2, [
                    'Saya merasa mulut kering',
                    'Saya merasa sulit bernapas',
                    'Saya merasa jantung berdebar kencang',
                    'Saya merasa gemetar tanpa sebab yang jelas',
                    'Saya merasa cemas tanpa alasan yang jelas',
                    'Saya merasa gugup dan tidak tenang',
                    'Saya merasa khawatir berlebihan',
                    'Saya merasa panik tanpa sebab',
                    'Saya merasa sesak di dada',
                    'Saya merasa takut sesuatu yang buruk akan terjadi',
                    'Saya merasa sulit untuk rileks',
                ]),
            ],
            [
                'week_number' => 3,
                'title' => 'Minggu 3 — Indikator Stres',
                'threshold_low_max' => 10,
                'threshold_medium_max' => 20,
                'questions' => $this->getLikertQuestions(3, [
                    'Saya merasa sulit untuk tenang',
                    'Saya merasa mudah marah karena hal-hal kecil',
                    'Saya merasa tegang dan tidak bisa bersantai',
                    'Saya merasa tertekan oleh tanggung jawab',
                    'Saya merasa sulit untuk bersantai setelah bekerja',
                    'Saya merasa kewalahan dengan banyaknya tugas',
                    'Saya merasa tidak sabar dengan orang lain',
                    'Saya merasa frustrasi dengan situasi pekerjaan',
                    'Saya merasa sulit untuk mengontrol emosi',
                    'Saya merasa kelelahan mental setelah bekerja',
                ]),
            ],
            [
                'week_number' => 4,
                'title' => 'Minggu 4 — Evaluasi & Refleksi',
                'threshold_low_max' => 10,
                'threshold_medium_max' => 20,
                'questions' => array_merge(
                    $this->getLikertQuestions(4, [
                        'Saya merasa lebih mampu mengelola stres dibanding minggu lalu',
                        'Saya merasa lebih tenang menghadapi tekanan pekerjaan',
                        'Saya merasa lebih mampu mengontrol emosi',
                        'Saya merasa lebih positif tentang masa depan',
                        'Saya merasa lebih bersemangat dalam bekerja',
                        'Saya merasa lebih mampu bersantai setelah bekerja',
                        'Saya merasa lebih sehat secara mental',
                        'Saya merasa hubungan dengan rekan kerja membaik',
                    ]),
                    [
                        [
                            'type' => QuestionType::Text,
                            'label' => 'Apa strategi coping stres yang paling efektif untuk Anda selama 4 minggu ini?',
                            'is_required' => false,
                        ],
                        [
                            'type' => QuestionType::Text,
                            'label' => 'Apa saran perbaikan untuk program mindfulness ini?',
                            'is_required' => false,
                        ],
                    ]
                ),
            ],
        ];

        foreach ($weeksData as $weekData) {
            $week = TestProgramWeek::create([
                'test_program_id' => $program->id,
                'week_number' => $weekData['week_number'],
                'title' => $weekData['title'],
                'is_active' => true,
                'threshold_low_max' => $weekData['threshold_low_max'],
                'threshold_medium_max' => $weekData['threshold_medium_max'],
            ]);

            $order = 1;
            foreach ($weekData['questions'] as $qData) {
                $question = Question::create([
                    'test_program_week_id' => $week->id,
                    'type' => $qData['type'],
                    'label' => $qData['label'],
                    'is_required' => $qData['is_required'] ?? true,
                    'order' => $order++,
                ]);

                // Seed options untuk soal Likert (skala 0-3)
                if ($qData['type'] === QuestionType::Likert) {
                    $this->seedLikertOptions($question);
                }
            }
        }
    }

    /**
     * Generate array pertanyaan Likert dari list label.
     */
    private function getLikertQuestions(int $weekNum, array $labels): array
    {
        return array_map(fn ($label) => [
            'type' => QuestionType::Likert,
            'label' => $label,
            'is_required' => true,
        ], $labels);
    }

    /**
     * Seed 4 opsi Likert standar (skor 0-3).
     */
    private function seedLikertOptions(Question $question): void
    {
        $options = [
            ['label' => 'Tidak Pernah', 'score' => 0],
            ['label' => 'Kadang-Kadang', 'score' => 1],
            ['label' => 'Sering', 'score' => 2],
            ['label' => 'Selalu', 'score' => 3],
        ];

        $order = 1;
        foreach ($options as $opt) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $opt['label'],
                'score' => $opt['score'],
                'order' => $order++,
            ]);
        }
    }
}
