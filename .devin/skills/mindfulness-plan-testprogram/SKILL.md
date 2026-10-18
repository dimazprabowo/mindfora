---
name: mindfulness-plan-testprogram
description: Rencana implementasi fitur inti Mindfulness App — Program Test 4 minggu (unlock rule), Program Assignment ke periode/bulan, Pelatihan, Riwayat, Konsultasi, Landing Page hijau fresh, tema warna, dan multi-bahasa. Gunakan skill ini setiap kali melanjutkan/mengeksekusi task-task tersebut.
triggers:
  - user
  - model
---

# Cara Pakai Skill Ini
Skill ini adalah **rencana kerja (roadmap)**, BUKAN pengganti `mindfulness-app` skill (yang berisi aturan arsitektur/konvensi wajib). Selalu ikuti KEDUA skill ini bersamaan:
- `mindfulness-app` → aturan HOW (pola Livewire, Service, Policy, komponen, dll — wajib dipatuhi).
- `mindfulness-plan-testprogram` (skill ini) → aturan WHAT (fitur apa, urutan apa, keputusan bisnis apa yang sudah disepakati).

Setiap kali diminta lanjutkan development fitur ini, baca skill ini dulu untuk tahu progres & keputusan yang sudah difinalkan, supaya tidak tanya ulang hal yang sudah dijawab user.

---

# Konteks Bisnis (SUDAH DIKONFIRMASI USER — jangan tanya ulang)

## 1. Program Test
- **Program Test** = "paket" template (mis. "Program A", "Program B"), berisi **4 minggu**, tiap minggu = **1 test**, jumlah soal per test **dinamis** (seperti Google Form).
- **Multiple program** bisa dibuat oleh admin. Program = template yang berisi 4 minggu + soal + opsi.

## 2. Program Assignment (Penjadwalan ke bulan/periode)
- Admin **assign program ke periode (bulan)** tertentu di awal, bisa di-update pertengahan.
- Contoh: Januari = Program A, Februari = Program A, Maret = Program B, April = Program C.
- **1 program aktif per user pada satu waktu** — ditentukan oleh assignment yang sedang aktif (periode saat ini).
- Setelah user selesai 4 minggu test dari assignment aktif → **auto-complete** untuk assignment itu. Bulan berikutnya, user masuk ke assignment berikutnya sesuai jadwal.
- **start_date global per assignment** (bukan per user). Semua peserta mulai di tanggal yang sama. User yang enroll terlambat tetap kena jadwal global (minggu yang lewat = overdue).

## 3. Tipe soal yang didukung
- Pilihan ganda (radio, scored), skala Likert (scored), checkbox multi-jawaban (scored), isian teks bebas (tidak scored).

## 4. Unlock rule test mingguan (gabungan sequential + kalender)
- Test minggu-N terbuka jika `hari_ini >= tanggal_mulai_minggu_N` **DAN** (`N == 1` ATAU test minggu (N-1) sudah `submitted`).
- `tanggal_mulai_minggu_N` = `assignment.start_date + (N-1) * 7 hari` (computed, tidak disimpan di DB).
- Jika user belum kerjakan minggu-1 saat kalender sudah masuk minggu-2 → di minggu-2 dia **hanya** bisa mengerjakan test minggu-1 (backlog), minggu-2 masih locked.
- Begitu minggu-1 disubmit (walau telat) dan kalender sudah di minggu-2 → minggu-2 **langsung terbuka** saat itu juga (tanpa nunggu lagi).
- Status per minggu yang ditampilkan ke user: `locked`, `available`, `overdue` (sudah lewat minggu kalendernya tapi belum submit), `completed`.

## 5. Retake & skor official
- **Boleh retake** test yang sudah disubmit. Semua attempt disimpan (tidak overwrite).
- **Skor official** = **skor terbaik (max)** dari semua attempt untuk minggu itu.
- Riwayat menampilkan semua attempt, tapi laporan/ringkasan pakai skor terbaik.

