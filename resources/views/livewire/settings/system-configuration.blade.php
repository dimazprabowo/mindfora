<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full md:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('settings.search_config') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['isActiveFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('common.status') }}</label>
                <x-searchable-select
                    wire:model.live="isActiveFilter"
                    :options="$this->isActiveOptions"
                    :placeholder="__('settings.all_status')"
                    :searchPlaceholder="__('settings.search_status')"
                />
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            @can('configuration_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" variant="success" size="md" :loadingText="__('settings.exporting')" title="{{ __('common.export_excel') }}">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                    Excel
                </x-loading-button>
            @endcan
            @can('configuration_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" variant="danger" size="md" :loadingText="__('settings.exporting')" title="{{ __('common.export_pdf') }}">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    PDF
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <x-sortable-header field="key" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.key') }}</x-sortable-header>
                        <x-sortable-header field="category" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.category') }}</x-sortable-header>
                        <x-sortable-header field="value" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.value') }}</x-sortable-header>
                        <x-sortable-header field="data_type" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.type') }}</x-sortable-header>
                        <x-sortable-header field="is_active" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.status') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($configurations as $config)
                        <tr wire:key="config-{{ $config->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $config->key }}</div>
                                @if($config->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $config->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($config->category->value === 'threshold') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200
                                    @elseif($config->category->value === 'sla') bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200
                                    @elseif($config->category->value === 'notification') bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200
                                    @endif">
                                    {{ $config->category->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate">{{ $config->value }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $config->data_type->label() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @can('configuration_update')
                                    <x-toggle-switch wire:click="toggleActive({{ $config->id }})"
                                        :active="$config->is_active"
                                        target="toggleActive({{ $config->id }})"
                                        wire:key="toggle-active-{{ $config->id }}"
                                        :title="__('settings.activate_deactivate')" />
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $config->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' }}">
                                        {{ $config->is_active ? __('common.active') : __('common.inactive') }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('configuration_update')
                                        <x-loading-button wire:click="edit({{ $config->id }})"
                                            target="edit({{ $config->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $config->id }}"
                                            title="{{ __('common.edit') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.no_configurations') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $configurations->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeModal()"></div>

                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ __('settings.edit_configuration') }}
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.key') }} <span class="text-red-500">*</span></label>
                                    <input wire:model="key" type="text" {{ $editMode ? 'readonly' : '' }}
                                        placeholder="{{ __('settings.key_placeholder') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white {{ $editMode ? 'bg-gray-100 dark:bg-gray-900' : '' }}">
                                    @error('key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.category') }} <span class="text-red-500">*</span></label>
                                    <x-searchable-select
                                        wire:model.live="category"
                                        :options="collect($categories)->map(fn($label, $key) => ['value' => $key, 'label' => $label])->values()->toArray()"
                                        :placeholder="__('settings.select_category')"
                                        :searchPlaceholder="__('settings.search_category')"
                                        :error="$errors->has('category')"
                                    />
                                    @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.value') }} @if($data_type !== 'datetime')<span class="text-red-500">*</span>@endif</label>
                                    @if($data_type === 'datetime')
                                        <input wire:model="value" type="datetime-local"
                                            placeholder="{{ __('settings.select_datetime') }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('settings.datetime_hint') }}</p>
                                    @elseif($data_type === 'boolean')
                                        <x-searchable-select
                                            wire:model="value"
                                            :options="[['value' => '1', 'label' => __('settings.yes_true')], ['value' => '0', 'label' => __('settings.no_false')]]"
                                            :placeholder="__('settings.select_value_boolean')"
                                            :searchPlaceholder="__('settings.search_value_boolean')"
                                            :error="$errors->has('value')"
                                        />
                                    @elseif($data_type === 'integer')
                                        <input wire:model="value" type="number"
                                            placeholder="{{ __('settings.enter_numeric') }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @else
                                        <input wire:model="value" type="text"
                                            placeholder="{{ __('settings.enter_value') }}"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @endif
                                    @error('value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.data_type') }} <span class="text-red-500">*</span></label>
                                    <x-searchable-select
                                        wire:model.live="data_type"
                                        :options="collect($dataTypes)->map(fn($label, $key) => ['value' => $key, 'label' => $label])->values()->toArray()"
                                        :placeholder="__('settings.select_data_type')"
                                        :searchPlaceholder="__('settings.search_data_type')"
                                        :error="$errors->has('data_type')"
                                    />
                                    @error('data_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.description') }}</label>
                                    <textarea wire:model="description" rows="3"
                                        placeholder="{{ __('settings.description_placeholder') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center gap-4">
                                    <label class="flex items-center">
                                        <input wire:model="is_editable" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('settings.editable') }}</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('common.active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                                :loadingText="__('settings.saving')" class="w-full sm:w-auto">
                                {{ __('settings.update') }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeModal" target="closeModal"
                                class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
