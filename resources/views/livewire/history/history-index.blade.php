<div>
    @if($attempts->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('history.no_history') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('history.no_history_desc') }}
            </p>
            <a href="{{ route('my-test.index') }}" wire:navigate
               class="mt-4 inline-flex items-center gap-2 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('history.start_test') }}
            </a>
        </div>
    @else
        {{-- Trend per assignment --}}
        @foreach($grouped as $assignmentId => $bestAttempts)
            @php
                $firstAttempt = $bestAttempts->first();
                $assignment = $firstAttempt?->assignment;
            @endphp
            <div class="mb-8">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $assignment?->program?->name ?? 'Program' }}
                    </h3>
                    @if($assignment?->label)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $assignment->label }}</p>
                    @endif
                </div>

                {{-- Trend chart (simple bar visualization) --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('history.score_trend_per_week') }}</h4>
                    <div class="flex items-end justify-between gap-4 h-48">
                        @foreach([1, 2, 3, 4] as $weekNum)
                            @php
                                $attempt = $bestAttempts->firstWhere(fn ($a) => $a->week?->week_number === $weekNum);
                                $score = $attempt?->score_total;
                                $maxScore = $attempt?->week?->max_score ?? 100;
                                $heightPercent = $score !== null ? max(5, ($score / max($maxScore, 1)) * 100) : 0;
                                $category = $attempt?->score_category;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-2">
                                <div class="text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $score ?? '-' }}
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-t-md relative" style="height: 160px;">
                                    @if($score !== null)
                                        <div class="absolute bottom-0 inset-x-0 rounded-t-md transition-all {{ $category?->color() === 'green' ? 'bg-green-500' : ($category?->color() === 'amber' ? 'bg-amber-500' : 'bg-red-500') }}"
                                             style="height: {{ $heightPercent }}%;"></div>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('history.week') }} {{ $weekNum }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Detail table --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('history.week') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('history.score') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('history.category') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('history.best') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('history.submitted') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($bestAttempts as $attempt)
                                    <tr wire:key="row-hist-{{ $attempt->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('history.week') }} {{ $attempt->week?->week_number }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $attempt->score_total }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $attempt->score_category?->badgeClass() }}">
                                                {{ $attempt->score_category?->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($attempt->is_best)
                                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-white">{{ $attempt->submitted_at?->format('d M Y H:i') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