## 6. Simpan parsial & resume
- User **boleh simpan jawaban parsial** (status `in_progress`) dan resume di lain waktu.
- Jawaban auto-save (atau manual save) selama minggu kalender masih berjalan.
- Test baru berubah `submitted` saat user klik tombol Submit.

## 7. Score threshold (kategori stres)
- **Configurable per minggu + default otomatis**.
- Default: 0-33% skor maks = rendah, 34-66% = sedang, 67-100% = tinggi (dihitung otomatis dari total skor semua soal).
- Admin bisa override threshold per minggu di form Program (field `threshold_low_max`, `threshold_medium_max` — nullable; kalau null pakai default persentase).

## 8. Role
- **Super Admin** — full access semua modul.
- **Admin** — read-only semua data lintas company (tidak bisa create/update/delete/publish).
- **User/Peserta** — kerjakan test, lihat pelatihan, riwayat sendiri, mulai konsultasi.

## 9. Konsultasi
- Reuse fitur `Chat`/`ChatMessage`/`ChatParticipant` yang SUDAH ADA di template. Jangan bikin model chat baru. Tambahkan entry point baru khusus user untuk mulai konsultasi ke admin/konselor.

## 10. Multi-bahasa
- Indonesia (default) & English, pakai Laravel lang files (`resources/lang`). Migrasi ke lang file dilakukan **bertahap**: halaman/fitur BARU wajib pakai `__()`, halaman existing template menyusil kalau memang diminta.

## 11. Landing page
- Publik di `/` (saat ini `/` masih redirect langsung ke `/login`, harus diganti jadi halaman landing). Tema warna **hijau fresh**, profesional, ada slot foto/gambar. Kalau AI tidak bisa generate gambar asli, WAJIB sediakan **placeholder foto** yang jelas (border dashed + label "Ganti dengan foto: ...") supaya user tinggal taruh file di folder tertentu.

## 12. Tema warna
- Base color aplikasi diganti dari biru (`blue-*`) → hijau fresh, dengan cara menambah alias warna `primary` di `tailwind.config.js` lalu mengganti pemakaian `blue-*` di komponen reusable secara konsisten (BUKAN acak per file).

---

## Hal yang SUDAH dikonfirmasi user (lanjutan)
- **Usia**: simpan `birth_date` (tanggal lahir). Usia otomatis terhitung via accessor `getAgeAttribute()`.
- **Enrollment**: otomatis semua user dengan role 'user' & `is_active=1` ikut assignment yang sedang aktif. TIDAK perlu tabel `test_program_enrollments`.
- **Departemen**: GLOBAL (sama untuk semua company). Tabel `departments` tanpa `company_id` FK.

---

# Data Model yang Direncanakan

## Edit migration create utama (JANGAN bikin migration `add_*` baru)
- `users`: tambah `gender` (enum L/P), `birth_date` (date, usia via accessor), `education_level` (enum via `App\Enums\EducationLevel`: none/sd/smp/sma/d3/d4/s1/s2/s3), `department_id` (FK ke departments), `work_started_at` (date), `locale` (string, default 'id').

## Tabel baru
- `departments` — id, name, code (unique), is_active. **TANPA company_id** (global untuk semua pabrik).
- `test_programs` — id, name, description, status (draft/active/archived), created_by. **Tidak ada start_date** (tanggal mulai ada di assignment, bukan program).
- `test_program_weeks` — id, test_program_id, week_number (1-4), title, is_active, `threshold_low_max` (int nullable), `threshold_medium_max` (int nullable). **Tidak ada opens_at** (computed dari assignment.start_date).
- `questions` — id, test_program_week_id, type (radio/checkbox/likert/text), label, is_required, order.
- `question_options` — id, question_id, label, score, order.
- `test_program_assignments` — id, test_program_id, label (mis. "Januari 2026"), start_date, end_date, status (scheduled/active/completed/cancelled), created_by. Hanya 1 assignment `active` pada satu waktu.
- **TIDAK ada tabel `test_program_enrollments`** — semua user role 'user' & is_active=1 otomatis ikut assignment aktif.
- `test_attempts` — id, **test_program_assignment_id**, test_program_week_id, user_id, status (in_progress/submitted), score_total, score_category (rendah/sedang/tinggi), is_best (bool, flag skor terbaik), started_at, submitted_at.
- `test_answers` — id, test_attempt_id, question_id, question_option_id nullable (radio/likert), selected_option_ids json nullable (checkbox), answer_text nullable (text).
- `training_materials` — id, title, description, type (audio/video), category, file_status, file_path, file_name, file_size, thumbnail_path nullable, is_published, created_by.

