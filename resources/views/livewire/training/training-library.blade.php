<div x-data="{
        open: false,
        material: null,
        currentId: null,
        isPlaying: false,
        getPlayer() {
            if (!this.material) return null;
            return this.material.type === 'audio'
                ? this.$refs.audioPlayer
                : this.$refs.videoPlayer;
        },
        openPlayer(m) {
            if (this.currentId === m.id) {
                const el = this.getPlayer();
                if (el) {
                    if (el.paused) { el.play().catch(() => {}); } else { el.pause(); }
                }
                return;
            }
            this.material = m;
            this.currentId = m.id;
            this.isPlaying = false;
            this.open = true;
            this.$nextTick(() => {
                const el = this.getPlayer();
                if (el) {
                    el.load();
                    el.play().catch(() => {});
                }
            });
        },
        switchTo(m) {
            this.material = m;
            this.currentId = m.id;
            this.isPlaying = false;
            this.$nextTick(() => {
                const el = this.getPlayer();
                if (el) {
                    el.load();
                    el.play().catch(() => {});
                }
            });
        },
        closePlayer() {
            this.open = false;
        },
        stopPlayer() {
            const el = this.getPlayer();
            if (el) { el.pause(); el.currentTime = 0; }
            this.isPlaying = false;
            this.currentId = null;
            this.open = false;
        },
        togglePlayPause() {
            const el = this.getPlayer();
            if (!el) return;
            if (el.paused) { el.play().catch(() => {}); } else { el.pause(); }
        },
        onPlay() { this.isPlaying = true; },
        onPause() { this.isPlaying = false; },
        onEnded() { this.isPlaying = false; this.currentId = null; }
    }">
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

    {{-- Player Modal (audio/video element tetap di DOM saat modal hidden — audio tetap playing) --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-on:keydown.escape.window="closePlayer()"
         role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div x-show="open" x-on:click="closePlayer()"
             class="fixed inset-0 bg-gray-900/75 transition-opacity" x-cloak></div>

        {{-- Modal panel --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="open"
                 class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden"
                 x-cloak>
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2 min-w-0">
                        <span x-show="material?.type === 'audio'"
                              class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">Audio</span>
                        <span x-show="material?.type === 'video'"
                              class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">Video</span>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="material?.title"></h3>
                        <span x-show="isPlaying" x-cloak
                              class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                            <span class="flex items-end gap-0.5 h-2.5">
                                <span class="w-0.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-pulse" style="height: 60%; animation-duration: 0.6s;"></span>
                                <span class="w-0.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-pulse" style="height: 100%; animation-duration: 0.6s; animation-delay: 150ms;"></span>
                                <span class="w-0.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-pulse" style="height: 80%; animation-duration: 0.6s; animation-delay: 300ms;"></span>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wide">{{ __('training.now_playing') }}</span>
                        </span>
                    </div>
                    <button x-on:click="closePlayer()" class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg transition-colors" title="{{ __('training.close') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Player area --}}
                <div class="p-5">
                    {{-- Audio player --}}
                    <div x-show="material?.type === 'audio'" class="space-y-4">
                        <div class="aspect-video flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg overflow-hidden">
                            <img x-show="material?.thumbnail_url" :src="material?.thumbnail_url" class="w-full h-full object-cover" alt="">
                            <svg x-show="!material?.thumbnail_url" class="h-20 w-20 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                        <audio x-ref="audioPlayer"
                               :src="material?.stream_url"
                               controls class="w-full" preload="metadata"
                               x-on:play="onPlay()"
                               x-on:pause="onPause()"
                               x-on:ended="onEnded()"></audio>
                    </div>

                    {{-- Video player --}}
                    <div x-show="material?.type === 'video'" class="space-y-4">
                        <video x-ref="videoPlayer"
                               :src="material?.stream_url"
                               controls class="w-full rounded-lg bg-black" preload="metadata" playsinline
                               x-on:play="onPlay()"
                               x-on:pause="onPause()"
                               x-on:ended="onEnded()"></video>
                    </div>

                    {{-- Description --}}
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span x-show="material?.category" x-text="material?.category" class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700"></span>
                            <span x-text="material?.file_size_formatted"></span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300" x-text="material?.description"></p>
                    </div>

                    {{-- Download button --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a :href="material?.download_url"
                           class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            {{ __('training.download') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mini Player (floating, shown when audio is playing but modal is closed) --}}
    <div x-show="currentId !== null && !open" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-4 right-4 left-4 sm:left-auto sm:w-96 z-40">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center gap-3 p-3">
                {{-- Thumbnail / icon --}}
                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center
                    bg-gradient-to-br"
                    :class="material?.type === 'audio' ? 'from-blue-500 to-blue-700' : 'from-purple-500 to-purple-700'">
                    <img x-show="material?.thumbnail_url" :src="material?.thumbnail_url" class="w-full h-full object-cover" alt="">
                    <svg x-show="!material?.thumbnail_url" class="h-6 w-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                </div>

                {{-- Title + equalizer --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-900 dark:text-white truncate" x-text="material?.title"></p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span x-show="isPlaying" class="flex items-end gap-0.5 h-2.5">
                            <span class="w-0.5 bg-primary-500 rounded-full animate-pulse" style="height: 60%; animation-duration: 0.6s;"></span>
                            <span class="w-0.5 bg-primary-500 rounded-full animate-pulse" style="height: 100%; animation-duration: 0.6s; animation-delay: 150ms;"></span>
                            <span class="w-0.5 bg-primary-500 rounded-full animate-pulse" style="height: 80%; animation-duration: 0.6s; animation-delay: 300ms;"></span>
                        </span>
                        <span class="text-[10px] uppercase tracking-wide font-medium"
                              :class="isPlaying ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500'"
                              x-text="isPlaying ? '{{ __('training.now_playing') }}' : '{{ __('training.paused') }}'"></span>
                    </div>
                </div>

                {{-- Controls --}}
                <button x-on:click="togglePlayPause()"
                        class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center transition-colors"
                        :title="isPlaying ? '{{ __('training.pause') }}' : '{{ __('training.resume') }}'">
                    <svg x-show="!isPlaying" class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <svg x-show="isPlaying" x-cloak class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                </button>
                <button x-on:click="open = true"
                        class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center transition-colors"
                        title="{{ __('training.expand') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>
                <button x-on:click="stopPlayer()"
                        class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center transition-colors"
                        title="{{ __('training.close') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
