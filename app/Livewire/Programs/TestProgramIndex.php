<?php

namespace App\Livewire\Programs;

use App\Enums\TestProgramStatus;
use App\Exports\TestProgramsExport;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasTableSorting;
use App\Models\TestProgram;
use App\Services\TestProgramService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TestProgramIndex extends Component
{
    use AuthorizesRequests, HasNotification, HasTableSorting, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    // Delete Modal
    public $showDeleteModal = false;

    public $deletingProgramId;

    public $deletingProgramName;

    public function mount()
    {
        $this->authorize('viewAny', TestProgram::class);
        $this->sortField = 'created_at';
        $this->sortDir = 'desc';
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(TestProgramStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function create()
    {
        $this->authorize('create', TestProgram::class);
        $this->redirect(route('programs.create'), navigate: true);
    }

    public function edit($id)
    {
        $program = TestProgram::findOrFail($id);
        $this->authorize('update', $program);
        $this->redirect(route('programs.edit', $program), navigate: true);
    }

    public function confirmDelete($id)
    {
        $program = TestProgram::findOrFail($id);
        $this->deletingProgramId = $program->id;
        $this->deletingProgramName = $program->name;
        $this->showDeleteModal = true;
    }

    public function delete(TestProgramService $service)
    {
        try {
            $program = TestProgram::findOrFail($this->deletingProgramId);
            $this->authorize('delete', $program);

            if ($program->assignments()->where('status', 'active')->exists()) {
                $this->notifyError('Program tidak dapat dihapus karena masih memiliki penjadwalan aktif.');
                $this->showDeleteModal = false;

                return;
            }

            $service->delete($program);
            $this->notifySuccess('Program test berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus program ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function publish($id, TestProgramService $service)
    {
        try {
            $program = TestProgram::findOrFail($id);
            $this->authorize('publish', $program);

            if ($program->weeks()->count() === 0) {
                $this->notifyError('Program tidak dapat dipublish karena belum memiliki minggu/test.');

                return;
            }

            $service->publish($program);
            $this->notifySuccess('Program test berhasil dipublish!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk publish program.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function archive($id, TestProgramService $service)
    {
        try {
            $program = TestProgram::findOrFail($id);
            $this->authorize('update', $program);

            $service->archive($program);
            $this->notifySuccess('Program test berhasil diarsipkan!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengarsipkan program.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', TestProgram::class);

        return (new TestProgramsExport($this->search, $this->statusFilter))
            ->download('program-test-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf(TestProgramService $service)
    {
        $this->authorize('exportPdf', TestProgram::class);

        $programs = $service->getFiltered(
            $this->search,
            $this->statusFilter,
            perPage: 10000,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        $pdf = Pdf::loadView('exports.test-programs-pdf', ['programs' => $programs]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'program-test-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(TestProgramService $service)
    {
        $programs = $service->getFiltered(
            $this->search,
            $this->statusFilter,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$programs->total()} data program test.");
            $this->filterChanged = false;
        }

        return view('livewire.programs.test-program-index', [
            'programs' => $programs,
        ]);
    }
}
