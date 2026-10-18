@props(['align' => 'right', 'variant' => 'default'])

@php
    $isFloating = $variant === 'floating';
@endphp

<div x-data="{ open: false }" class="relative {{ $isFloating ? '' : '' }}" @click.outside="open = false">
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
        :title="$store.locale.locales[$store.locale.current]?.label"
        @class([
            $isFloating
                ? 'p-3 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-200 dark:border-gray-700 inline-flex items-center justify-center'
                : 'inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 transition-colors'
        ])
    >
        <span class="font-semibold uppercase {{ $isFloating ? 'text-xs' : '' }}" x-text="$store.locale.locales[$store.locale.current]?.short"></span>
        @if($isFloating)
            <svg class="w-4 h-4 ml-1" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        @else
            <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-2 w-44 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 focus:outline-none z-50"
    >
        <div class="py-1">
            <template x-for="(locale, code) in $store.locale.locales" :key="code">
                <button
                    type="button"
                    @click="$store.locale.switch(code)"
                    class="flex items-center gap-3 w-full px-4 py-2 text-sm"
                    :class="code === $store.locale.current ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-200 font-medium' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'"
                >
                    <span class="font-semibold uppercase text-xs w-6" x-text="locale.short"></span>
                    <span x-text="locale.label"></span>
                    <svg x-show="code === $store.locale.current" class="w-4 h-4 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>
