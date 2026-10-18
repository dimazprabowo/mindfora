<?php

namespace App\Livewire\Training;

use App\Livewire\Traits\HasNotification;
use App\Services\TrainingMaterialService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TrainingLibrary extends Component
{
    use AuthorizesRequests, HasNotification, WithPagination;

    public $search = '';

    public $typeFilter = '';

    public bool $filterChanged = false;

    public function mount()
    {
        $this->authorize('viewLibrary', \App\Models\TrainingMaterial::class);
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

    public function resetFilters()
    {
        $this->typeFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getTypeOptionsProperty(): array
    {
        return collect(\App\Enums\TrainingMaterialType::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function render(TrainingMaterialService $service)
    {
        $materials = $service->getPublishedLibrary($this->search, $this->typeFilter);

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$materials->total()} materi.");
            $this->filterChanged = false;
        }

        return view('livewire.training.training-library', [
            'materials' => $materials,
        ]);
    }
}
