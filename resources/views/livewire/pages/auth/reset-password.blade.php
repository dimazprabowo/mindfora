<x-auth-shell>
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ __('auth.reset_password_title') }}</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm sm:text-base">{{ __('auth.reset_password_subtitle') }}</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-6">
        <!-- Email Address (Read-only) -->
        <div>
            <x-input-label for="email" value="Email" :required="true" />
            <input
                wire:model="email"
                id="email"
                type="email"
                readonly
                class="block mt-1 w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed opacity-70 focus:outline-none"
            />
            @error('email')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="{{ __('auth.new_password') }}" :required="true" />
            <div class="relative mt-1">
                <input
                    wire:model="password"
                    id="password"
                    type="{{ $showPassword ? 'text' : 'password' }}"
                    required
                    autocomplete="new-password"
                    class="block w-full px-4 py-2.5 pr-11 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm transition"
                    placeholder="{{ __('auth.password_min_placeholder') }}"
                />
                <button
                    type="button"
                    wire:click="togglePasswordVisibility"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    @if($showPassword)
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    @endif
                </button>
            </div>
            <div class="mt-2 space-y-1">
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('auth.password_requirements') }}</p>
                <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5 ml-4">
                    <li class="flex items-center gap-1">
                        <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('auth.password_req_min_length') }}
                    </li>
                    <li class="flex items-center gap-1">
                        <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('auth.password_req_upper_lower') }}
                    </li>
                    <li class="flex items-center gap-1">
                        <svg class="h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ __('auth.password_req_numbers_symbols') }}
                    </li>
                </ul>
            </div>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="{{ __('auth.confirm_new_password') }}" :required="true" />
            <div class="relative mt-1">
                <input
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    type="{{ $showPasswordConfirmation ? 'text' : 'password' }}"
                    required
                    autocomplete="new-password"
                    class="block w-full px-4 py-2.5 pr-11 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-primary-500/40 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm transition"
                    placeholder="{{ __('auth.confirm_new_password_placeholder') }}"
                />
                <button
                    type="button"
                    wire:click="togglePasswordConfirmationVisibility"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    @if($showPasswordConfirmation)
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    @endif
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="resetPassword"
            class="w-full inline-flex items-center justify-center px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200 gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <svg wire:loading wire:target="resetPassword" class="animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span wire:loading.class="hidden" wire:target="resetPassword">{{ __('auth.reset_password_button') }}</span>
            <span wire:loading wire:target="resetPassword">{{ __('common.processing') }}</span>
        </button>
    </form>

    <!-- Back to Login -->
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" wire:navigate class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 font-medium transition-colors inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ __('auth.back_to_login') }}
        </a>
    </div>
</x-auth-shell>
