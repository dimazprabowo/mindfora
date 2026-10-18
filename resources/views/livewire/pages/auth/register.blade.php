<x-auth-shell>
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ __('auth.register_title') }}</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm sm:text-base">{{ __('auth.register_subtitle') }}</p>
    </div>

    <form wire:submit="register" class="space-y-6">
        {{-- Section: Account Data --}}
        <div class="space-y-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                {{ __('auth.section_account') }}
            </h3>

            {{-- Name --}}
            <div>
                <x-input-label for="name" value="{{ __('auth.full_name') }}" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="{{ __('auth.full_name_placeholder') }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            {{-- Email Address --}}
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" placeholder="{{ __('auth.email_placeholder') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="{{ __('auth.password_min_placeholder') }}" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-input-label for="password_confirmation" value="{{ __('auth.confirm_password') }}" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('auth.confirm_password_placeholder') }}" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        {{-- Section: Profile Data (optional) --}}
        <div class="space-y-5">
            <div class="flex items-baseline justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ __('auth.section_profile') }}
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('auth.optional_hint') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Company --}}
                <div>
                    <x-input-label for="company_id" value="{{ __('auth.company') }}" />
                    <x-searchable-select
                        wire:model.live="company_id"
                        :options="$companies->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()"
                        :placeholder="__('auth.company_placeholder')"
                        :searchPlaceholder="__('auth.search_company')"
                        id="company_id"
                        :error="$errors->has('company_id')"
                    />
                    <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
                </div>

                {{-- Phone --}}
                <div>
                    <x-input-label for="phone" value="{{ __('auth.phone') }}" />
                    <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="tel" name="phone" autocomplete="tel" placeholder="{{ __('auth.phone_placeholder') }}" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                {{-- Position --}}
                <div>
                    <x-input-label for="position" value="{{ __('auth.position') }}" />
                    <x-text-input wire:model="position" id="position" class="block mt-1 w-full" type="text" name="position" autocomplete="organization-title" placeholder="{{ __('auth.position_placeholder') }}" />
                    <x-input-error :messages="$errors->get('position')" class="mt-2" />
                </div>

                {{-- Gender --}}
                <div>
                    <x-input-label for="gender" value="{{ __('auth.gender') }}" />
                    <x-searchable-select
                        wire:model.live="gender"
                        :options="collect($genderOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
                        :placeholder="__('auth.gender_placeholder')"
                        :searchPlaceholder="__('auth.search_gender')"
                        id="gender"
                        :error="$errors->has('gender')"
                    />
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>

                {{-- Birth Date --}}
                <div>
                    <x-input-label for="birth_date" value="{{ __('auth.birth_date') }}" />
                    <x-text-input wire:model="birth_date" id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" autocomplete="bday" />
                    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                </div>

                {{-- Education Level --}}
                <div>
                    <x-input-label for="education_level" value="{{ __('auth.education_level') }}" />
                    <x-searchable-select
                        wire:model.live="education_level"
                        :options="collect($educationOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
                        :placeholder="__('auth.education_placeholder')"
                        :searchPlaceholder="__('auth.search_education')"
                        id="education_level"
                        :error="$errors->has('education_level')"
                    />
                    <x-input-error :messages="$errors->get('education_level')" class="mt-2" />
                </div>

                {{-- Department --}}
                <div>
                    <x-input-label for="department_id" value="{{ __('auth.department') }}" />
                    <x-searchable-select
                        wire:model.live="department_id"
                        :options="$departments->map(fn($d) => ['value' => $d->id, 'label' => $d->name])->toArray()"
                        :placeholder="__('auth.department_placeholder')"
                        :searchPlaceholder="__('auth.search_department')"
                        id="department_id"
                        :error="$errors->has('department_id')"
                    />
                    <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                </div>

                {{-- Work Started At --}}
                <div class="sm:col-span-2">
                    <x-input-label for="work_started_at" value="{{ __('auth.work_started_at') }}" />
                    <x-text-input wire:model="work_started_at" id="work_started_at" class="block mt-1 w-full" type="date" name="work_started_at" />
                    <x-input-error :messages="$errors->get('work_started_at')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('auth.work_started_at_hint') }}</p>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full inline-flex items-center justify-center px-4 py-3 bg-primary-600 border border-transparent rounded-lg font-semibold text-base text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            wire:loading.attr="disabled"
            wire:target="register">

            <span class="inline-flex items-center justify-center gap-2">
                {{-- Loading Icon --}}
                <svg wire:loading wire:target="register"
                    class="animate-spin h-5 w-5 text-white"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                {{-- Normal Text --}}
                <span wire:loading.remove wire:target="register">
                    {{ __('auth.register_button') }}
                </span>

                {{-- Loading Text --}}
                <span wire:loading wire:target="register">
                    {{ __('common.processing') }}
                </span>
            </span>
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium transition-colors">
                {{ __('auth.have_account_login') }}
            </a>
        </div>
    </form>
</x-auth-shell>
