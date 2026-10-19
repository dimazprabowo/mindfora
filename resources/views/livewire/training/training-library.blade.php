<x-training-player>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
        <!-- Search -->
        <div class="flex-1 w-full sm:w-auto">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('training.search_materials') }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
        </div>

        <!-- Filter Popover -->
        <x-filter-popover :filters="['typeFilter']">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('training.type') }}</label>
                <x-searchable-select
                    wire:model.live="typeFilter"
                    :options="$this->typeOptions"
                    placeholder="{{ __('training.all_types') }}"
                    searchPlaceholder="{{ __('training.search_type') }}"
                />
            </div>
        </x-filter-popover>
    </div>

    {{-- Grid of materials --}}
    @if($materials->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('training.no_materials') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('training.no_materials_desc') }}
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($materials as $material)
                @php
                    $canPlay = $material->is_ready && $material->file_path;
                    $materialJson = json_encode([
                        'id' => $material->id,
                        'title' => $material->title,
                        'description' => $material->description,
                        'type' => $material->type->value,
                        'category' => $material->category,
                        'file_size_formatted' => $material->file_size_formatted,
                        'stream_url' => route('training.stream', $material),
                        'download_url' => route('training.download', $material),
                        'thumbnail_url' => $material->is_thumbnail_ready ? route('training.thumbnail', $material) : null,
                    ]);
                @endphp
                <div wire:key="card-mat-{{ $material->id }}"
                     :class="currentId === {{ $material->id }} ? 'ring-2 ring-primary-500 dark:ring-primary-400' : ''"
                     class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all">
                    {{-- Thumbnail / icon area — clickable to open player --}}
                    <div @if($canPlay) x-on:click="openPlayer({{ $materialJson }})" @endif
                        class="aspect-video flex items-center justify-center overflow-hidden relative
                        @if($canPlay) cursor-pointer @endif
                        @if($material->is_thumbnail_ready)
                            bg-gray-100 dark:bg-gray-700
                        @else
                            bg-gradient-to-br {{ $material->type->value === 'audio' ? 'from-blue-500 to-blue-700' : 'from-purple-500 to-purple-700' }}
                        @endif">
                        @if($material->is_thumbnail_ready)
                            <img src="{{ route('training.thumbnail', $material) }}" alt="{{ $material->title }}" class="w-full h-full object-cover">
                        @elseif($material->type->value === 'audio')
                            <svg class="h-16 w-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        @else
                            <svg class="h-16 w-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        @endif
                        {{-- Play button overlay (only if file is ready) --}}
                        @if($canPlay)
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 hover:opacity-100 transition-opacity">
                                <div class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                    <svg x-show="currentId !== {{ $material->id }} || !isPlaying" class="w-7 h-7 text-primary-600 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    <svg x-show="currentId === {{ $material->id }} && isPlaying" x-cloak class="w-7 h-7 text-primary-600" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                                </div>
                            </div>
                        @endif
                        {{-- Now Playing indicator overlay --}}
                        @if($canPlay)
                            <div x-show="currentId === {{ $material->id }} && isPlaying" x-cloak
                                 class="absolute top-2 left-2 inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-primary-600/95 backdrop-blur-sm shadow-lg">
                                <span class="flex items-end gap-0.5 h-3">
                                    <span class="w-0.5 bg-white rounded-full animate-pulse" style="height: 60%; animation-duration: 0.6s; animation-delay: 0ms;"></span>
                                    <span class="w-0.5 bg-white rounded-full animate-pulse" style="height: 100%; animation-duration: 0.6s; animation-delay: 150ms;"></span>
                                    <span class="w-0.5 bg-white rounded-full animate-pulse" style="height: 80%; animation-duration: 0.6s; animation-delay: 300ms;"></span>
                                    <span class="w-0.5 bg-white rounded-full animate-pulse" style="height: 50%; animation-duration: 0.6s; animation-delay: 450ms;"></span>
                                </span>
                                <span class="text-[10px] font-bold text-white uppercase tracking-wide">{{ __('training.now_playing') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $material->type->badgeClass() }}">
                                {{ $material->type->label() }}
                            </span>
                            @if($material->category)
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $material->category }}</span>
                            @endif
                        </div>

                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ $material->title }}</h3>

                        @if($material->description)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $material->description }}</p>
                        @endif

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ $material->file_size_formatted }}</span>
                            <div class="flex items-center gap-1.5">
                                @if($canPlay)
                                    <button x-on:click="openPlayer({{ $materialJson }})"
                                        class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition-colors"
                                        :class="currentId === {{ $material->id }}
                                            ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                                            : 'bg-primary-600 text-white hover:bg-primary-500'"
                                        :title="currentId === {{ $material->id }} ? (isPlaying ? '{{ __('training.pause') }}' : '{{ __('training.resume') }}') : '{{ __('training.play') }}'">
                                        <svg x-show="currentId !== {{ $material->id }} || !isPlaying" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg x-show="currentId === {{ $material->id }} && isPlaying" x-cloak class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                                        <span x-text="currentId === {{ $material->id }} ? (isPlaying ? '{{ __('training.playing') }}' : '{{ __('training.paused') }}') : '{{ __('training.play') }}'"></span>
                                    </button>
                                @endif
                                @if($canPlay)
                                    <a href="{{ route('training.download', $material) }}"
                                       class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                       title="{{ __('training.download') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $materials->links() }}
        </div>
    @endif

</x-training-player>
