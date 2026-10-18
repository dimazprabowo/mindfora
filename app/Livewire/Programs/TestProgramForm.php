<?php

namespace App\Livewire\Programs;

use App\Enums\QuestionType;
use App\Enums\TestProgramStatus;
use App\Livewire\Traits\HasNotification;
use App\Models\TestProgram;
use App\Services\TestProgramService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TestProgramForm extends Component
{
    use AuthorizesRequests, HasNotification;

    public $program;

    public bool $editMode = false;

    // Form fields
    public $name = '';

    public $description = '';

    public $status = 'draft';

    // Nested weeks (repeater)
    public array $weeks = [];

    public function mount(?TestProgram $program = null)
    {
        if ($program && $program->exists) {
            $this->authorize('update', $program);
            $this->editMode = true;
            $this->program = $program;

            $this->name = $program->name;
            $this->description = $program->description ?? '';
            $this->status = $program->status->value;

            // Load weeks with nested questions + options
            $this->weeks = $program->weeks()
                ->orderBy('week_number')
                ->with(['questions.options' => fn ($q) => $q->orderBy('order')])
                ->get()
                ->map(fn ($week) => [
                    'id' => $week->id,
                    'week_number' => $week->week_number,
                    'title' => $week->title ?? '',
                    'is_active' => $week->is_active,
                    'threshold_low_max' => $week->threshold_low_max,
                    'threshold_medium_max' => $week->threshold_medium_max,
                    'questions' => $week->questions->map(fn ($question) => [
                        'id' => $question->id,
                        'type' => $question->type->value,
                        'label' => $question->label,
                        'is_required' => $question->is_required,
                        'order' => $question->order,
                        'options' => $question->options->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'score' => $option->score,
                            'order' => $option->order,
                        ])->toArray(),
                    ])->toArray(),
                ])
                ->toArray();
        } else {
            $this->authorize('create', TestProgram::class);
            $this->program = new TestProgram;

            // Initialize with 4 empty weeks
            for ($i = 1; $i <= 4; $i++) {
                $this->weeks[] = $this->emptyWeek($i);
            }
        }
    }

    protected function emptyWeek(int $number): array
    {
        return [
            'id' => null,
            'week_number' => $number,
            'title' => '',
            'is_active' => true,
            'threshold_low_max' => null,
            'threshold_medium_max' => null,
            'questions' => [],
        ];
    }

    protected function emptyQuestion(): array
    {
        return [
            'id' => null,
            'type' => 'radio',
            'label' => '',
            'is_required' => true,
            'order' => 0,
            'options' => [],
        ];
    }

    protected function emptyOption(): array
    {
        return [
            'id' => null,
            'label' => '',
            'score' => 0,
            'order' => 0,
        ];
    }

    // --- Week actions ---
    public function toggleWeekActive(int $weekIndex)
    {
        $this->weeks[$weekIndex]['is_active'] = ! $this->weeks[$weekIndex]['is_active'];
    }

    public function toggleQuestionRequired(int $weekIndex, int $questionIndex)
    {
        $this->weeks[$weekIndex]['questions'][$questionIndex]['is_required'] = ! $this->weeks[$weekIndex]['questions'][$questionIndex]['is_required'];
    }

    public function addQuestion(int $weekIndex)
    {
        $this->weeks[$weekIndex]['questions'][] = $this->emptyQuestion();
    }

    public function removeQuestion(int $weekIndex, int $questionIndex)
    {
        unset($this->weeks[$weekIndex]['questions'][$questionIndex]);
        $this->weeks[$weekIndex]['questions'] = array_values($this->weeks[$weekIndex]['questions']);
    }

    // --- Question actions ---
    public function addOption(int $weekIndex, int $questionIndex)
    {
        $this->weeks[$weekIndex]['questions'][$questionIndex]['options'][] = $this->emptyOption();
    }

    public function removeOption(int $weekIndex, int $questionIndex, int $optionIndex)
    {
        unset($this->weeks[$weekIndex]['questions'][$questionIndex]['options'][$optionIndex]);
        $this->weeks[$weekIndex]['questions'][$questionIndex]['options'] = array_values($this->weeks[$weekIndex]['questions'][$questionIndex]['options']);
    }

    // --- When question type changes, clear options if text ---
    public function updatedWeeks($value, $key)
    {
        // Detect type change
        if (str_ends_with($key, '.type')) {
            $parts = explode('.', $key);
            $weekIndex = $parts[0];
            $questionIndex = $parts[2];
            $type = $this->weeks[$weekIndex]['questions'][$questionIndex]['type'] ?? null;

            if ($type === 'text') {
                // Text questions don't have options
                $this->weeks[$weekIndex]['questions'][$questionIndex]['options'] = [];
            } elseif (empty($this->weeks[$weekIndex]['questions'][$questionIndex]['options'])) {
                // Auto-add 2 empty options for radio/checkbox/likert
                $this->weeks[$weekIndex]['questions'][$questionIndex]['options'] = [
                    $this->emptyOption(),
                    $this->emptyOption(),
                ];
            }
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => ['required', 'string', 'in:'.implode(',', TestProgramStatus::values())],
            'weeks' => 'required|array|min:1|max:4',
            'weeks.*.week_number' => 'required|integer|min:1|max:4',
            'weeks.*.title' => 'nullable|string|max:255',
            'weeks.*.is_active' => 'boolean',
            'weeks.*.threshold_low_max' => 'nullable|integer|min:0',
            'weeks.*.threshold_medium_max' => 'nullable|integer|min:0',
            'weeks.*.questions' => 'array',
            'weeks.*.questions.*.type' => ['required', 'string', 'in:'.implode(',', QuestionType::values())],
            'weeks.*.questions.*.label' => 'required|string|max:1000',
            'weeks.*.questions.*.is_required' => 'boolean',
            'weeks.*.questions.*.options' => 'array',
            'weeks.*.questions.*.options.*.label' => 'required|string|max:255',
            'weeks.*.questions.*.options.*.score' => 'nullable|integer|min:0',
        ];
    }

    public function validationAttributes()
    {
        return [
            'name' => 'nama program',
            'description' => 'deskripsi',
            'status' => 'status',
            'weeks' => 'minggu',
            'weeks.*.title' => 'judul minggu',
            'weeks.*.questions.*.label' => 'pertanyaan',
            'weeks.*.questions.*.type' => 'tipe pertanyaan',
            'weeks.*.questions.*.options.*.label' => 'opsi jawaban',
            'weeks.*.questions.*.options.*.score' => 'skor opsi',
        ];
    }

    public function save(TestProgramService $service)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->notifyValidationError($e);
            throw $e;
        }

        try {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'weeks' => $this->weeks,
            ];

            if ($this->editMode) {
                $service->update($this->program, $data);
                $message = 'Program test berhasil diupdate!';
            } else {
                $service->create($data);
                $message = 'Program test berhasil ditambahkan!';
            }

            $this->notifySuccess($message);

            return $this->redirect(route('programs.index'), navigate: true);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->notifyError('Anda tidak memiliki izin untuk melakukan aksi ini.');
        } catch (\Exception $e) {
            $this->notifyError('Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    public function cancel()
    {
        return $this->redirect(route('programs.index'), navigate: true);
    }

    public function getQuestionTypeOptionsProperty(): array
    {
        return collect(QuestionType::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function getStatusOptionsProperty(): array
    {
        return collect(TestProgramStatus::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.programs.test-program-form');
    }
}
