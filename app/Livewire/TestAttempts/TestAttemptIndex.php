<?php

namespace App\Livewire\TestAttempts;

use App\Enums\StressCategory;
use App\Livewire\Traits\HasNotification;
use App\Livewire\Traits\HasTableSorting;
use App\Models\TestAttempt;
use App\Services\TestAttemptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TestAttemptIndex extends Component
{
    use AuthorizesRequests, HasNotification, HasTableSorting, WithPagination;

    public $search = '';

    public $categoryFilter = '';

    public $weekFilter = '';

    public bool $filterChanged = false;

    public function mount()
    {
        $this->authorize('viewAny', TestAttempt::class);
        $this->sortField = 'submitted_at';
        $this->sortDir = 'desc';
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function updatingWeekFilter()
    {
        $this->resetPage();
        $this->filterChanged = true;
    }

    public function resetFilters()
    {
        $this->categoryFilter = '';
        $this->weekFilter = '';
        $this->resetPage();
        $this->filterChanged = true;
        $this->notifySuccess('Filter berhasil direset.');
    }

    public function getCategoryOptionsProperty(): array
    {
        return collect(StressCategory::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function getWeekOptionsProperty(): array
    {
        return [
            ['value' => '1', 'label' => 'Minggu 1'],
            ['value' => '2', 'label' => 'Minggu 2'],
            ['value' => '3', 'label' => 'Minggu 3'],
            ['value' => '4', 'label' => 'Minggu 4'],
        ];
    }

    public function render(TestAttemptService $service)
    {
        $attempts = $service->getFilteredAttempts(
            $this->search,
            $this->categoryFilter,
            $this->weekFilter ? (int) $this->weekFilter : null,
            sortField: $this->sortField,
            sortDir: $this->sortDir
        );

        if ($this->filterChanged) {
            $this->notifySuccess("Ditemukan {$attempts->total()} data hasil test.");
            $this->filterChanged = false;
        }

        return view('livewire.test-attempts.test-attempt-index', [
            'attempts' => $attempts,
        ]);
    }
}
