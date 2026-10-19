<nav class="lg:hidden fixed inset-x-0 bottom-0 z-40 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border-t border-gray-200 dark:border-gray-700 shadow-[0_-4px_16px_-8px_rgba(0,0,0,0.12)] pb-safe select-none"
     aria-label="{{ __('menu.main_navigation') }}">
    <div class="flex h-16 items-stretch px-1">
        @foreach ($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
               wire:navigate
               wire:key="tab-{{ $tab['route'] }}"
               data-haptic
               @if ($tab['active']) aria-current="page" @endif
               class="group flex flex-1 min-w-0 flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-xl
                      {{ $tab['active'] ? 'text-primary-700 dark:text-primary-300' : 'text-gray-500 dark:text-gray-400 active:text-gray-900 dark:active:text-white' }}">
                <span class="flex h-8 w-14 items-center justify-center rounded-full transition-all duration-200
                             {{ $tab['active'] ? 'bg-primary-100 dark:bg-primary-900/50' : 'group-active:bg-gray-100 dark:group-active:bg-gray-700' }}">
                    <x-icon :name="$tab['icon']" class="w-6 h-6" />
                </span>
                <span class="max-w-full truncate px-1">{{ $tab['name'] }}</span>
            </a>
        @endforeach

        <button type="button"
                @click="Alpine.store('sidebar').toggle()"
                data-haptic
                :aria-expanded="Alpine.store('sidebar').open"
                class="group flex flex-1 min-w-0 flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded-xl"
                :class="(Alpine.store('sidebar').open || @js($moreActive)) ? 'text-primary-700 dark:text-primary-300' : 'text-gray-500 dark:text-gray-400 active:text-gray-900 dark:active:text-white'">
            <span class="flex h-8 w-14 items-center justify-center rounded-full transition-all duration-200"
                  :class="(Alpine.store('sidebar').open || @js($moreActive)) ? 'bg-primary-100 dark:bg-primary-900/50' : 'group-active:bg-gray-100 dark:group-active:bg-gray-700'">
                <x-icon name="squares" class="w-6 h-6" />
            </span>
            <span class="max-w-full truncate px-1">{{ __('menu.more') }}</span>
        </button>
    </div>
</nav>
