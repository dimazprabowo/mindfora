@php
    // Single source of the available text sizes (root font-size %). Labels from lang common.font_size_levels.
    $levels = __('common.font_size_levels');
@endphp

{{-- Floating text-size control (bottom-right default, draggable). Scales the whole UI via the root font-size (Alpine store fontScale).
     Drag: threshold 10px supaya tap tetap men-toggle panel; saat dilepas tombol snap ke tepi horizontal terdekat; posisi disimpan di localStorage('fontFabPos'). --}}
<div x-data="{
        open: false,
        pos: null,
        drag: null,
        dragging: false,
        justDragged: false,
        snapFrame: null,
        holding: false,
        holdTimer: null,
        levels: @js(array_keys($levels)),
        labels: @js($levels),
        get index() { return Math.max(0, this.levels.indexOf($store.fontScale.percent)); },
        step(delta) {
            const next = Math.min(this.levels.length - 1, Math.max(0, this.index + delta));
            $store.fontScale.set(this.levels[next]);
        },
        init() {
            try {
                const saved = JSON.parse(localStorage.getItem('fontFabPos'));
                if (saved && Number.isFinite(saved.x) && Number.isFinite(saved.y)) {
                    this.pos = this.clamp(saved.x, saved.y);
                }
            } catch (e) {}
        },
        clamp(x, y) {
            const w = this.$el.offsetWidth || 56, h = this.$el.offsetHeight || 56;
            return {
                x: Math.min(Math.max(8, x), Math.max(8, window.innerWidth - w - 8)),
                y: Math.min(Math.max(8, y), Math.max(8, window.innerHeight - h - 8)),
            };
        },
        startDrag(e) {
            if (e.button !== undefined && e.button !== 0) return;
            cancelAnimationFrame(this.snapFrame); // batalkan animasi snap yang masih berjalan
            clearTimeout(this.holdTimer);
            this.holdTimer = setTimeout(() => this.holding = true, 500); // klik-tahan = mode genggam (visual feedback)
            try { e.currentTarget.setPointerCapture(e.pointerId); } catch (err) {}
            const rect = this.$el.getBoundingClientRect();
            this.drag = { startX: e.clientX, startY: e.clientY, offX: e.clientX - rect.left, offY: e.clientY - rect.top };
            this.dragging = false;
        },
        onDrag(e) {
            if (!this.drag) return;
            if (!this.dragging && Math.hypot(e.clientX - this.drag.startX, e.clientY - this.drag.startY) < 10) return;
            if (!this.dragging) { this.dragging = true; this.open = false; clearTimeout(this.holdTimer); this.holding = false; }
            this.pos = this.clamp(e.clientX - this.drag.offX, e.clientY - this.drag.offY);
        },
        endDrag() {
            if (!this.drag) return;
            clearTimeout(this.holdTimer);
            const wasDragging = this.dragging, wasHolding = this.holding;
            this.drag = null;
            this.dragging = false;
            this.holding = false;
            if (wasDragging || wasHolding) {
                this.justDragged = true;
                if (wasDragging) this.snapToEdge();
                setTimeout(() => this.justDragged = false, 400);
            }
        },
        // Lepas drag: animasikan tombol ke tepi horizontal terdekat (ease-out cubic 200ms), lalu simpan posisi.
        snapToEdge() {
            if (!this.pos) return;
            const w = this.$el.offsetWidth || 56;
            const targetX = (this.pos.x + w / 2) < window.innerWidth / 2 ? 8 : Math.max(8, window.innerWidth - w - 8);
            if (Math.abs(targetX - this.pos.x) < 1) {
                try { localStorage.setItem('fontFabPos', JSON.stringify(this.pos)); } catch (e) {}
                return;
            }
            const startX = this.pos.x, y = this.pos.y, t0 = performance.now();
            const tick = (t) => {
                const k = Math.min(1, (t - t0) / 200);
                this.pos = this.clamp(startX + (targetX - startX) * (1 - Math.pow(1 - k, 3)), y);
                if (k < 1) this.snapFrame = requestAnimationFrame(tick);
                else try { localStorage.setItem('fontFabPos', JSON.stringify(this.pos)); } catch (e) {}
            };
            this.snapFrame = requestAnimationFrame(tick);
        },
        // Panel membuka ke bawah saat FAB di paruh atas layar, ke atas saat di paruh bawah.
        get panelBelow() { return this.pos ? this.pos.y <= window.innerHeight / 2 : false; },
        // Panel melebar ke kiri saat FAB di paruh kanan layar, ke kanan saat di paruh kiri (arah ke tengah layar).
        get panelRight() { return this.pos ? this.pos.x >= window.innerWidth / 2 : true; },
        // Offset kiri panel relatif ke FAB (px) — tepi panel diratakan dengan tepi tombol, lalu diklem supaya tidak keluar viewport.
        get panelShift() {
            const bw = this.$el.offsetWidth || 56;
            const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
            const w = Math.min(18 * rem, window.innerWidth - 32); // w-72 & max-w-[calc(100vw-2rem)]
            if (!this.pos) return bw - w;
            const want = this.panelRight ? this.pos.x + bw - w : this.pos.x;
            return Math.min(Math.max(8, want), Math.max(8, window.innerWidth - w - 8)) - this.pos.x;
        },
    }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     @pointermove.window="onDrag($event)"
     @pointerup.window="endDrag()"
     @pointercancel.window="endDrag()"
     @resize.window="pos && (pos = clamp(pos.x, pos.y))"
     data-ptr-ignore
     :class="pos
        ? 'fixed z-50 select-none print:hidden'
        : 'font-scale-fab bottom-float fixed right-4 z-50 select-none print:hidden'"
     :style="pos ? `left:${pos.x}px; top:${pos.y}px` : ''">

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
         :class="panelBelow ? 'top-full mt-3' : 'bottom-full mb-3'"
         :style="{ left: panelShift + 'px' }"
         class="absolute w-72 max-w-[calc(100vw-2rem)] max-h-[calc(100dvh-2rem)] overflow-y-auto custom-scrollbar origin-center rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-4">
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
            @pointerdown="startDrag($event)"
            @contextmenu.prevent
            @click="if (justDragged) { justDragged = false } else { open = !open }"
            :aria-expanded="open"
            aria-controls="font-scale-panel"
            data-haptic
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg shadow-primary-900/20 ring-4 ring-white/80 dark:ring-gray-900/80 hover:bg-primary-700 active:scale-95 transition-all focus:outline-none focus:ring-primary-300 touch-none cursor-grab"
            :class="(dragging || holding) && 'cursor-grabbing scale-95'"
            aria-label="{{ __('common.font_size') }}"
            title="{{ __('common.font_size') }}">
        <span class="font-bold leading-none pointer-events-none" aria-hidden="true"><span class="text-xl">A</span><span class="text-sm">a</span></span>
        <span x-show="index > 0"
              x-cloak
              x-text="levels[index] + '%'"
              class="absolute -top-1 -right-1 rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold leading-none text-amber-950 shadow pointer-events-none"></span>
    </button>
</div>
