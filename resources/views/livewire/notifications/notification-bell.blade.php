<div wire:poll.15s="loadUnreadCount" wire:click.away="closeDropdown" class="relative">
    {{-- Bell Button --}}
    <button wire:click="toggleOpen"
            class="relative p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-800">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    @if($isOpen)
        <div class="absolute right-0 mt-2 w-72 sm:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('notifications.title') }}</h3>
                <div class="flex items-center space-x-2">
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead"
                                wire:loading.attr="disabled"
                                wire:target="markAllAsRead"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline disabled:opacity-50">
                            <span wire:loading.class="hidden" wire:target="markAllAsRead">{{ __('notifications.mark_all_read') }}</span>
                            <svg wire:loading wire:target="markAllAsRead" class="animate-spin w-3.5 h-3.5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Notification List --}}
            <div class="max-h-80 overflow-y-auto custom-scrollbar divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($notifications as $notification)
                    <div wire:key="notif-{{ $notification->id }}"
                         wire:click="markAsRead({{ $notification->id }})"
                         class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer {{ is_null($notification->read_at) ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                        <div class="flex items-start space-x-3">
                            {{-- Icon --}}
                            <div class="flex-shrink-0 mt-0.5 relative">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center @switch($notification->type) @case('success') bg-green-100 dark:bg-green-900/30 @break @case('warning') bg-yellow-100 dark:bg-yellow-900/30 @break @case('danger') bg-red-100 dark:bg-red-900/30 @break @default bg-primary-100 dark:bg-primary-900/30 @endswitch">
                                    @switch($notification->type)
                                        @case('success')
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            @break
                                        @case('warning')
                                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            @break
                                        @case('danger')
                                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                    @endswitch
                                </div>
                                <div wire:loading.flex wire:target="markAsRead({{ $notification->id }})" class="absolute inset-0 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 items-center justify-center">
                                    <svg class="animate-spin w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $notification->title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-0.5">
                                    {{ $notification->message }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Unread dot --}}
                            @if(is_null($notification->read_at))
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('notifications.no_notifications_yet') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            @if($notifications->count() > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-2.5">
                    <a href="{{ route('notifications.index') }}"
                       wire:navigate
                       wire:click="closeDropdown"
                       class="block text-center text-sm text-primary-600 dark:text-primary-400 hover:underline font-medium">
                        {{ __('notifications.view_all') }}
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
