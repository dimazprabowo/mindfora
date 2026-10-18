<div>
    {{-- Breadcrumb --}}
    <nav class="mb-4 sm:mb-6 flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li>
                <a href="{{ route('my-test.index') }}" wire:navigate class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ __('menu.my_test') }}</a>
            </li>
            <li class="flex-shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </li>
            <li class="text-gray-700 dark:text-gray-300" aria-current="page">{{ __('my_test.week') }} {{ $week->week_number }}</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="mb-4 sm:mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-base sm:text-lg flex-shrink-0">
                {{ $week->week_number }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white break-words">
                    {{ __('my_test.week') }} {{ $week->week_number }}
                    @if($week->title) - {{ $week->title }} @endif
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    {{ $week->questions_count }} {{ __('my_test.questions_label') }}
                    @if($attempt->status === \App\Enums\AttemptStatus::InProgress)
                        | <span class="text-amber-600 dark:text-amber-400">{{ __('my_test.in_progress') }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <form wire:submit="submit" class="space-y-4">
        @foreach($week->questions as $index => $question)
            @php
                $qId = $question->id;
                $currentAnswer = $answers[$qId] ?? null;
            @endphp
            <div wire:key="question-{{ $qId }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
                <div class="flex items-start gap-2.5 sm:gap-3 mb-4">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-200 flex items-center justify-center text-sm font-semibold">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white break-words">
                            {{ $question->label }}
                            @if($question->is_required)
                                <span class="text-red-500">*</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $question->type->label() }}</p>
                    </div>
                </div>

                {{-- Answer input by type --}}
                <div class="ml-9 sm:ml-10">
                    @if($question->type->value === 'radio' || $question->type->value === 'likert')
                        <div class="space-y-2">
                            @foreach($question->options as $option)
                                @php
                                    $optId = $option->id;
                                    $isChecked = ($currentAnswer['option_id'] ?? null) == $optId;
                                @endphp
                                <label wire:key="opt-{{ $qId }}-{{ $optId }}" class="flex items-center gap-2.5 sm:gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors {{ $isChecked ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                    <input type="radio"
                                        name="question-{{ $qId }}"
                                        value="{{ $optId }}"
                                        wire:model.live.debounce.500ms="answers.{{ $qId }}.option_id"
                                        class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500 flex-shrink-0"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300 min-w-0 break-words">{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type->value === 'checkbox')
                        <div class="space-y-2">
                            @foreach($question->options as $option)
                                @php
                                    $optId = $option->id;
                                    $selectedIds = $currentAnswer['option_ids'] ?? [];
                                    $isChecked = in_array($optId, $selectedIds);
                                @endphp
                                <label wire:key="opt-{{ $qId }}-{{ $optId }}" class="flex items-center gap-2.5 sm:gap-3 p-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors {{ $isChecked ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                    <input type="checkbox"
                                        value="{{ $optId }}"
                                        wire:model.live.debounce.500ms="answers.{{ $qId }}.option_ids"
                                        class="w-4 h-4 rounded text-primary-600 border-gray-300 focus:ring-primary-500 flex-shrink-0"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300 min-w-0 break-words">{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type->value === 'text')
                        <textarea
                            wire:model.live.debounce.500ms="answers.{{ $qId }}.text"
                            rows="3"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 px-3 py-2"
                            placeholder="{{ __('my_test.write_answer') }}">{{ $currentAnswer['text'] ?? '' }}</textarea>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Action Bar --}}
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-3 sm:p-4 -mx-4 sm:mx-0 sm:rounded-lg sm:shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-2.5 sm:gap-3">
                <x-loading-button wire:click="cancel" target="cancel" variant="secondary" size="md"
                    loadingText="{{ __('my_test.saving') }}" class="w-full sm:w-auto">
                    {{ __('my_test.save_and_exit') }}
                </x-loading-button>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline">
                        <svg class="inline w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('my_test.auto_saved') }}
                    </span>
                    <x-loading-button type="submit" target="submit" variant="primary" size="md"
                        loadingText="{{ __('my_test.saving') }}" class="w-full sm:w-auto">
                        {{ __('my_test.submit_test') }}
                    </x-loading-button>
                </div>
            </div>
        </div>
    </form>
</div>
