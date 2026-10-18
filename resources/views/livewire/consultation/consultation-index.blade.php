<div class="h-[calc(100vh-8rem)] flex overflow-hidden rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    {{-- Sidebar: Chat list + Counselor search --}}
    <div class="w-full sm:w-80 flex flex-col border-r border-gray-200 dark:border-gray-700 {{ $activeChat ? 'hidden sm:flex' : 'flex' }}">
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('menu.consultation') }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('consultation.chat_with_counselor_admin') }}</p>
        </div>

        {{-- Search counselor --}}
        <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
            <input wire:model.live.debounce.300ms="searchCounselor" type="text" placeholder="{{ __('consultation.search_counselor') }}"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        {{-- Chat list --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @if($chats->isNotEmpty())
                <div class="px-2 py-1">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 px-2 py-1">{{ __('consultation.active_conversations') }}</p>
                    @foreach($chats as $chat)
                        @php
                            $unread = $unreadCounts[$chat->id] ?? 0;
                            $otherUser = $chat->participants->firstWhere('id', '!=', auth()->id());
                        @endphp
                        <button wire:click="selectChat({{ $chat->id }})"
                            wire:key="chat-list-{{ $chat->id }}"
                            class="w-full flex items-center gap-3 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ $activeChatId === $chat->id ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                {{ substr($otherUser?->name ?? '?', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0 text-left">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $otherUser?->name ?? 'Unknown' }}</span>
                                    @if($chat->latestMessage)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ $chat->latestMessage->created_at->format('H:i') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $chat->latestMessage?->body ?? __('consultation.no_messages') }}</span>
                                    @if($unread > 0)
                                        <span class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-600 rounded-full flex-shrink-0">{{ $unread }}</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Counselor list --}}
            <div class="px-2 py-1">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 px-2 py-1">{{ __('consultation.available_counselors') }}</p>
                @forelse($counselors as $counselor)
                    <button wire:click="startConsultation({{ $counselor->id }})"
                        wire:key="counselor-{{ $counselor->id }}"
                        class="w-full flex items-center gap-3 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                            {{ substr($counselor->name, 0, 1) }}
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></span>
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $counselor->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $counselor->email }}</div>
                        </div>
                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                @empty
                    <p class="text-xs text-gray-400 dark:text-gray-500 px-2 py-4 text-center">{{ __('consultation.no_counselors_found') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Main chat area --}}
    <div class="flex-1 flex flex-col {{ $activeChat ? 'flex' : 'hidden sm:flex' }}">
        @if($activeChat)
            @php $otherUser = $activeChat->participants->firstWhere('id', '!=', auth()->id()); @endphp
            {{-- Chat header --}}
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <button wire:click="$set('activeChatId', null)" class="sm:hidden p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-sm">
                    {{ substr($otherUser?->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $otherUser?->name ?? 'Unknown' }}</div>
                    <div class="text-xs text-green-500">{{ __('consultation.online') }}</div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-3 bg-gray-50 dark:bg-gray-900/30" x-data="{ scrollToBottom() { $nextTick(() => { $el.scrollTop = $el.scrollHeight; }) } }" x-init="scrollToBottom()" wire:poll.5s="$refresh">
                @foreach($messages as $message)
                    @php $isMine = $message->user_id === auth()->id(); @endphp
                    <div wire:key="msg-{{ $message->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-lg px-3 py-2 {{ $isMine ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white' }}">
                            <p class="text-sm whitespace-pre-wrap break-words">{{ $message->body }}</p>
                            <p class="text-xs {{ $isMine ? 'text-primary-100' : 'text-gray-400 dark:text-gray-500' }} mt-0.5">{{ $message->created_at->format('H:i') }}</p>
                        </div>
                    </div>
                @endforeach

                @if($messages->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400 dark:text-gray-500">{{ __('consultation.no_messages_start') }}</p>
                    </div>
                @endif
            </div>

            {{-- Message input --}}
            <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                <form wire:submit="sendMessage" class="flex items-center gap-2">
                    <input wire:model="messageBody"
                        wire:keydown.enter="sendMessage"
                        x-on:keydown.window="if($event.key === 'Enter' && !$event.shiftKey) { $wire.sendTyping(false); }"
                        x-on:input.debounce.500ms="$wire.sendTyping(true)"
                        x-on:blur="$wire.sendTyping(false)"
                        type="text"
                        placeholder="{{ __('consultation.write_message') }}"
                        class="flex-1 px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-full focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    <x-loading-button type="submit" target="sendMessage" variant="icon-blue" icon="send" iconClass="w-6 h-6"
                        class="!p-0 w-11 h-11 flex-shrink-0 !rounded-full !text-white !bg-primary-600 hover:!bg-primary-700 dark:!text-white focus:!ring-primary-500"
                        title="{{ __('consultation.send') }}">
                        <span class="sr-only">{{ __('consultation.send') }}</span>
                    </x-loading-button>
                </form>
            </div>
        @else
            {{-- Empty state --}}
            <div class="flex-1 flex items-center justify-center p-8">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">{{ __('consultation.select_counselor') }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('consultation.select_counselor_desc') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
