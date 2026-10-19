<?php

use App\Livewire\Actions\Logout;
use App\Models\TestProgram;
use App\Models\TestProgramWeek;
use App\Models\TrainingMaterial;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes — the Android app skips the marketing landing page and opens straight into the app.
Route::get('/', fn () => is_native_app()
    ? redirect()->route(auth()->check() ? 'dashboard' : 'login')
    : view('welcome'))->name('welcome');

// Language switching is handled client-side via Alpine + cookie (see
// components/language-switcher.blade.php). SetLocale middleware reads the
// cookie on every request. No server route needed.

// Logout Route (must be authenticated)
Route::post('/logout', function (Request $request, Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware(['auth', 'verified', 'active'])->group(function () {

    // Dashboard
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

    // Profile
    Route::view('profile', 'profile')->name('profile');

    // Master Data Routes
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::view('/companies', 'master-data.companies')->middleware('can:companies_view')->name('companies');
        Route::view('/departments', 'master-data.departments')->middleware('can:departments_view')->name('departments');
    });

    // Program Test Routes (full-page form pattern)
    Route::prefix('programs')->name('programs.')->group(function () {
        Route::view('/', 'programs.index')->middleware('can:test_programs_view')->name('index');
        Route::view('/create', 'programs.create')->middleware('can:test_programs_create')->name('create');
        Route::get('/{program}/edit', function (TestProgram $program) {
            return view('programs.edit', ['program' => $program]);
        })->middleware('can:test_programs_update')->name('edit');
    });

    // Program Assignment Routes
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::view('/', 'assignments.index')->middleware('can:test_assignments_view')->name('index');
    });

    // Test Attempts (admin view all results)
    Route::view('/test-attempts', 'test-attempts.index')->middleware('can:test_attempts_view')->name('test-attempts.index');

    // My Test (user/peserta — take test)
    Route::prefix('my-test')->name('my-test.')->group(function () {
        Route::view('/', 'my-test.index')->middleware('can:test_attempts_view_own')->name('index');
        Route::get('/take/{week}', function (TestProgramWeek $week) {
            return view('my-test.take', ['week' => $week]);
        })->middleware('can:test_attempts_view_own')->name('take');
    });

    // History (user/peserta — view own results + trend)
    Route::view('/history', 'history.index')->middleware('can:test_attempts_view_own')->name('history.index');

    // Training Materials
    Route::prefix('training')->name('training.')->group(function () {
        // Admin manage
        Route::view('/manage', 'training.manage')->middleware('can:training_materials_view')->name('manage');
        // User library (public-facing)
        Route::view('/', 'training.index')->middleware('can:training_materials_view_public')->name('index');

        // Download main file (shared — policy checks visibility)
        Route::get('/download/{material}', function (TrainingMaterial $material, FileStorageService $fileStorage) {
            if (! $material->is_ready || ! $material->file_path) {
                abort(404, 'File tidak tersedia.');
            }
            // Authorization: admin with view perm OR user with view_public + published
            if (auth()->user()->can('training_materials_view') || ($material->is_published && auth()->user()->can('training_materials_view_public'))) {
                if (! $fileStorage->exists($material->file_path)) {
                    abort(404, 'File tidak ditemukan di storage.');
                }

                return $fileStorage->download($material->file_path, $material->file_name ?? $material->title);
            }
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        })->name('download');

        // Stream main file inline (for browser playback via <audio>/<video>)
        Route::get('/stream/{material}', function (TrainingMaterial $material, FileStorageService $fileStorage) {
            if (! $material->is_ready || ! $material->file_path) {
                abort(404, 'File tidak tersedia.');
            }
            if (auth()->user()->can('training_materials_view') || ($material->is_published && auth()->user()->can('training_materials_view_public'))) {
                if (! $fileStorage->exists($material->file_path)) {
                    abort(404, 'File tidak ditemukan di storage.');
                }

                return $fileStorage->stream($material->file_path, $material->file_name ?? $material->title);
            }
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        })->name('stream');

        // Thumbnail image (shared — same visibility as download)
        Route::get('/thumbnail/{material}', function (TrainingMaterial $material, FileStorageService $fileStorage) {
            if (! $material->is_thumbnail_ready || ! $material->thumbnail_path) {
                abort(404, 'Thumbnail tidak tersedia.');
            }
            if (auth()->user()->can('training_materials_view') || ($material->is_published && auth()->user()->can('training_materials_view_public'))) {
                if (! $fileStorage->exists($material->thumbnail_path)) {
                    abort(404, 'Thumbnail tidak ditemukan di storage.');
                }

                return $fileStorage->download($material->thumbnail_path, $material->thumbnail_name);
            }
            abort(403, 'Anda tidak memiliki akses ke thumbnail ini.');
        })->name('thumbnail');
    });

    // Notifications
    Route::view('/notifications', 'notifications.index')->middleware('can:notifications_view')->name('notifications.index');
    Route::view('/notifications/send', 'notifications.send')->middleware('can:notifications_send')->name('notifications.send');

    // Chat
    Route::view('/chat', 'chat.index')->middleware('can:chat_view')->name('chat.index');

    // Settings Routes - each route checks its own permission
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::view('/system', 'settings.system')->middleware('can:configuration_view')->name('system');
        Route::view('/users', 'settings.users')->middleware('can:users_view')->name('users');
        Route::view('/roles', 'settings.roles')->middleware('can:roles_view')->name('roles');
    });

    // Konsultasi (user/peserta — chat with counselor/admin)
    Route::view('/consultation', 'consultation.index')->middleware('can:consultation_start')->name('consultation.index');
});

require __DIR__.'/auth.php';
