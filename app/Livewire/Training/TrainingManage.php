<?php

namespace App\Livewire\Training;

use App\Enums\TrainingMaterialType;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasTableSorting;
use App\Models\TrainingMaterial;
use App\Services\FileStorageService;
use App\Services\TrainingMaterialService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class TrainingManage extends Component
{
    use AuthorizesRequests, HasNotification, HasTableSorting, WithFileUploads, WithPagination;

    public $search = '';

    public $typeFilter = '';

    public $statusFilter = '';

    public bool $filterChanged = false;

    public $showModal = false;

    public $editMode = false;

    // Form fields
    public $materialId;

    public $title;

    public $description;

    public $type = 'audio';

    public $category;

    public $is_published = false;

    public $file; // Livewire upload — main file (audio/video)

    public $thumbnail; // Livewire upload — thumbnail image (optional)

    // Loaded from DB for edit display
    public $existingFile = null;

    public $existingThumbnail = null;

    // Delete Modal
    public $showDeleteModal = false;

    public $deletingMaterialId;

    public $deletingMaterialName;

    public function mount()
    {
        $this->authorize('viewAny', TrainingMaterial::class);
        $this->sortField = 'created_at';
        $this->sortDir = 'desc';
    }

    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => ['required', 'string', 'in:'.implode(',', TrainingMaterialType::values())],
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
        ];

        // Main file: required on create, optional on edit (replace)
        $rules['file'] = $this->editMode
            ? file_upload_validation_rule('training-materials', false)
            : file_upload_validation_rule('training-materials', true);

        // Thumbnail: always optional
        $rules['thumbnail'] = file_upload_validation_rule('training-thumbnail', false);

        return $rules;
    }

    public function validationAttributes()
    {
        return [
            'title' => 'judul materi',
            'description' => 'deskripsi',
            'type' => 'tipe materi',
            'category' => 'kategori',
            'file' => 'file materi',
            'thumbnail' => 'thumbnail',
            'is_published' => 'status publish',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingTypeFilter()
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
        $this->typeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getTypeOptionsProperty(): array
    {
        return collect(TrainingMaterialType::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function getStatusOptionsProperty(): array
    {
        return [
            ['value' => 'published', 'label' => 'Published'],
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'processing', 'label' => 'Memproses'],
            ['value' => 'completed', 'label' => 'File Selesai'],
            ['value' => 'failed', 'label' => 'File Gagal'],
        ];
    }

    public function create()
    {
        $this->authorize('create', TrainingMaterial::class);
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function toggleIsPublished()
    {
        $this->is_published = ! $this->is_published;
    }

    public function edit($id)
    {
        $material = TrainingMaterial::findOrFail($id);
        $this->authorize('update', $material);

        $this->materialId = $material->id;
        $this->title = $material->title;
        $this->description = $material->description;
        $this->type = $material->type->value;
        $this->category = $material->category;
        $this->is_published = $material->is_published;

        // Load existing file/thumbnail info for display
        $this->existingFile = $material->file_name ? [
            'name' => $material->file_name,
            'status' => $material->file_status->value,
            'size' => $material->file_size_formatted,
        ] : null;

        $this->existingThumbnail = $material->thumbnail_path ? [
            'path' => $material->thumbnail_path,
            'name' => $material->thumbnail_name,
            'status' => $material->thumbnail_status?->value,
        ] : null;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(TrainingMaterialService $service, FileStorageService $fileStorage)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'type' => $this->type,
                'category' => $this->category,
                'is_published' => $this->is_published,
            ];

            // Store main file to temp if uploaded
            $filePayload = null;
            if ($this->file) {
                $temp = $fileStorage->storeTemp($this->file, 'training-materials');
                $filePayload = [
                    'temp_path' => $temp['path'],
                    'original_name' => $temp['original_name'],
                ];
            }

            // Store thumbnail to temp if uploaded
            $thumbnailPayload = null;
            if ($this->thumbnail) {
                $temp = $fileStorage->storeTemp($this->thumbnail, 'training-thumbnails');
                $thumbnailPayload = [
                    'temp_path' => $temp['path'],
                    'original_name' => $temp['original_name'],
                ];
            }

            if ($this->editMode) {
                $material = TrainingMaterial::findOrFail($this->materialId);
                $this->authorize('update', $material);

                $service->update($material, $data, $filePayload, $thumbnailPayload);
                $message = 'Materi pelatihan berhasil diupdate!';
            } else {
                $this->authorize('create', TrainingMaterial::class);

                $service->create($data, $filePayload, $thumbnailPayload);
                $message = 'Materi pelatihan berhasil ditambahkan! File sedang diproses.';
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
        $material = TrainingMaterial::findOrFail($id);
        $this->deletingMaterialId = $material->id;
        $this->deletingMaterialName = $material->title;
        $this->showDeleteModal = true;
    }

    public function delete(TrainingMaterialService $service)
    {
        try {
            $material = TrainingMaterial::findOrFail($this->deletingMaterialId);
            $this->authorize('delete', $material);

            $service->delete($material);
            $this->notifySuccess('Materi pelatihan berhasil dihapus!');
            $this->showDeleteModal = false;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak dapat menghapus materi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function togglePublish($id, TrainingMaterialService $service)
    {
        try {
            $material = TrainingMaterial::findOrFail($id);
            $this->authorize('togglePublish', $material);

            if (! $material->is_ready) {
                $this->notifyError('Materi belum bisa dipublish karena file masih diproses atau gagal.');

                return;
            }

            $service->togglePublish($material);
            $status = $material->fresh()->is_published ? 'published' : 'unpublished';
            $this->notifySuccess("Materi berhasil di-{$status}!");
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk mengubah status publish.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    /**
     * Remove uploaded (but not yet saved) file from form state.
     */
    public function removeFile()
    {
        $this->file = null;
        $this->resetValidation('file');
    }

    /**
     * Remove uploaded (but not yet saved) thumbnail from form state.
     */
    public function removeThumbnail()
    {
        $this->thumbnail = null;
        $this->resetValidation('thumbnail');
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
            'materialId',
            'title',
            'description',
            'type',
            'category',
            'is_published',
            'file',
            'thumbnail',
            'existingFile',
            'existingThumbnail',
        ]);
        $this->type = 'audio';
        $this->is_published = false;
    }

    public function render(TrainingMaterialService $service)
    {
        $materials = $service->getFiltered(
            $this->search,
            $this->typeFilter,
            $this->statusFilter,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$materials->total()} data materi.");
            $this->filterChanged = false;
        }

        return view('livewire.training.training-manage', [
            'materials' => $materials,
        ]);
    }
}