## Enum baru (`App\Enums`)
`EducationLevel`, `TestProgramStatus` (draft/active/archived), `AssignmentStatus` (scheduled/active/completed/cancelled), `QuestionType` (radio/checkbox/likert/text), `StressCategory` (rendah/sedang/tinggi + `badgeClass()`), `TrainingMaterialType` (audio/video), `AttemptStatus` (in_progress/submitted), `WeekUnlockStatus` (locked/available/overdue/completed + `badgeClass()`).

---

# Service & Policy per Entity

| Entity | Service | Policy | Form Strategy |
|---|---|---|---|
| Department | DepartmentService | DepartmentPolicy | Modal (≤3 field) |
| TestProgram (+Week+Question+Option nested) | TestProgramService | TestProgramPolicy | **Full-page** (nested repeater — question builder dinamis) |
| TestProgramAssignment | TestProgramAssignmentService | TestProgramAssignmentPolicy | Full-page (assign program ke periode + enroll users) |
| TestAttempt | TestAttemptService | TestAttemptPolicy | Full-page (pengerjaan soal) — berisi logic unlock rule + scoring + retake |
| TrainingMaterial | TrainingMaterialService | TrainingMaterialPolicy | Full-page (ada upload file async) |
| Riwayat | pakai TestAttemptService (`getHistoryForUser`) | TestAttemptPolicy | Index only |
| Konsultasi | reuse ChatService existing | reuse ChatPolicy | Reuse chat.index + entry point baru |

**Logic unlock (WAJIB di `TestAttemptService`, jangan taruh di Livewire component atau Blade):**
```php
// week terbuka untuk user dalam assignment tertentu jika:
$weekOpensAt = $assignment->start_date->addDays(($week->week_number - 1) * 7);
$previousWeekAttempt = $week->week_number > 1
    ? TestAttempt::where('user_id', $user->id)
        ->where('test_program_assignment_id', $assignment->id)
        ->where('test_program_week_id', $previousWeek->id)
        ->where('status', 'submitted')
        ->latest('submitted_at')
        ->first();

$isUnlocked = now()->gte($weekOpensAt)
    && ($week->week_number === 1 || $previousWeekAttempt !== null);
```

**Logic scoring (WAJIB di `TestAttemptService`):**
```php
// Hitung total skor dari jawaban
// Radio/Likert: ambil score dari question_option_id yang dipilih
// Checkbox: jumlahkan score dari semua selected_option_ids
// Text: tidak scored
// score_total = sum semua skor jawaban
// max_possible = sum max score per question (score tertinggi di options tiap question)
// Tentukan kategori:
//   jika threshold_low_max/medium_max di-set di week → pakai itu
//   else → default: 0-33% max = rendah, 34-66% = sedang, 67-100% = tinggi
// Setelah submit, cek & update is_best flag untuk attempt minggu itu
```

---

# Permission Baru (tambah ke `PermissionSeeder` + grouping di `RolePermissionService`)
```
departments_view/create/update/delete/export_excel/export_pdf
test_programs_view/create/update/delete/export_excel/export_pdf
test_programs_publish
test_assignments_view/create/update/delete
test_attempts_view          (admin/super admin — lihat hasil semua peserta)
test_attempts_view_own      (user — riwayat sendiri)
training_materials_view/create/update/delete
training_materials_view_public
consultation_start
```
Role mapping: Super Admin = semua; Admin = semua `_view`/`export_*` saja; User = `test_attempts_view_own`, `training_materials_view_public`, `consultation_start`.

