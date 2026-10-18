<div>
    {{-- Breadcrumb --}}
    <nav class="mb-6 flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm">
            <li>
                <a href="{{ route('programs.index') }}" wire:navigate class="text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">{{ __('programs.title') }}</a>
            </li>
            <li>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </li>
            <li class="text-gray-700 dark:text-gray-300" aria-current="page">{{ $editMode ? __('common.edit') : __('common.create') }}</li>
        </ol>
    </nav>

    <form wire:submit="save" class="space-y-6">
        {{-- Program Info Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                {{ __('programs.info_section') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="{{ __('programs.program_name') }}" />
                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="{{ __('programs.name_placeholder') }}" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="status" value="{{ __('common.status') }}" />
                    <div class="mt-1">
                        <x-searchable-select
                            wire:model.live="status"
                            :options="$this->statusOptions"
                            placeholder="{{ __('programs.select_status') }}"
                            searchPlaceholder="{{ __('programs.search_status') }}"
                        />
                    </div>
                    <x-input-error for="status" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="description" value="{{ __('common.description') }}" />
                    <textarea id="description" wire:model="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 px-3 py-2"
                        placeholder="{{ __('programs.description_placeholder') }}"></textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Weeks Section (Nested Repeater) --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('programs.weeks_structure') }}
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('programs.weeks_count_max', ['count' => count($weeks)]) }}
                </span>
            </div>

            @foreach($weeks as $weekIndex => $week)
                <div wire:key="week-{{ $weekIndex }}" x-data="{ open: true }" class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    {{-- Week Header --}}
                    <div role="button" tabindex="0" @click="open = !open" @keydown.enter="open = !open" @keydown.space.prevent="open = !open"
                        class="w-full bg-gray-50 dark:bg-gray-700/50 px-4 py-3 flex items-center justify-between text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-inset">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold text-sm">
                                {{ $week['week_number'] }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('programs.week_number', ['number' => $week['week_number']]) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('programs.questions_count', ['count' => count($week['questions'])]) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400" @click.stop>
                                <span>{{ __('common.active') }}</span>
                                <x-toggle-switch wire:click="toggleWeekActive({{ $weekIndex }})"
                                    :active="$week['is_active']"
                                    target="toggleWeekActive({{ $weekIndex }})"
                                    activeColor="green"
                                    wire:key="toggle-week-active-{{ $weekIndex }}"
                                    title="{{ __('programs.toggle_week') }}" />
                            </label>
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform flex-shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Week Body --}}
                    <div x-show="open" x-collapse.duration.200ms class="p-4 space-y-4">
                        {{-- Week Title --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-1">
                                <x-input-label for="week-{{ $weekIndex }}-title" value="{{ __('programs.week_title') }}" />
                                <x-text-input id="week-{{ $weekIndex }}-title" wire:model="weeks.{{ $weekIndex }}.title" type="text" class="mt-1 block w-full" placeholder="{{ __('common.optional') }}" />
                                <x-input-error for="weeks.{{ $weekIndex }}.title" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="week-{{ $weekIndex }}-low" value="{{ __('programs.threshold_low') }}" />
                                <x-text-input id="week-{{ $weekIndex }}-low" wire:model="weeks.{{ $weekIndex }}.threshold_low_max" type="number" min="0" class="mt-1 block w-full" placeholder="{{ __('programs.automatic') }}" />
                                <x-input-error for="weeks.{{ $weekIndex }}.threshold_low_max" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="week-{{ $weekIndex }}-med" value="{{ __('programs.threshold_medium') }}" />
                                <x-text-input id="week-{{ $weekIndex }}-med" wire:model="weeks.{{ $weekIndex }}.threshold_medium_max" type="number" min="0" class="mt-1 block w-full" placeholder="{{ __('programs.automatic') }}" />
                                <x-input-error for="weeks.{{ $weekIndex }}.threshold_medium_max" class="mt-2" />
                            </div>
                        </div>

                        {{-- Questions --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('programs.questions') }}</h4>
                                <x-loading-button type="button" wire:click="addQuestion({{ $weekIndex }})"
                                    target="addQuestion({{ $weekIndex }})"
                                    variant="link" size="sm" icon="plus"
                                    wire:key="btn-add-question-{{ $weekIndex }}"
                                    loadingText="{{ __('common.adding') }}">
                                    {{ __('programs.add_question') }}
                                </x-loading-button>
                            </div>

                            @foreach($week['questions'] as $questionIndex => $question)
                                <div wire:key="q-{{ $weekIndex }}-{{ $questionIndex }}" class="rounded-md border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-700/20">
                                    {{-- Question Header --}}
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div class="sm:col-span-2 flex flex-col">
                                                <x-input-label for="q-{{ $weekIndex }}-{{ $questionIndex }}-label" value="{{ __('programs.question') }}" />
                                                <textarea id="q-{{ $weekIndex }}-{{ $questionIndex }}-label"
                                                    wire:model="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.label"
                                                    rows="2"
                                                    class="mt-1 block w-full flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 px-3 py-2 min-h-[80px]"
                                                    placeholder="{{ __('programs.question_placeholder') }}"></textarea>
                                                <x-input-error for="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.label" class="mt-2" />
                                            </div>
                                            <div class="flex flex-col gap-2">
                                                <div>
                                                    <x-input-label for="q-{{ $weekIndex }}-{{ $questionIndex }}-type" value="{{ __('programs.type') }}" />
                                                    <div class="mt-1">
                                                        <x-searchable-select id="q-{{ $weekIndex }}-{{ $questionIndex }}-type"
                                                            wire:model.live="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.type"
                                                            :options="$this->questionTypeOptions"
                                                            placeholder="{{ __('programs.select_type') }}"
                                                            searchPlaceholder="{{ __('programs.search_type') }}"
                                                            :error="$errors->has('weeks.'.$weekIndex.'.questions.'.$questionIndex.'.type')"
                                                        />
                                                    </div>
                                                    <x-input-error for="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.type" class="mt-2" />
                                                </div>
                                                {{-- Required toggle (below Type on desktop, inline on mobile) --}}
                                                <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                    <x-toggle-switch wire:click="toggleQuestionRequired({{ $weekIndex }}, {{ $questionIndex }})"
                                                        :active="$question['is_required']"
                                                        target="toggleQuestionRequired({{ $weekIndex }}, {{ $questionIndex }})"
                                                        activeColor="green"
                                                        wire:key="toggle-q-required-{{ $weekIndex }}-{{ $questionIndex }}"
                                                        title="{{ __('common.required') }}" />
                                                    <span>{{ __('common.required') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <x-loading-button type="button"
                                            wire:click="removeQuestion({{ $weekIndex }}, {{ $questionIndex }})"
                                            target="removeQuestion({{ $weekIndex }}, {{ $questionIndex }})"
                                            variant="icon-red" icon="delete"
                                            wire:key="btn-remove-question-{{ $weekIndex }}-{{ $questionIndex }}"
                                            title="{{ __('programs.delete_question') }}" />
                                    </div>

                                    {{-- Options (only for radio/checkbox/likert) --}}
                                    @if(in_array($question['type'], ['radio', 'checkbox', 'likert']))
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('programs.options_score') }}</span>
                                                <x-loading-button type="button"
                                                    wire:click="addOption({{ $weekIndex }}, {{ $questionIndex }})"
                                                    target="addOption({{ $weekIndex }}, {{ $questionIndex }})"
                                                    variant="link" size="xs" icon="plus"
                                                    wire:key="btn-add-option-{{ $weekIndex }}-{{ $questionIndex }}"
                                                    loadingText="{{ __('common.adding') }}">
                                                    {{ __('programs.add_option') }}
                                                </x-loading-button>
                                            </div>

                                            @foreach($question['options'] as $optionIndex => $option)
                                                <div wire:key="opt-{{ $weekIndex }}-{{ $questionIndex }}-{{ $optionIndex }}" class="flex items-center gap-2">
                                                    <div class="flex-1">
                                                        <x-text-input wire:model="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.label"
                                                            type="text" class="block w-full" placeholder="{{ __('programs.option_placeholder') }}" />
                                                        <x-input-error for="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.label" class="mt-1" />
                                                    </div>
                                                    <div class="w-24">
                                                        <x-text-input wire:model="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.score"
                                                            type="number" min="0" class="block w-full" placeholder="{{ __('programs.score') }}" />
                                                        <x-input-error for="weeks.{{ $weekIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.score" class="mt-1" />
                                                    </div>
                                                    <x-loading-button type="button"
                                                        wire:click="removeOption({{ $weekIndex }}, {{ $questionIndex }}, {{ $optionIndex }})"
                                                        target="removeOption({{ $weekIndex }}, {{ $questionIndex }}, {{ $optionIndex }})"
                                                        variant="icon-red" icon="close"
                                                        iconClass="w-4 h-4"
                                                        wire:key="btn-remove-option-{{ $weekIndex }}-{{ $questionIndex }}-{{ $optionIndex }}"
                                                        title="{{ __('programs.delete_option') }}" />
                                                </div>
                                            @endforeach

                                            @if(empty($question['options']))
                                                <p class="text-xs text-gray-400 dark:text-gray-500 italic">{{ __('programs.no_options') }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">{{ __('programs.text_question_no_options') }}</p>
                                    @endif
                                </div>
                            @endforeach

                            @if(empty($week['questions']))
                                <div class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-md">
                                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('programs.no_questions') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
            <x-cancel-button wire:click="cancel" target="cancel" class="w-full sm:w-auto" />
            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                loadingText="{{ __('programs.saving') }}" class="w-full sm:w-auto">
                {{ $editMode ? __('programs.update_program') : __('programs.save_program') }}
            </x-loading-button>
        </div>
    </form>
</div>
