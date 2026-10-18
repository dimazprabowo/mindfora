@props(['editMode' => false, 'showStatus' => false])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Code --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('profile.company_code') }} <span class="text-red-500">*</span>
        </label>
        <x-text-input wire:model="code" type="text" class="w-full"
            placeholder="{{ __('profile.company_code_placeholder') }}" />
        @error('code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('profile.company_name') }} <span class="text-red-500">*</span>
        </label>
        <x-text-input wire:model="name" type="text" class="w-full"
            placeholder="{{ __('profile.company_name_placeholder') }}" />
        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- NPWP --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            NPWP <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            x-data="{
                npwp: @entangle('npwp'),
                formatNpwp(value) {
                    let digits = value.replace(/\D/g, '').substring(0, 16);
                    let len = digits.length;
                    
                    if (len <= 15) {
                        // Format lama: XX.XXX.XXX.X-XXX.XXX
                        let formatted = '';
                        if (len > 0) formatted += digits.substring(0, Math.min(2, len));
                        if (len > 2) formatted += '.' + digits.substring(2, Math.min(5, len));
                        if (len > 5) formatted += '.' + digits.substring(5, Math.min(8, len));
                        if (len > 8) formatted += '.' + digits.substring(8, Math.min(9, len));
                        if (len > 9) formatted += '-' + digits.substring(9, Math.min(12, len));
                        if (len > 12) formatted += '.' + digits.substring(12, 15);
                        return formatted;
                    } else {
                        // Format baru: XXXX.XXXX.XXXX.XXXX
                        let formatted = '';
                        if (len > 0) formatted += digits.substring(0, Math.min(4, len));
                        if (len > 4) formatted += '.' + digits.substring(4, Math.min(8, len));
                        if (len > 8) formatted += '.' + digits.substring(8, Math.min(12, len));
                        if (len > 12) formatted += '.' + digits.substring(12, 16);
                        return formatted;
                    }
                },
                displayValue() {
                    return this.npwp ? this.formatNpwp(this.npwp) : '';
                }
            }"
            x-init="$watch('npwp', value => { if(value) npwp = value.replace(/\D/g, '').substring(0, 16); })"
            :value="displayValue()"
            @input="npwp = $event.target.value.replace(/\D/g, '').substring(0, 16); $event.target.value = formatNpwp($event.target.value)"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 px-3 py-2 font-mono"
            placeholder="XX.XXX.XXX.X-XXX.XXX">
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('profile.npwp_format_hint') }}</p>
        @error('npwp') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

    {{-- Email --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
        <x-text-input wire:model="email" type="email" class="w-full"
            placeholder="{{ __('profile.company_email_placeholder') }}" />
        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Phone --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('common.phone') }}</label>
        <x-text-input wire:model="phone" type="text" class="w-full"
            placeholder="021-1234567" />
        @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Address --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('profile.address') }}</label>
        <textarea wire:model="address" rows="2"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-primary-500 px-3 py-2"
            placeholder="{{ __('profile.address_placeholder') }}"></textarea>
        @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Divider --}}
    <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('profile.person_in_charge') }}</h4>
    </div>

    {{-- PIC Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('profile.pic_name') }}</label>
        <x-text-input wire:model="pic_name" type="text" class="w-full"
            placeholder="{{ __('profile.pic_name_placeholder') }}" />
        @error('pic_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- PIC Email --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('profile.pic_email') }}</label>
        <x-text-input wire:model="pic_email" type="email" class="w-full"
            placeholder="{{ __('profile.pic_email_placeholder') }}" />
        @error('pic_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- PIC Phone --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('profile.pic_phone') }}</label>
        <x-text-input wire:model="pic_phone" type="text" class="w-full"
            placeholder="08123456789" />
        @error('pic_phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>

    {{-- Status (only for admin) --}}
    @if($showStatus)
    <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ __('common.status') }} <span class="text-red-500">*</span>
        </label>
        @php
            $statusOptions = collect(\App\Enums\CompanyStatus::cases())
                ->map(fn ($case) => ['value' => $case->value, 'label' => $case->label()])
                ->toArray();
        @endphp
        <x-searchable-select
            wire:model="status"
            :options="$statusOptions"
            :placeholder="__('common.select_status')"
            :searchPlaceholder="__('common.search_status')"
            :error="$errors->has('status')"
        />
        @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    </div>
    @endif
</div>
