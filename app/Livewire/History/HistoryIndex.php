<?php

namespace App\Livewire\History;

use App\Livewire\Traits\HasNotification;
use App\Services\TestAttemptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class HistoryIndex extends Component
{
    use AuthorizesRequests, HasNotification;

    public function mount()
    {
        $this->authorize('viewOwnHistory', \App\Models\TestAttempt::class);
    }

    public function render(TestAttemptService $service)
    {
        $user = auth()->user();
        $attempts = $service->getUserHistory($user);

        // Group by assignment for trend view
        $grouped = $attempts->groupBy(fn ($a) => $a->test_program_assignment_id)->map(function ($group) {
            // Only take best attempt per week
            return $group->filter(fn ($a) => $a->is_best)->sortBy(fn ($a) => $a->week?->week_number ?? 0)->values();
        })->filter(fn ($group) => $group->isNotEmpty());

        return view('livewire.history.history-index', [
            'attempts' => $attempts,
            'grouped' => $grouped,
        ]);
    }
}
