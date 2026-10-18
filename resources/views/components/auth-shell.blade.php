@props(['title' => null])

@php
    // Auth Shell - Shared layout for all guest/authentication pages.
    // Reads panel position from the configuration layer (auth_layout_position()),
    // NEVER directly from env. Supports: left | center | right.
    // Mobile (<lg) always stacks vertically. Invalid values fall back to "center".
    $position = auth_layout_position();
    $showBranding = $position !== 'center';
    // Desktop row direction: default flex-row (left), reverse for right
    $rowDirection = $position === 'right' ? 'lg:flex-row-reverse' : 'lg:flex-row';
@endphp

<div class="min-h-screen flex flex-col {{ $rowDirection }} relative">
    {{-- Floating Controls (top-right): Home + Language + Dark Mode --}}
    <div class="fixed top-4 right-4 z-50 flex items-center gap-2">
        <a href="{{ route('welcome') }}"
           wire:navigate
           x-data="{ loading: false }"
           x-on:click="$nextTick(() => loading = true)"
           x-on:livewire:navigate:end.window="loading = false"
           :class="{ 'opacity-75 cursor-wait': loading }"
           class="p-3 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 dark:border-gray-700 inline-flex items-center justify-center"
           aria-label="{{ __('auth.back_to_home') }}"
           title="{{ __('auth.back_to_home') }}">
            <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5M5 9.5v10a1 1 0 001 1h3.5v-6h5v6H18a1 1 0 001-1v-10" />
            </svg>
            <svg x-cloak x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </a>
        <x-language-switcher variant="floating" />
        <button @click="$store.darkMode.toggle()"
                class="p-3 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 dark:border-gray-700"
                aria-label="{{ __('auth.toggle_dark_mode') }}"
                title="{{ __('auth.toggle_dark_mode') }}">
            <svg x-show="!$store.darkMode.dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="$store.darkMode.dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

    {{-- Branding Panel (desktop only, hidden when position=center) --}}
    @if($showBranding)
        <x-auth-branding-panel />
    @endif

    {{-- Form Panel --}}
    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gray-50 dark:bg-gray-900 min-h-screen lg:min-h-0 relative overflow-hidden">
        {{-- Mobile Background (visible only on mobile, soft calming gradient) --}}
        <div class="lg:hidden absolute inset-0 z-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900"></div>

        <div class="w-full max-w-md relative z-10">
            {{-- Mobile Logo (compact branding header for mobile) --}}
            <x-auth-mobile-logo />

            {{-- Auth Card (consistent styling across all pages) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-200 dark:border-gray-700">
                {{ $slot }}
            </div>

            {{-- Footer Help Link (consistent across all pages) --}}
            {{-- Mobile: white/primary-100 on blue gradient bg; Desktop: gray on gray-50/900 bg --}}
            <p class="text-center text-sm text-white lg:text-gray-600 dark:text-primary-100 dark:lg:text-gray-400 mt-6">
                {{ __('auth.need_help') }} <a href="#" class="text-primary-100 lg:text-primary-600 dark:lg:text-primary-400 hover:text-white dark:hover:text-white dark:lg:hover:text-primary-300 lg:hover:text-primary-700 font-medium transition-colors">{{ __('auth.contact_support') }}</a>
            </p>

            {{-- Mobile Footer Copyright (only mobile, sits on blue gradient bg) --}}
            <p class="lg:hidden text-center text-primary-200 text-xs mt-8">
                &copy; {{ date('Y') }} {{ app_name() }}. {{ __('auth.all_rights_reserved') }}
            </p>
        </div>
    </div>
</div>
