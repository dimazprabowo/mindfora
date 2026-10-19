{{--
    Training Player — Alpine audio/video player yang dipakai ulang oleh
    halaman library (peserta) dan halaman manage (admin).

    Pakai: bungkus list/grid materi dengan <x-training-player>...</x-training-player>,
    lalu panggil openPlayer(materialJson) dari tombol/card mana pun di dalamnya.
--}}
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
    {{ $slot }}

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
         data-floating-player
         class="fixed bottom-float right-4 left-4 sm:left-auto sm:w-96 z-40">
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
