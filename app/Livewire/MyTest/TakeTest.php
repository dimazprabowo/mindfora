<?php

namespace App\Livewire\MyTest;

use App\Livewire\Traits\HasNotification;
use App\Models\TestAttempt;
use App\Models\TestProgramAssignment;
use App\Models\TestProgramWeek;
use App\Services\TestAttemptService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TakeTest extends Component
{
    use AuthorizesRequests, HasNotification;

    public TestProgramWeek $week;

    public ?TestProgramAssignment $assignment = null;

    public ?TestAttempt $attempt = null;

    // Answers: keyed by question_id
    public array $answers = [];

    public function mount(TestProgramWeek $week)
    {
        $this->authorize('take', [$week]);

        $this->week = $week->load(['questions.options' => fn ($q) => $q->orderBy('order')]);

        $service = app(TestAttemptService::class);
        $this->assignment = $service->getActiveAssignmentForUser(auth()->user());

        if (! $this->assignment) {
            $this->notifyError('Tidak ada program aktif saat ini.');

            return $this->redirect(route('my-test.index'), navigate: true);
        }

        // Verify week belongs to this assignment's program
        if ($week->test_program_id !== $this->assignment->test_program_id) {
            $this->notifyError('Minggu ini tidak termasuk dalam program aktif.');

            return $this->redirect(route('my-test.index'), navigate: true);
        }

        // Verify week is unlocked
        $currentWeek = $this->assignment->getCurrentWeekNumber();
        if ($currentWeek === null || $week->week_number > $currentWeek) {
            $this->notifyError('Minggu ini masih terkunci.');

            return $this->redirect(route('my-test.index'), navigate: true);
        }

        // Start or resume attempt
        $this->attempt = $service->startOrResumeAttempt(auth()->user(), $this->assignment, $week);

        // Load existing answers
        $this->loadExistingAnswers();
    }

    protected function loadExistingAnswers(): void
    {
        $this->answers = [];
        foreach ($this->attempt->answers as $answer) {
            $this->answers[$answer->question_id] = [
                'option_id' => $answer->question_option_id,
                'option_ids' => $answer->selected_option_ids ?? [],
                'text' => $answer->answer_text,
            ];
        }
    }

    /**
     * Autosave (debounced from Blade).
     */
    public function saveProgress(TestAttemptService $service)
    {
        $service->saveAnswers($this->attempt, $this->answers);
        $this->notifyInfo('Jawaban tersimpan otomatis.');
    }

    /**
     * Submit the test.
     */
    public function submit(TestAttemptService $service)
    {
        // Validate required questions
        $errors = [];
        foreach ($this->week->questions as $question) {
            if (! $question->is_required) {
                continue;
            }
            $answer = $this->answers[$question->id] ?? null;
            $isEmpty = ! $answer
                || ($question->type->value === 'text' && empty($answer['text']))
                || (in_array($question->type->value, ['radio', 'likert']) && empty($answer['option_id']))
                || ($question->type->value === 'checkbox' && empty($answer['option_ids']));

            if ($isEmpty) {
                $errors["answers.{$question->id}"] = 'Pertanyaan ini wajib dijawab.';
            }
        }

        if (! empty($errors)) {
            $this->notifyError('Masih ada pertanyaan wajib yang belum dijawab.');

            return;
        }

        try {
            $attempt = $service->submitAttempt($this->attempt, $this->answers);

            $this->notifySuccess("Test berhasil disubmit! Skor: {$attempt->score_total} ({$attempt->score_category->label()})");

            return $this->redirect(route('my-test.index'), navigate: true);
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan saat submit. Silakan coba lagi.');
        }
    }

    public function cancel()
    {
        // Save progress before leaving
        app(TestAttemptService::class)->saveAnswers($this->attempt, $this->answers);

        return $this->redirect(route('my-test.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.my-test.take-test');
    }
}
