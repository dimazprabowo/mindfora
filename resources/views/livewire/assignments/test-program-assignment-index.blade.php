<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('assignments.search_placeholder') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['statusFilter', 'programFilter']">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('common.status') }}</label>
                    <x-searchable-select
                        wire:model.live="statusFilter"
                        :options="$this->statusOptions"
                        placeholder="{{ __('assignments.all_status') }}"
                        searchPlaceholder="{{ __('assignments.search_status') }}"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('assignments.program') }}</label>
                    <x-searchable-select
                        wire:model.live="programFilter"
                        :options="$this->programOptions"
                        placeholder="{{ __('assignments.all_programs') }}"
                        searchPlaceholder="{{ __('assignments.search_program') }}"
                    />
                </div>
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @can('test_assignments_create')
                <x-loading-button wire:click="create" target="create" variant="primary" size="md" loadingText="{{ __('common.loading') }}" class="flex-1 sm:flex-none" icon="plus">
                    {{ __('assignments.add_assignment') }}
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <x-sortable-header field="program" :sortField="$sortField" :sortDir="$sortDir">{{ __('assignments.program') }}</x-sortable-header>
                        <x-sortable-header field="label" :sortField="$sortField" :sortDir="$sortDir">{{ __('assignments.label') }}</x-sortable-header>
                        <x-sortable-header field="start_date" :sortField="$sortField" :sortDir="$sortDir">{{ __('assignments.period') }}</x-sortable-header>
                        <x-sortable-header field="attempts_count" :sortField="$sortField" :sortDir="$sortDir">{{ __('assignments.attempts') }}</x-sortable-header>
                        <x-sortable-header field="status" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.status') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($assignments as $assignment)
                        <tr wire:key="row-assign-{{ $assignment->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->program?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('assignments.weeks_count', ['count' => $assignment->program?->weeks_count ?? 0]) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $assignment->label ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $assignment->start_date?->format('d M Y') }}</div>
                                @if($assignment->end_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('assignments.to') }} {{ $assignment->end_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-200">
                                    {{ __('assignments.attempt_count', ['count' => $assignment->attempts_count]) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $assignment->status->badgeClass() }}">
                                    {{ $assignment->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('test_assignments_update')
                                        @if($assignment->status->value === 'scheduled')
                                            <x-loading-button wire:click="activate({{ $assignment->id }})"
                                                target="activate({{ $assignment->id }})"
                                                variant="icon-green" icon="check"
                                                wire:key="btn-activate-{{ $assignment->id }}"
                                                title="{{ __('common.activate') }}" />
                                        @endif
                                        @if(in_array($assignment->status->value, ['scheduled', 'active']))
                                            <x-loading-button wire:click="complete({{ $assignment->id }})"
                                                target="complete({{ $assignment->id }})"
                                                variant="icon-amber" icon="check"
                                                wire:key="btn-complete-{{ $assignment->id }}"
                                                title="{{ __('assignments.complete') }}" />
                                            <x-loading-button wire:click="cancel({{ $assignment->id }})"
                                                target="cancel({{ $assignment->id }})"
                                                variant="icon-red" icon="close"
                                                wire:key="btn-cancel-{{ $assignment->id }}"
                                                title="{{ __('assignments.cancel_action') }}" />
                                        @endif
                                        <x-loading-button wire:click="edit({{ $assignment->id }})"
                                            target="edit({{ $assignment->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $assignment->id }}"
                                            title="{{ __('common.edit') }}" />
                                    @endcan
                                    @can('test_assignments_delete')
                                        <x-loading-button wire:click="confirmDelete({{ $assignment->id }})"
                                            target="confirmDelete({{ $assignment->id }})"
                                            variant="icon-red" icon="delete"
                                            wire:key="btn-delete-{{ $assignment->id }}"
                                            title="{{ __('common.delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('assignments.no_assignments') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $assignments->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeModal()"></div>

                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editMode ? __('assignments.edit_title') : __('assignments.create_title') }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <x-input-label for="test_program_id" value="{{ __('assignments.test_program') }}" />
                                    <div class="mt-1">
                                        <x-searchable-select
                                            wire:model.live="test_program_id"
                                            :options="$this->programOptions"
                                            placeholder="{{ __('assignments.select_program') }}"
                                            searchPlaceholder="{{ __('assignments.search_program') }}"
                                        />
                                    </div>
                                    <x-input-error for="test_program_id" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="label" value="{{ __('assignments.label_optional') }}" />
                                    <x-text-input id="label" wire:model="label" type="text" class="mt-1 block w-full" placeholder="{{ __('assignments.label_placeholder') }}" />
                                    <x-input-error for="label" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="start_date" value="{{ __('common.start_date') }}" />
                                    <x-text-input id="start_date" wire:model="start_date" type="date" class="mt-1 block w-full" />
                                    <x-input-error for="start_date" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="end_date" value="{{ __('assignments.end_date_optional') }}" />
                                    <x-text-input id="end_date" wire:model="end_date" type="date" class="mt-1 block w-full" />
                                    <x-input-error for="end_date" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="status" value="{{ __('common.status') }}" />
                                    <div class="mt-1">
                                        <x-searchable-select
                                            wire:model.live="status"
                                            :options="$this->statusOptions"
                                            placeholder="{{ __('assignments.select_status') }}"
                                            searchPlaceholder="{{ __('assignments.search_status') }}"
                                        />
                                    </div>
                                    <x-input-error for="status" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                                loadingText="{{ __('assignments.saving') }}" class="w-full sm:w-auto">
                                {{ $editMode ? __('assignments.update') : __('common.save') }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeModal" target="closeModal"
                                class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    <x-delete-modal
        :show="$showDeleteModal"
        wire:model="showDeleteModal"
        title="{{ __('assignments.delete_title') }}"
        message="{{ __('assignments.delete_message') }}"
        :itemName="$deletingAssignmentLabel"
        confirmMethod="delete"
    />
</div>