---

# Routing yang Direncanakan
```
/                                -> landing page publik (ganti dari redirect langsung ke /login)
/programs                       -> index program template (admin/super admin)
/programs/create                -> full-page form (weeks + questions repeater)
/programs/{program}/edit
/assignments                    -> index assignment jadwal (admin/super admin)
/assignments/create             -> full-page form (pilih program + set periode + enroll users)
/assignments/{assignment}/edit
/departments                    -> index + modal form
/training                       -> library materi (semua user login)
/training/manage                -> CRUD admin
/my-test                        -> dashboard status 4 minggu (user, berdasarkan assignment aktif)
/my-test/{week}/take             -> full-page pengerjaan soal
/history                        -> riwayat hasil test user (semua assignment/cycle)
/consultation                   -> reuse chat.index + entry "mulai konsultasi"
```
Semua model yang tampil di URL (`TestProgram`, `TestProgramAssignment`, `TestProgramWeek`, `Department`, `TrainingMaterial`) WAJIB pakai `HasEncryptedRouteKey`.

---

# Landing Page — Struktur Section
1. Navbar (logo, nav link, tombol Login/Daftar, language switcher)
2. Hero + `[SLOT FOTO: hero image]`
3. Tentang Program (teks saja)
4. Cara Kerja — timeline 4 minggu (ikon saja, tanpa foto)
5. Fitur Unggulan — grid card (Pelatihan, Test Mingguan, Konsultasi, Riwayat) tiap card `[SLOT FOTO/ILUSTRASI]`
6. Manfaat/Testimoni `[SLOT FOTO: testimoni]` (opsional)
7. CTA akhir — daftar sekarang
8. Footer

Semua `[SLOT FOTO]` dibuat sebagai placeholder (div border dashed + label jelas file mana yang harus diganti user), taruh di `public/images/landing/...`.

---

# Urutan Eksekusi (checklist — update status di sini kalau sudah selesai)

- [x] 1. Migration + Model + Enum (Department, profil user, TestProgram+Week+Question+Option, Assignment, Attempt+Answer, TrainingMaterial) → `migrate:fresh --seed`
- [x] 2. Permission + Policy + RolePermissionService grouping + role Admin baru
- [x] 3. Tema warna hijau (`tailwind.config.js` alias `primary` + replace `blue-*` → `primary-*` di komponen reusable)
- [x] 4. Landing page (`welcome.blade.php` + route `/`) + setup dasar multi-bahasa (`resources/lang/id`, `resources/lang/en` + language switcher)
- [x] 5. Modul Department (master data — pola referensi)
- [x] 6. Modul Program Test (builder dinamis + full-page form + nested repeater question/option + threshold per minggu)
- [x] 7. Modul Program Assignment (assign program ke periode/bulan + enroll users)
- [x] 8. Modul Pengerjaan Test user (unlock logic, ambil soal, simpan parsial, submit, scoring, retake, is_best) + dashboard status mingguan (`/my-test`)
- [x] 9. Modul Riwayat (`/history` — tampilkan semua attempt, skor official = is_best, tren antar assignment/bulan)
- [x] 10. Modul Pelatihan (upload audio/video async via Job + FileStorageService)
- [x] 11. Modul Konsultasi (reuse chat, entry point baru `/consultation`)
- [x] 12. Update `HasMenuItems` (menu baru per role) + Dashboard (statistik super admin/admin)
- [x] 13. Testing (Pest/PHPUnit dasar) + `./vendor/bin/pint` + verifikasi `migrate:fresh --seed`

**Cara kerja per langkah**: tetap ikuti Protokol Kerja AI di skill `mindfulness-app` (FASE 1 Analisis → FASE 2 Rencana detail file → FASE 3 Eksekusi → FASE 4 Verifikasi + Definition of Done checklist), langkah di atas hanya urutan besar (epic), bukan pengganti protokol per-fitur.
