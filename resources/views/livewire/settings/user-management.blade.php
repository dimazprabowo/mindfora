<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full md:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('settings.search_user') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['isActiveFilter', 'roleFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('settings.active_status') }}</label>
                <x-searchable-select
                    wire:model.live="isActiveFilter"
                    :options="$this->isActiveOptions"
                    :placeholder="__('settings.all_status')"
                    :searchPlaceholder="__('settings.search_status')"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('settings.role') }}</label>
                <x-searchable-select
                    wire:model.live="roleFilter"
                    :options="$this->roleOptions"
                    :placeholder="__('settings.all_roles')"
                    :searchPlaceholder="__('settings.search_role')"
                />
            </div>
        </x-filter-popover>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            @can('users_export_excel')
                <x-loading-button wire:click="exportExcel" target="exportExcel" variant="success" size="md" :loadingText="__('settings.exporting')" title="{{ __('common.export_excel') }}">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </x-slot:icon>
                    Excel
                </x-loading-button>
            @endcan
            @can('users_export_pdf')
                <x-loading-button wire:click="exportPdf" target="exportPdf" variant="danger" size="md" :loadingText="__('settings.exporting')" title="{{ __('common.export_pdf') }}">
                    <x-slot:icon>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </x-slot:icon>
                    PDF
                </x-loading-button>
            @endcan
            @can('users_create')
                <x-loading-button wire:click="create" target="create" variant="primary" size="md" :loadingText="__('common.loading')" class="flex-1 md:flex-none">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </x-slot:icon>
                    {{ __('settings.add_user') }}
                </x-loading-button>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <x-sortable-header field="name" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.user') }}</x-sortable-header>
                        <x-sortable-header field="email" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.email') }}</x-sortable-header>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('settings.role') }}</th>
                        <x-sortable-header field="company" :sortField="$sortField" :sortDir="$sortDir">{{ __('settings.company') }}</x-sortable-header>
                        <x-sortable-header field="is_active" :sortField="$sortField" :sortDir="$sortDir">{{ __('common.status') }}</x-sortable-header>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        @if($user->position)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->position }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $user->email }}</div>
                                @if($user->phone)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $roleColors = [
                                        'super admin' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
                                        'admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
                                        'manager' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200',
                                        'sbu' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                                        'kacab' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-200',
                                        'inspector' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
                                        'user' => 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200',
                                    ];
                                @endphp
                                @if($user->roles->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            @php $colorClass = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200'; @endphp
                                            <span wire:key="user-role-{{ $user->id }}-{{ $role->id }}" class="inline-flex items-center whitespace-nowrap px-2 py-1 text-xs font-medium rounded-full {{ $colorClass }}">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200">{{ __('settings.no_role') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $user->company->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @can('users_update')
                                    <x-toggle-switch wire:click="toggleActive({{ $user->id }})"
                                        :active="$user->is_active"
                                        target="toggleActive({{ $user->id }})"
                                        :disabled="$user->id == auth()->id()"
                                        wire:key="toggle-active-{{ $user->id }}"
                                        title="{{ $user->id == auth()->id() ? __('settings.cannot_deactivate_self') : __('settings.activate_deactivate') }}" />
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap px-2 py-1 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' }}">
                                        {{ $user->is_active ? __('common.active') : __('common.inactive') }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @can('users_update')
                                        <x-loading-button wire:click="edit({{ $user->id }})"
                                            target="edit({{ $user->id }})"
                                            variant="icon-blue" icon="edit"
                                            wire:key="btn-edit-{{ $user->id }}"
                                            title="{{ __('common.edit') }}" />
                                        <x-loading-button wire:click="openResetPasswordModal({{ $user->id }})"
                                            target="openResetPasswordModal({{ $user->id }})"
                                            variant="icon-amber" icon="reset"
                                            wire:key="btn-reset-{{ $user->id }}"
                                            title="{{ __('settings.reset_password') }}" />
                                    @endcan
                                    @can('users_delete')
                                        @if($user->id != auth()->id())
                                            <x-loading-button wire:click="confirmDelete({{ $user->id }})"
                                                target="confirmDelete({{ $user->id }})"
                                                variant="icon-red" icon="delete"
                                                wire:key="btn-delete-{{ $user->id }}"
                                                title="{{ __('common.delete') }}" />
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.no_users') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->links() }}
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
                                {{ $editMode ? __('settings.edit_user') : __('settings.add_user') }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.full_name') }} <span class="text-red-500">*</span></label>
                                    <input wire:model="name" type="text"
                                        placeholder="{{ __('settings.enter_full_name') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.email') }} <span class="text-red-500">*</span></label>
                                    <input wire:model="email" type="email"
                                        placeholder="nama@email.com"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div x-data="{ showPassword: false }">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('settings.password') }} @if(!$editMode)<span class="text-red-500">*</span>@endif
                                        <br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $editMode ? __('settings.password_leave_blank') : '' }}
                                        </span>
                                    </label>
                                    
                                    <div class="relative">
                                        <input wire:model="password" :type="showPassword ? 'text' : 'password'"
                                            placeholder="{{ $editMode ? __('settings.password_leave_blank') : __('settings.password_placeholder_create') }}"
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <button type="button"
                                                @click="showPassword = !showPassword"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div x-data="{ showPasswordConfirmation: false }">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ __('settings.confirm_password') }} @if(!$editMode)<span class="text-red-500">*</span>@endif
                                        <br>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $editMode ? __('settings.password_leave_blank') : '' }}
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <input wire:model="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'"
                                            placeholder="{{ $editMode ? __('settings.password_leave_blank') : __('settings.password_confirm_placeholder_create') }}"
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                        <button type="button"
                                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <svg x-show="!showPasswordConfirmation" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showPasswordConfirmation" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.role') }} <span class="text-red-500">*</span></label>
                                    <x-multi-searchable-select
                                        wire:model="selectedRoles"
                                        :options="$roles->map(fn($r) => ['value' => $r->name, 'label' => ucfirst($r->name)])->toArray()"
                                        :placeholder="__('settings.select_role')"
                                        :searchPlaceholder="__('settings.search_role')"
                                        :error="$errors->has('selectedRoles')"
                                    />
                                    @error('selectedRoles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @error('selectedRoles.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.company_optional') }}</label>
                                    <x-searchable-select
                                        wire:model.live="company_id"
                                        :options="$companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()"
                                        :placeholder="__('settings.none')"
                                        :searchPlaceholder="__('settings.search_company')"
                                        :error="$errors->has('company_id')"
                                    />
                                    @error('company_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.phone') }}</label>
                                    <input wire:model="phone" type="text"
                                        placeholder="08123456789"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.position') }}</label>
                                    <input wire:model="position" type="text"
                                        placeholder="{{ __('settings.position_placeholder') }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                                    @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('settings.user_active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <x-loading-button type="submit" target="save" variant="primary" size="lg"
                                :loadingText="__('settings.saving')" class="w-full sm:w-auto">
                                {{ $editMode ? __('settings.update') : __('common.save') }}
                            </x-loading-button>
                            <x-cancel-button wire:click="closeModal" target="closeModal"
                                class="mt-3 sm:mt-0 w-full sm:w-auto" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Reset Password Modal -->
    <x-reset-password-modal :show="$showResetPasswordModal" />

    <!-- Delete Confirmation Modal -->
    <x-delete-modal 
        :show="$showDeleteModal"
        wire:model="showDeleteModal"
        :title="__('settings.delete_user')"
        :message="__('settings.delete_user_confirm')"
        :itemName="$deletingUserName"
        confirmMethod="delete"
    />
</div>
