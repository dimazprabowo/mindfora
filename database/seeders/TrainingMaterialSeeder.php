<?php

namespace Database\Seeders;

use App\Enums\FileStatus;
use App\Enums\TrainingMaterialType;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrainingMaterialSeeder extends Seeder
{
    /**
     * Seed training materials with real downloadable files from free sources.
     *
     * Sources (all free, no attribution required):
     *  - SoundHelix (free MP3 samples for testing, ~9-10MB each)
     *  - SampleLib (free MP3/MP4 samples, various sizes)
     *  - Kozco (test MP3 files)
     *
     * Hybrid best-effort: if download fails, the record is still created
     * with file_path = null (playback/download buttons won't appear).
     * Admin can re-upload real mindfulness audio/video via the manage UI.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@app.com')->first();

        // Each material has multiple fallback URLs for resilience.
        // URLs verified working (HTTP 200) as of seeder creation.
        $materials = [
            [
                'title' => 'Relaksasi Napas Dalam (4-7-8)',
                'description' => 'Panduan audio teknik pernapasan 4-7-8 untuk menenangkan sistem saraf dan mengurangi kecemasan. Cocok dipakai sebelum tidur atau saat merasa panik.',
                'type' => TrainingMaterialType::Audio,
                'category' => 'Relaksasi',
                'urls' => [
                    ['https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 'mp3'],
                    ['https://samplelib.com/lib/preview/mp3/sample-9s.mp3', 'mp3'],
                    ['https://www.kozco.com/tech/LRMonoPhase4.mp3', 'mp3'],
                ],
            ],
            [
                'title' => 'Meditasi Mindfulness 10 Menit',
                'description' => 'Sesi meditasi mindfulness singkat untuk membantu fokus pada momen saat ini dan mengurangi rumination. Panduan suara dengan musik latar.',
                'type' => TrainingMaterialType::Audio,
                'category' => 'Meditasi',
                'urls' => [
                    ['https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3', 'mp3'],
                    ['https://samplelib.com/lib/preview/mp3/sample-3s.mp3', 'mp3'],
                ],
            ],
            [
                'title' => 'Stretching & Gerakan Rileks di Meja Kerja',
                'description' => 'Video panduan gerakan stretching ringan yang bisa dilakukan di meja kerja untuk mengurangi ketegangan otot akibat duduk terlalu lama.',
                'type' => TrainingMaterialType::Video,
                'category' => 'Fisik',
                'urls' => [
                    ['https://samplelib.com/lib/preview/mp4/sample-10s.mp4', 'mp4'],
                    ['https://samplelib.com/lib/preview/mp4/sample-5s.mp4', 'mp4'],
                ],
            ],
            [
                'title' => 'Progressive Muscle Relaxation (PMR)',
                'description' => 'Video panduan teknik relaksasi otot progresif: tegangkan lalu rileksaskan setiap kelompok otot dari ujung kaki hingga kepala. Efektif untuk insomnia.',
                'type' => TrainingMaterialType::Video,
                'category' => 'Relaksasi',
                'urls' => [
                    ['https://samplelib.com/lib/preview/mp4/sample-5s.mp4', 'mp4'],
                    ['https://samplelib.com/lib/preview/mp4/sample-10s.mp4', 'mp4'],
                ],
            ],
            [
                'title' => 'Meditasi Cinta Kasih (Loving-Kindness)',
                'description' => 'Audio meditasi loving-kindness untuk menumbuhkan perasaan positif terhadap diri sendiri dan orang lain. Membantu mengurangi depresi dan meningkatkan empati.',
                'type' => TrainingMaterialType::Audio,
                'category' => 'Meditasi',
                'urls' => [
                    ['https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3', 'mp3'],
                    ['https://samplelib.com/lib/preview/mp3/sample-9s.mp3', 'mp3'],
                ],
            ],
            [
                'title' => 'Panduan Jurnal Refleksi Stres',
                'description' => 'Video tutorial cara menulis jurnal refleksi untuk mengidentifikasi pemicu stres dan pola pikir negatif. Termasuk template prompt jurnal.',
                'type' => TrainingMaterialType::Video,
                'category' => 'Refleksi',
                'urls' => [
                    ['https://samplelib.com/lib/preview/mp4/sample-10s.mp4', 'mp4'],
                    ['https://samplelib.com/lib/preview/mp4/sample-5s.mp4', 'mp4'],
                ],
            ],
        ];

        foreach ($materials as $data) {
            $this->seedMaterial($data, $admin, published: true);
        }

        // 1 materi draft (belum publish) untuk demo UI
        $this->seedMaterial([
            'title' => 'Audio Binaural Beats untuk Fokus',
            'description' => 'Eksperimen: gelombang beta untuk meningkatkan konsentrasi. Belum final, masih dalam review.',
            'type' => TrainingMaterialType::Audio,
            'category' => 'Eksperimen',
            'urls' => [
                ['https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3', 'mp3'],
                ['https://samplelib.com/lib/preview/mp3/sample-3s.mp3', 'mp3'],
            ],
        ], $admin, published: false);
    }

    /**
     * Seed a single material: download file from internet (best-effort),
     * save to storage, and create the DB record.
     */
    private function seedMaterial(array $data, ?User $admin, bool $published): void
    {
        $downloadResult = $this->downloadFromUrls($data['urls']);

        $record = TrainingMaterial::firstOrCreate(
            ['title' => $data['title']],
            [
                'description' => $data['description'],
                'type' => $data['type'],
                'category' => $data['category'],
                'file_status' => $downloadResult ? FileStatus::Completed : FileStatus::Failed,
                'file_path' => $downloadResult['path'] ?? null,
                'file_name' => $downloadResult['name'] ?? null,
                'file_size' => $downloadResult['size'] ?? null,
                'file_processed_at' => $downloadResult ? now() : null,
                'file_error' => $downloadResult ? null : 'Gagal download dari semua sumber URL (seeder best-effort).',
                'is_published' => $published,
                'created_by' => $admin?->id ?? 1,
            ]
        );

        if (! $downloadResult) {
            $this->command->warn("  [!] Material '{$data['title']}' — file tidak terdownload (file_path=null). Playback/download tidak akan muncul.");
        } else {
            $this->command->info("  [OK] Material '{$data['title']}' — file terdownload ({$downloadResult['size_formatted']}).");
        }
    }

    /**
     * Try downloading from a list of fallback URLs.
     * Returns ['path', 'name', 'size', 'size_formatted'] on success, null on failure.
     */
    private function downloadFromUrls(array $urls): ?array
    {
        foreach ($urls as [$url, $ext]) {
            try {
                $response = Http::timeout(60)->connectTimeout(15)->get($url);

                if (! $response->successful() || $response->body() === '') {
                    continue;
                }

                $content = $response->body();
                $size = strlen($content);

                // Skip empty or too-small files (< 10KB — likely error page)
                if ($size < 10240) {
                    continue;
                }

                // Build path: {app_env}/training-materials/{slug}/{slug}_{YmdHis}.{ext}
                $slug = Str::slug(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
                $fileName = $slug.'_'.now()->format('YmdHis').'.'.$ext;
                $path = config('app.env').'/training-materials/'.$slug.'/'.$fileName;

                Storage::disk(file_disk())->put($path, $content);

                // Verify file was written
                if (! Storage::disk(file_disk())->exists($path)) {
                    continue;
                }

                return [
                    'path' => $path,
                    'name' => $fileName,
                    'size' => $size,
                    'size_formatted' => $this->formatBytes($size),
                ];
            } catch (\Throwable $e) {
                // Try next URL
                continue;
            }
        }

        return null;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
