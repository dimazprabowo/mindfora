<?php

namespace App\Livewire\MasterData;

use App\Exports\DepartmentsExport;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasTableSorting;
use App\Models\Department;
use App\Services\DepartmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManagement extends Component
{
    use AuthorizesRequests, HasNotification, HasTableSorting, WithPagination;

    public $search = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    // Form fields
    public $departmentId;

    public $code;

    public $name;

    public $is_active = true;

    // Delete Modal
    public $showDeleteModal = false;

    public $deletingDepartmentId;

    public $deletingDepartmentName;

    public function mount()
    {
        $this->authorize('viewAny', Department::class);
        $this->sortField = 'name';
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', $this->editMode ? 'unique:departments,code,'.$this->departmentId : 'unique:departments,code'],
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function validationAttributes()
    {
        return [
            'code' => 'kode departemen',
            'name' => 'nama departemen',
            'is_active' => 'status aktif',
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

    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getStatusOptionsProperty(): array
    {
        return [
            ['value' => '1', 'label' => 'Aktif'],
            ['value' => '0', 'label' => 'Tidak Aktif'],
        ];
    }

    public function create()
    {
        $this->authorize('create', Department::class);

        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function toggleIsActive()
    {
        $this->is_active = ! $this->is_active;
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $this->authorize('update', $department);

        $this->departmentId = $department->id;
        $this->code = $department->code;
        $this->name = $department->name;
        $this->is_active = $department->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(DepartmentService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'code' => $this->code,
                'name' => $this->name,
                'is_active' => $this->is_active,
            ];

            if ($this->editMode) {
                $department = Department::findOrFail($this->departmentId);
                $this->authorize('update', $department);

                $service->update($department, $data);
                $message = 'Departemen berhasil diupdate!';
            } else {
                $this->authorize('create', Department::class);

                $service->create($data);
                $message = 'Departemen berhasil ditambahkan!';
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
        $department = Department::findOrFail($id);
        $this->deletingDepartmentId = $department->id;
        $this->deletingDepartmentName = $department->name;
        $this->showDeleteModal = true;
    }

    public function delete(DepartmentService $service)
    {
        try {
            $department = Department::findOrFail($this->deletingDepartmentId);
            $this->authorize('delete', $department);

            if ($department->users()->exists()) {
                $this->notifyError('Departemen tidak dapat dihapus karena masih memiliki user terkait.');
                $this->showDeleteModal = false;

                return;
            }

            $service->delete($department);
            $this->notifySuccess('Departemen berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus departemen ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function toggleStatus($id, DepartmentService $service)
    {
        try {
            $department = Department::findOrFail($id);
            $this->authorize('toggleStatus', $department);

            $service->toggleStatus($department);

            $status = $department->fresh()->is_active ? 'Aktif' : 'Tidak Aktif';
            $this->notifySuccess("Status departemen berhasil diubah menjadi {$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status departemen.');
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
            'departmentId',
            'code',
            'name',
            'is_active',
        ]);

        $this->is_active = true;
    }

    public function exportExcel()
    {
        $this->authorize('exportExcel', Department::class);

        return (new DepartmentsExport($this->search, $this->statusFilter))
            ->download('departemen-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function exportPdf(DepartmentService $service)
    {
        $this->authorize('exportPdf', Department::class);

        $departments = $service->getFiltered(
            $this->search,
            $this->statusFilter,
            perPage: 10000,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        $pdf = Pdf::loadView('exports.departments-pdf', ['departments' => $departments]);
        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'departemen-'.now()->format('Y-m-d-His').'.pdf'
        );
    }

    public function render(DepartmentService $service)
    {
        $departments = $service->getFiltered(
            $this->search,
            $this->statusFilter,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$departments->total()} data departemen.");
            $this->filterChanged = false;
        }

        return view('livewire.master-data.department-management', [
            'departments' => $departments,
        ]);
    }
}
