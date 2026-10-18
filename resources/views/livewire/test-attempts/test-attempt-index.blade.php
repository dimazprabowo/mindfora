<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('test_attempts.search_placeholder') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['categoryFilter', 'weekFilter']">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('test_attempts.stress_category') }}</label>
                    <x-searchable-select
                        wire:model.live="categoryFilter"
                        :options="$this->categoryOptions"
                        placeholder="{{ __('test_attempts.all_categories') }}"
                        searchPlaceholder="{{ __('test_attempts.search_category') }}"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('test_attempts.week') }}</label>
                    <x-searchable-select
                        wire:model.live="weekFilter"
                        :options="$this->weekOptions"
                        placeholder="{{ __('test_attempts.all_weeks') }}"
                        searchPlaceholder="{{ __('test_attempts.search_week') }}"
                    />
                </div>
            </div>
        </x-filter-popover>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <x-sortable-header field="user" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.user') }}</x-sortable-header>
                        <x-sortable-header field="program" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.program') }}</x-sortable-header>
                        <x-sortable-header field="week" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.week') }}</x-sortable-header>
                        <x-sortable-header field="score_total" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.score') }}</x-sortable-header>
                        <x-sortable-header field="score_category" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.category') }}</x-sortable-header>
                        <x-sortable-header field="is_best" :sortField="$sortField" :sortDir="$sortDir">{{ __('test_attempts.best') }}</x-sortable-header>
                        <x-sortable-header field="submitted_at" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.submit') }}</x-sortable-header>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($attempts as $attempt)
                        <tr wire:key="row-attempt-{{ $attempt->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $attempt->user?->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $attempt->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $attempt->week?->program?->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $attempt->assignment?->label ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900 dark:text-white">{{ __('test_attempts.week_number', ['number' => $attempt->week?->week_number]) }}</span>
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
                                <div class="text-sm text-gray-900 dark:text-white">{{ $attempt->submitted_at?->format('d M Y H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('test_attempts.no_attempts') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $attempts->links() }}
        </div>
    </div>
</div>
