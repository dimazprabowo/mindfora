<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('programs.search_placeholder') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['statusFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('common.status') }}</label>
                <x-searchable-select
                    wire:model.live="statusFilter"
                    :options="$this->statusOptions"
                    placeholder="{{ __('programs.all_status') }}"
                    searchPlaceholder="{{ __('programs.search_status') }}"
                />
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @can('test_programs_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" variant="success" size="md" loadingText="{{ __('programs.exporting') }}" title="{{ __('common.export_excel') }}" icon="excel">
                    Excel
                </x-loading-button>
            @endcan
            @can('test_programs_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" variant="danger" size="md" loadingText="{{ __('programs.exporting') }}" title="{{ __('common.export_pdf') }}" icon="pdf">
                    PDF
                </x-loading-button>
            @endcan
            @can('test_programs_create')
                <x-loading-button wire:click="create" target="create" variant="primary" size="md"
                    loadingText="{{ __('common.loading') }}" icon="plus" class="flex-1 sm:flex-none">
                    {{ __('programs.add_program') }}
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <x-sortable-header field="name" :sortField="$sortField" :sortDir="$sortDir">{{ __('programs.program') }}</x-sortable-header>
                        <x-sortable-header field="weeks_count" :sortField="$sortField" :sortDir="$sortDir">{{ __('programs.weeks') }}</x-sortable-header>
                        <x-sortable-header field="creator" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.created_by') }}</x-sortable-header>
                        <x-sortable-header field="status" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.status') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($programs as $program)
                        <tr wire:key="row-program-{{ $program->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $program->name }}</div>
                                @if($program->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $program->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-200">
                                    {{ __('programs.weeks_count', ['count' => $program->weeks_count]) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $program->creator?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $program->created_at?->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $program->status->badgeClass() }}">
                                    {{ $program->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('test_programs_update')
                                        <x-loading-button wire:click="edit({{ $program->id }})"
                                            target="edit({{ $program->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $program->id }}"
                                            title="{{ __('common.edit') }}" />
                                    @endcan
                                    @can('test_programs_publish')
                                        @if($program->status->value === 'draft')
                                            <x-loading-button wire:click="publish({{ $program->id }})"
                                                target="publish({{ $program->id }})"
                                                variant="icon-green" icon="check"
                                                wire:key="btn-publish-{{ $program->id }}"
                                                title="{{ __('programs.publish') }}" />
                                        @endif
                                    @endcan
                                    @can('test_programs_update')
                                        @if($program->status->value === 'active')
                                            <x-loading-button wire:click="archive({{ $program->id }})"
                                                target="archive({{ $program->id }})"
                                                variant="icon-amber" icon="reset"
                                                wire:key="btn-archive-{{ $program->id }}"
                                                title="{{ __('programs.archive') }}" />
                                        @endif
                                    @endcan
                                    @can('test_programs_delete')
                                        <x-loading-button wire:click="confirmDelete({{ $program->id }})"
                                            target="confirmDelete({{ $program->id }})"
                                            variant="icon-red" icon="delete"
                                            wire:key="btn-delete-{{ $program->id }}"
                                            title="{{ __('common.delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('programs.no_programs') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $programs->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-delete-modal
        :show="$showDeleteModal"
        wire:model="showDeleteModal"
        title="{{ __('programs.delete_title') }}"
        message="{{ __('programs.delete_message') }}"
        :itemName="$deletingProgramName"
        confirmMethod="delete"
    />
</div>
