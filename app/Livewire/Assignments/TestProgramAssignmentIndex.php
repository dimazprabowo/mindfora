<?php

namespace App\Livewire\Assignments;

use App\Enums\AssignmentStatus;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasTableSorting;
use App\Models\TestProgramAssignment;
use App\Services\TestProgramAssignmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TestProgramAssignmentIndex extends Component
{
    use AuthorizesRequests, HasNotification, HasTableSorting, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $programFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    // Form fields
    public $assignmentId;

    public $test_program_id;

    public $label;

    public $start_date;

    public $end_date;

    public $status = 'scheduled';

    // Delete Modal
    public $showDeleteModal = false;

    public $deletingAssignmentId;

    public $deletingAssignmentLabel;

    public function mount()
    {
        $this->authorize('viewAny', TestProgramAssignment::class);
        $this->sortField = 'start_date';
        $this->sortDir = 'desc';
    }

    public function rules()
    {
        return [
            'test_program_id' => 'required|exists:test_programs,id',
            'label' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['required', 'string', 'in:'.implode(',', AssignmentStatus::values())],
        ];
    }

    public function validationAttributes()
    {
        return [
            'test_program_id' => 'program test',
            'label' => 'label penjadwalan',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
            'status' => 'status',
        ];
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

    public function updatingProgramFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->programFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(AssignmentStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function getProgramOptionsProperty(): array
    {
        return app(TestProgramAssignmentService::class)->getProgramOptions();
    }

    public function create()
    {
        $this->authorize('create', TestProgramAssignment::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $assignment = TestProgramAssignment::findOrFail($id);
        $this->authorize('update', $assignment);

        $this->assignmentId = $assignment->id;
        $this->test_program_id = $assignment->test_program_id;
        $this->label = $assignment->label;
        $this->start_date = $assignment->start_date?->format('Y-m-d');
        $this->end_date = $assignment->end_date?->format('Y-m-d');
        $this->status = $assignment->status->value;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(TestProgramAssignmentService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'test_program_id' => $this->test_program_id,
                'label' => $this->label,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ];

            if ($this->editMode) {
                $assignment = TestProgramAssignment::findOrFail($this->assignmentId);
                $this->authorize('update', $assignment);
                $service->update($assignment, $data);
                $message = 'Penjadwalan berhasil diupdate!';
            } else {
                $this->authorize('create', TestProgramAssignment::class);
                $service->create($data);
                $message = 'Penjadwalan berhasil ditambahkan!';
            }

            $this->notifySuccess($message);
            $this->closeModal();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function confirmDelete($id)
    {
        $assignment = TestProgramAssignment::findOrFail($id);
        $this->deletingAssignmentId = $assignment->id;
        $this->deletingAssignmentLabel = $assignment->label ?? ('Program: '.$assignment->program?->name);
        $this->showDeleteModal = true;
    }

    public function delete(TestProgramAssignmentService $service)
    {
        try {
            $assignment = TestProgramAssignment::findOrFail($this->deletingAssignmentId);
            $this->authorize('delete', $assignment);

            $service->delete($assignment);
            $this->notifySuccess('Penjadwalan berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus penjadwalan ini (mungkin sudah ada yang submit).');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function activate($id, TestProgramAssignmentService $service)
    {
        try {
            $assignment = TestProgramAssignment::findOrFail($id);
            $this->authorize('update', $assignment);

            $service->activate($assignment);
            $this->notifySuccess('Penjadwalan berhasil diaktifkan! Penjadwalan aktif lainnya telah diselesaikan.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengaktifkan penjadwalan.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function cancel($id, TestProgramAssignmentService $service)
    {
        try {
            $assignment = TestProgramAssignment::findOrFail($id);
            $this->authorize('update', $assignment);

            $service->cancel($assignment);
            $this->notifySuccess('Penjadwalan berhasil dibatalkan!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk membatalkan penjadwalan.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function complete($id, TestProgramAssignmentService $service)
    {
        try {
            $assignment = TestProgramAssignment::findOrFail($id);
            $this->authorize('update', $assignment);

            $service->complete($assignment);
            $this->notifySuccess('Penjadwalan berhasil diselesaikan!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk menyelesaikan penjadwalan.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset([
            'assignmentId',
            'test_program_id',
            'label',
            'start_date',
            'end_date',
            'status',
        ]);
        $this->status = AssignmentStatus::Scheduled->value;
    }

    public function render(TestProgramAssignmentService $service)
    {
        $assignments = $service->getFiltered(
            $this->search,
            $this->statusFilter,
            $this->programFilter ? (int) $this->programFilter : null,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$assignments->total()} data penjadwalan.");
            $this->filterChanged = false;
        }

        return view('livewire.assignments.test-program-assignment-index', [
            'assignments' => $assignments,
        ]);
    }
}
