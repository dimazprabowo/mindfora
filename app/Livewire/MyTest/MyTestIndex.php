<?php

namespace App\Livewire\MyTest;

use App\Livewire\Traits\HasNotification;
use App\Models\TestProgramWeek;
use App\Services\TestAttemptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MyTestIndex extends Component
{
    use AuthorizesRequests, HasNotification;

    public function mount()
    {
        $this->authorize('viewOwnHistory', \App\Models\TestAttempt::class);
    }

    public function takeTest($weekId)
    {
        $week = TestProgramWeek::findOrFail($weekId);
        $this->authorize('viewOwnHistory', \App\Models\TestAttempt::class);
        $this->redirect(route('my-test.take', $week), navigate: true);
    }

    public function render(TestAttemptService $service)
    {
        $user = auth()->user();
        $assignment = $service->getActiveAssignmentForUser($user);
        $weekStatuses = $assignment ? $service->getWeekStatusesForUser($user, $assignment) : [];

        return view('livewire.my-test.my-test-index', [
            'assignment' => $assignment,
            'weekStatuses' => $weekStatuses,
        ]);
    }
}
