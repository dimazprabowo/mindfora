<div
    @if($materials->contains(fn ($m) => $m->file_status->value === 'processing' || ($m->thumbnail_status && $m->thumbnail_status->value === 'processing')))
        wire:poll.10s
    @endif
>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('training.search_materials_short') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['typeFilter', 'statusFilter']">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('training.type') }}</label>
                    <x-searchable-select
                        wire:model.live="typeFilter"
                        :options="$this->typeOptions"
                        placeholder="{{ __('training.all_types') }}"
                        searchPlaceholder="{{ __('training.search_type') }}"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('common.status') }}</label>
                    <x-searchable-select
                        wire:model.live="statusFilter"
                        :options="$this->statusOptions"
                        placeholder="{{ __('training.all_statuses') }}"
                        searchPlaceholder="{{ __('training.search_status') }}"
                    />
                </div>
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @can('training_materials_create')
                <x-loading-button wire:click="create" target="create" variant="primary" size="md" loadingText="{{ __('common.loading') }}" class="flex-1 sm:flex-none" icon="plus">
                    {{ __('training.add_material') }}
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <x-sortable-header field="title" :sortField="$sortField" :sortDir="$sortDir">{{ __('training.material') }}</x-sortable-header>
                        <x-sortable-header field="type" :sortField="$sortField" :sortDir="$sortDir">{{ __('training.type') }}</x-sortable-header>
                        <x-sortable-header field="file_status" :sortField="$sortField" :sortDir="$sortDir">{{ __('training.file') }}</x-sortable-header>
                        <x-sortable-header field="is_published" :sortField="$sortField" :sortDir="$sortDir">{{ __('training.publish') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($materials as $material)
                        <tr wire:key="row-mat-{{ $material->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    {{-- Thumbnail mini --}}
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        @if($material->is_thumbnail_ready)
                                            <img src="{{ route('training.thumbnail', $material) }}" alt="{{ $material->title }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $material->title }}</div>
                                        @if($material->category)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $material->category }}</div>
                                        @endif
                                        @if($material->description)
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 line-clamp-1">{{ $material->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $material->type->badgeClass() }}">
                                    {{ $material->type->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $material->file_status->badgeClass() }}">
                                        {{ $material->file_status->label() }}
                                    </span>
                                    @if($material->file_size_formatted)
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $material->file_size_formatted }}</span>
                                    @endif
                                </div>
                                @if($material->file_status->value === 'failed' && $material->file_error)
                                    <div class="text-xs text-red-500 mt-1">{{ $material->file_error }}</div>
                                @endif
                                @if($material->file_status->value === 'processing')
                                    <div class="text-xs text-amber-500 mt-1 flex items-center gap-1">
                                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        {{ __('training.processing') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @can('training_materials_update')
                                    <x-toggle-switch wire:click="togglePublish({{ $material->id }})"
                                        :active="$material->is_published"
                                        target="togglePublish({{ $material->id }})"
                                        :disabled="!$material->is_ready"
                                        activeColor="green"
                                        wire:key="toggle-publish-{{ $material->id }}"
                                        title="Publish/Unpublish" />
                                @else
                                    @php
                                        $pubClass = $material->is_published
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $pubClass }}">
                                        {{ $material->is_published ? __('training.published') : __('training.draft') }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if($material->is_ready)
                                        <a href="{{ route('training.download', $material) }}"
                                           class="p-1.5 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                                           title="Download">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    @endif
                                    @can('training_materials_update')
                                        <x-loading-button wire:click="edit({{ $material->id }})"
                                            target="edit({{ $material->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $material->id }}"
                                            title="Edit" />
                                    @endcan
                                    @can('training_materials_delete')
                                        <x-loading-button wire:click="confirmDelete({{ $material->id }})"
                                            target="confirmDelete({{ $material->id }})"
                                            variant="icon-red" icon="delete"
                                            wire:key="btn-delete-{{ $material->id }}"
                                            title="Hapus" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('training.no_materials_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $materials->links() }}
        </div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="$wire.closeModal()"></div>

                <div class="inline-block align-bottom w-full bg-white dark:bg-gray-800 rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save" x-data="{ fileUploading: false, fileProgress: 0, thumbUploading: false, thumbProgress: 0 }"
                        x-on:livewire-upload-start="fileUploading = true"
                        x-on:livewire-upload-finish="fileUploading = false; fileProgress = 0"
                        x-on:livewire-upload-cancel="fileUploading = false"
                        x-on:livewire-upload-error="fileUploading = false"
                        x-on:livewire-upload-progress="fileProgress = $event.detail.progress">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[calc(100vh-12rem)] overflow-y-auto custom-scrollbar">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                {{ $editMode ? __('training.edit_material') : __('training.add_material') }}
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <x-input-label for="title" value="{{ __('training.material_title') }}" :required="true" />
                                    <x-text-input id="title" wire:model="title" type="text" class="mt-1 block w-full" placeholder="{{ __('training.material_title_placeholder') }}" />
                                    <x-input-error for="title" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="type" value="{{ __('training.type') }}" :required="true" />
                                    <div class="mt-1">
                                        <x-searchable-select
                                            wire:model.live="type"
                                            :options="$this->typeOptions"
                                            placeholder="{{ __('training.select_type') }}"
                                            searchPlaceholder="{{ __('training.search_type') }}"
                                        />
                                    </div>
                                    <x-input-error for="type" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="category" value="{{ __('training.category_optional') }}" />
                                    <x-text-input id="category" wire:model="category" type="text" class="mt-1 block w-full" placeholder="{{ __('training.category_placeholder') }}" />
                                    <x-input-error for="category" class="mt-2" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="description" value="{{ __('common.description') }}" />
                                    <textarea id="description" wire:model="description" rows="3"
                                        class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-3 py-2 text-sm"
                                        placeholder="{{ __('training.description_placeholder') }}"></textarea>
                                    <x-input-error for="description" class="mt-2" />
                                </div>

                                {{-- Main file upload --}}
                                <div class="sm:col-span-2">
                                    <x-input-label for="file" value="{{ $editMode ? __('training.replace_file_optional') : __('training.material_file') }}" :required="!$editMode" />

                                    @if($file && is_object($file))
                                        {{-- File selected preview --}}
                                        <div class="mt-2 flex items-center justify-between bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg px-3 py-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $file->getClientOriginalName() }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($file->getSize() / 1024, 0) }} KB</div>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removeFile"
                                                class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors flex-shrink-0"
                                                title="{{ __('common.remove') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Upload area with progress --}}
                                        <div class="mt-1">
                                            <label for="file" class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                <div x-show="!fileUploading" class="flex flex-col items-center gap-1">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('training.click_to_upload') }}</span>
                                                </div>
                                                <div x-show="fileUploading" x-cloak class="flex flex-col items-center gap-2 w-full px-4">
                                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-primary-600 h-full transition-all duration-300" :style="'width: ' + fileProgress + '%'"></div>
                                                    </div>
                                                    <span class="text-xs text-primary-600 dark:text-primary-400" x-text="fileProgress + '%'"></span>
                                                </div>
                                                <input id="file" type="file" wire:model="file" class="hidden" accept=".{{ implode(',.', get_allowed_mimes_array('training-materials')) }}">
                                            </label>
                                        </div>
                                    @endif

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ get_upload_config_display('training-materials') }}
                                    </p>
                                    <x-input-error for="file" class="mt-2" />

                                    {{-- Existing file info on edit --}}
                                    @if($editMode && $existingFile)
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-md px-3 py-2">
                                            <span class="font-medium">{{ __('training.current_file') }}:</span>
                                            {{ $existingFile['name'] }}
                                            @if($existingFile['size'])
                                                ({{ $existingFile['size'] }})
                                            @endif
                                            @if($existingFile['status'] === 'processing')
                                                — <span class="text-amber-500">{{ __('training.still_processing') }}</span>
                                            @elseif($existingFile['status'] === 'failed')
                                                — <span class="text-red-500">{{ __('training.upload_failed') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Thumbnail upload (optional) --}}
                                <div class="sm:col-span-2">
                                    <x-input-label for="thumbnail" value="{{ __('training.thumbnail_optional') }}" />

                                    @if($thumbnail && is_object($thumbnail))
                                        {{-- Thumbnail selected preview --}}
                                        <div class="mt-2 flex items-center justify-between bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg px-3 py-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <img src="{{ $thumbnail->temporaryUrl() }}" alt="thumbnail preview" class="w-10 h-10 rounded object-cover flex-shrink-0">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $thumbnail->getClientOriginalName() }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($thumbnail->getSize() / 1024, 0) }} KB</div>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removeThumbnail"
                                                class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors flex-shrink-0"
                                                title="{{ __('common.remove') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Upload area --}}
                                        <div class="mt-1">
                                            <label for="thumbnail" class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                <div class="flex flex-col items-center gap-1">
                                                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('training.click_to_upload_thumbnail') }}</span>
                                                </div>
                                                <input id="thumbnail" type="file" wire:model="thumbnail" class="hidden" accept=".{{ implode(',.', get_allowed_mimes_array('training-thumbnail')) }}">
                                            </label>
                                        </div>
                                    @endif

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ get_upload_config_display('training-thumbnail') }}
                                    </p>
                                    <x-input-error for="thumbnail" class="mt-2" />

                                    {{-- Existing thumbnail info on edit --}}
                                    @if($editMode && $existingThumbnail)
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-md px-3 py-2">
                                            <span class="font-medium">{{ __('training.current_thumbnail') }}:</span>
                                            {{ $existingThumbnail['name'] }}
                                            @if($existingThumbnail['status'] === 'processing')
                                                — <span class="text-amber-500">{{ __('training.still_processing') }}</span>
                                            @elseif($existingThumbnail['status'] === 'failed')
                                                — <span class="text-red-500">{{ __('training.upload_failed') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="flex items-center gap-2">
                                        <x-toggle-switch wire:click="toggleIsPublished"
                                            :active="$is_published"
                                            target="toggleIsPublished"
                                            activeColor="green"
                                            wire:key="toggle-published"
                                            title="Publish" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('training.publish_show_participants') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                                loadingText="{{ __('my_test.saving') }}" class="w-full sm:w-auto">
                                {{ $editMode ? __('training.update') : __('common.save') }}
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
        title="{{ __('training.delete_material') }}"
        message="{{ __('training.delete_material_confirm') }}"
        :itemName="$deletingMaterialName"
        confirmMethod="delete"
    />
</div>
