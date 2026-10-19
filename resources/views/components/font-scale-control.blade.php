@php
    // Single source of the available text sizes (root font-size %). Labels from lang common.font_size_levels.
    $levels = __('common.font_size_levels');
@endphp

{{-- Floating text-size control (bottom-right). Scales the whole UI via the root font-size (Alpine store fontScale). --}}
<div x-data="{
        open: false,
        levels: @js(array_keys($levels)),
        labels: @js($levels),
        get index() { return Math.max(0, this.levels.indexOf($store.fontScale.percent)); },
        step(delta) {
            const next = Math.min(this.levels.length - 1, Math.max(0, this.index + delta));
            $store.fontScale.set(this.levels[next]);
        },
     }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     class="font-scale-fab bottom-float fixed right-4 z-40 flex flex-col items-end gap-3 transition-transform duration-300 print:hidden">

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         id="font-scale-panel"
         role="dialog"
         aria-labelledby="font-scale-title"
         class="w-72 max-w-[calc(100vw-2rem)] origin-bottom-right rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-4">
        <p id="font-scale-title" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('common.font_size') }}</p>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('common.font_size_hint') }}</p>

        <div class="mt-4 flex items-center gap-3">
            <button type="button"
                    @click="step(-1)"
                    :disabled="index === 0"
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    aria-label="{{ __('common.font_size_decrease') }}"
                    title="{{ __('common.font_size_decrease') }}">
                <span class="text-sm font-bold" aria-hidden="true">A&minus;</span>
            </button>

            <div class="min-w-0 flex-1 text-center" aria-live="polite">
                <p class="truncate text-base font-semibold text-gray-900 dark:text-white" x-text="labels[levels[index]]"></p>
                <div class="mt-2 flex justify-center gap-1.5" aria-hidden="true">
                    <template x-for="(level, i) in levels" :key="level">
                        <span class="h-1.5 w-6 rounded-full transition-colors"
                              :class="i <= index ? 'bg-primary-600 dark:bg-primary-400' : 'bg-gray-200 dark:bg-gray-700'"></span>
                    </template>
                </div>
            </div>

            <button type="button"
                    @click="step(1)"
                    :disabled="index === levels.length - 1"
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    aria-label="{{ __('common.font_size_increase') }}"
                    title="{{ __('common.font_size_increase') }}">
                <span class="text-lg font-bold" aria-hidden="true">A+</span>
            </button>
        </div>

        <button type="button"
                x-show="index > 0"
                x-cloak
                @click="$store.fontScale.set(levels[0])"
                class="mt-4 w-full rounded-lg py-2 text-sm font-medium text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
            {{ __('common.font_size_reset') }}
        </button>
    </div>

    <button type="button"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="font-scale-panel"
            data-haptic
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg shadow-primary-900/20 ring-4 ring-white/80 dark:ring-gray-900/80 hover:bg-primary-700 active:scale-95 transition-all focus:outline-none focus:ring-primary-300"
            aria-label="{{ __('common.font_size') }}"
            title="{{ __('common.font_size') }}">
        <span class="font-bold leading-none" aria-hidden="true"><span class="text-xl">A</span><span class="text-sm">a</span></span>
        <span x-show="index > 0"
              x-cloak
              x-text="levels[index] + '%'"
              class="absolute -top-1 -right-1 rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-950 shadow"></span>
    </button>
</div>
