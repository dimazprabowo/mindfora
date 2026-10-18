<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full md:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('master_data.search_department') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['statusFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('common.status') }}</label>
                <x-searchable-select
                    wire:model.live="statusFilter"
                    :options="$this->statusOptions"
                    :placeholder="__('master_data.all_status')"
                    :searchPlaceholder="__('master_data.search_status')"
                />
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            @can('departments_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" variant="success" size="md" :loadingText="__('master_data.exporting')" title="{{ __('common.export_excel') }}" icon="excel">
                    Excel
                </x-loading-button>
            @endcan
            @can('departments_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" variant="danger" size="md" :loadingText="__('master_data.exporting')" title="{{ __('common.export_pdf') }}" icon="pdf">
                    PDF
                </x-loading-button>
            @endcan
            @can('departments_create')
                <x-loading-button wire:click="create" target="create" variant="primary" size="md" :loadingText="__('common.loading')" class="flex-1 md:flex-none" icon="plus">
                    {{ __('master_data.add_department') }}
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <x-sortable-header field="name" :sortField="$sortField" :sortDir="$sortDir">{{ __('master_data.department') }}</x-sortable-header>
                        <x-sortable-header field="code" :sortField="$sortField" :sortDir="$sortDir">{{ __('master_data.code') }}</x-sortable-header>
                        <x-sortable-header field="users_count" :sortField="$sortField" :sortDir="$sortDir">{{ __('master_data.users_count') }}</x-sortable-header>
                        <x-sortable-header field="is_active" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.status') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($departments as $department)
                        <tr wire:key="row-dept-{{ $department->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-xs">
                                            {{ substr($department->code, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $department->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $department->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-200">
                                    {{ $department->users_count }} {{ __('master_data.user_unit') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @can('departments_update')
                                    <x-toggle-switch wire:click="toggleStatus({{ $department->id }})"
                                        :active="$department->is_active"
                                        target="toggleStatus({{ $department->id }})"
                                        wire:key="toggle-status-{{ $department->id }}"
                                        activeColor="green"
                                        :title="__('master_data.activate_deactivate')" />
                                @else
                                    @php
                                        $statusClass = $department->is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                        {{ $department->is_active ? __('common.active') : __('master_data.not_active') }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('departments_update')
                                        <x-loading-button wire:click="edit({{ $department->id }})"
                                            target="edit({{ $department->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $department->id }}"
                                            title="{{ __('common.edit') }}" />
                                    @endcan
                                    @can('departments_delete')
                                        @if($department->users_count === 0)
                                            <x-loading-button wire:click="confirmDelete({{ $department->id }})"
                                                target="confirmDelete({{ $department->id }})"
                                                variant="icon-red" icon="delete"
                                                wire:key="btn-delete-{{ $department->id }}"
                                                title="{{ __('common.delete') }}" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('master_data.no_departments') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $departments->links() }}
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
                                {{ $editMode ? __('master_data.edit_department') : __('master_data.add_department') }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="code" :value="__('master_data.department_code')" />
                                    <x-text-input id="code" wire:model="code" type="text" class="mt-1 block w-full" placeholder="{{ __('master_data.code_placeholder') }}" />
                                    <x-input-error for="code" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('master_data.department_name')" />
                                    <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="{{ __('master_data.name_placeholder') }}" />
                                    <x-input-error for="name" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="is_active" :value="__('common.status')" />
                                <div class="mt-2 flex items-center gap-3">
                                    <x-toggle-switch wire:click="toggleIsActive"
                                        :active="$is_active"
                                        target="toggleIsActive"
                                        activeColor="green"
                                        wire:key="toggle-is-active"
                                        :title="__('master_data.active_inactive')" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $is_active ? __('common.active') : __('master_data.not_active') }}
                                    </span>
                                </div>
                                <x-input-error for="is_active" class="mt-2" />
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                                :loadingText="__('master_data.saving')" class="w-full sm:w-auto">
                                {{ $editMode ? __('master_data.update') : __('common.save') }}
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
        :title="__('master_data.delete_department')"
        :message="__('master_data.delete_department_confirm')"
        :itemName="$deletingDepartmentName"
        confirmMethod="delete"
    />
</div>
