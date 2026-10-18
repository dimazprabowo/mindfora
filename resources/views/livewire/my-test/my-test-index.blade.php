<div>
    @if(!$assignment)
        {{-- No active assignment --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('my_test.no_active_program') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('my_test.no_active_program_desc') }}
            </p>
        </div>
    @else
        {{-- Program info header --}}
        <div class="mb-4 sm:mb-6 bg-gradient-to-br from-primary-600 to-primary-800 rounded-lg p-4 sm:p-6 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base sm:text-xl font-bold break-words">{{ $assignment->program->name }}</h2>
                    @if($assignment->label)
                        <p class="text-primary-100 text-xs sm:text-sm mt-1 break-words">{{ $assignment->label }}</p>
                    @endif
                    <p class="text-primary-200 text-xs mt-2 break-words">
                        {{ __('common.start_date') }}: {{ $assignment->start_date->format('d M Y') }}
                        @if($assignment->end_date) | {{ __('common.end_date') }}: {{ $assignment->end_date->format('d M Y') }} @endif
                    </p>
                </div>
                <div class="flex sm:block items-baseline gap-2 sm:text-right flex-shrink-0">
                    <div class="text-xl sm:text-3xl font-bold">
                        {{ __('my_test.week') }} {{ $assignment->getCurrentWeekNumber() ?? '-' }}
                    </div>
                    <div class="text-primary-200 text-xs">{{ __('my_test.of_4_weeks') }}</div>
                </div>
            </div>
        </div>

        {{-- Week cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @foreach($weekStatuses as $status)
                @php
                    $week = $status['week'];
                    $isLocked = $status['unlock_status'] === 'locked';
                    $isCurrent = $status['is_current'];
                @endphp
                <div wire:key="week-card-{{ $week->id }}"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border {{ $isCurrent ? 'border-primary-500 dark:border-primary-500 ring-1 ring-primary-200 dark:ring-primary-900' : 'border-gray-200 dark:border-gray-700' }} p-4 sm:p-5 {{ $isLocked ? 'opacity-60' : '' }}">

                    <div class="flex items-start justify-between mb-3 gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full {{ $isLocked ? 'bg-gray-200 dark:bg-gray-700 text-gray-400' : 'bg-primary-600 text-white' }} flex items-center justify-center font-bold flex-shrink-0">
                                {{ $week->week_number }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                    <span>{{ __('my_test.week') }} {{ $week->week_number }}</span>
                                    @if($isCurrent)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-200">{{ __('my_test.now') }}</span>
                                    @endif
                                </h3>
                                @if($week->title)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $week->title }}</p>
                                @endif
                            </div>
                        </div>
                        @if($isLocked)
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>

                    <div class="space-y-1.5 sm:space-y-2 text-sm">
                        {{-- Unlock info --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400 flex-shrink-0">{{ __('common.status') }}:</span>
                            <span class="text-xs {{ $isLocked ? 'text-gray-400' : 'text-primary-600 dark:text-primary-400' }} text-right truncate">{{ $status['unlock_label'] }}</span>
                        </div>

                        {{-- Questions count --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-gray-500 dark:text-gray-400 flex-shrink-0">{{ __('my_test.questions') }}:</span>
                            <span class="text-gray-900 dark:text-white flex-shrink-0">{{ $week->questions_count }}</span>
                        </div>

                        {{-- Attempts --}}
                        @if($status['attempts_count'] > 0)
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400 flex-shrink-0">{{ __('my_test.attempts') }}:</span>
                                <span class="text-gray-900 dark:text-white flex-shrink-0">{{ $status['attempts_count'] }}x</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400 flex-shrink-0">{{ __('my_test.best_score') }}:</span>
                                <span class="font-semibold {{ $status['best_category']?->badgeClass() }} px-2 py-0.5 rounded text-xs flex-shrink-0">
                                    {{ $status['best_score'] }} ({{ $status['best_category']?->label() }})
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Action button --}}
                    <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 dark:border-gray-700">
                        @if($isLocked)
                            <button disabled class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                {{ __('my_test.locked') }}
                            </button>
                        @else
                            @php
                                $takeVariant = $status['has_in_progress'] ? 'warning' : 'primary';
                                $takeIcon = $status['has_in_progress'] ? 'play' : ($status['attempts_count'] > 0 ? 'reset' : 'plus');
                                $takeText = $status['has_in_progress']
                                    ? __('my_test.continue')
                                    : ($status['attempts_count'] > 0 ? __('my_test.retry') : __('my_test.start_test'));
                            @endphp
                            <x-loading-button wire:click="takeTest({{ $week->id }})"
                                target="takeTest({{ $week->id }})"
                                variant="{{ $takeVariant }}"
                                icon="{{ $takeIcon }}"
                                size="md"
                                loadingText="{{ __('common.loading') }}"
                                wire:key="btn-take-{{ $week->id }}"
                                class="w-full">
                                {{ $takeText }}
                            </x-loading-button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
