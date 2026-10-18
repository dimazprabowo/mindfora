/**
 * Alpine.js Store Definitions
 * Shared between app and guest layouts to avoid duplication.
 *
 * Timing robustness: Livewire's <script> (regular, non-module) executes
 * before Vite's module scripts (deferred). On DOMContentLoaded,
 * Livewire starts Alpine → dispatches "alpine:init".
 *
 * We register stores on "alpine:init" (normal path) AND immediately if
 * window.Alpine is already available (edge-case fallback). The "notify"
 * event listener is registered OUTSIDE alpine:init so it is always active,
 * even if stores weren't registered in time.
 */

/**
 * Register all Alpine stores. Safe to call multiple times — each store
 * checks if it already exists before registering.
 */
function registerStores() {
    if (!Alpine.store('layout')) {
        Alpine.store('layout', {
            mode: localStorage.getItem('layoutMode') || 'sidebar',
            toggleMode() {
                this.mode = this.mode === 'sidebar' ? 'navbar' : 'sidebar';
                localStorage.setItem('layoutMode', this.mode);
            },
            isSidebar() { return this.mode === 'sidebar'; },
            isNavbar() { return this.mode === 'navbar'; },
        });
    }

    if (!Alpine.store('sidebar')) {
        Alpine.store('sidebar', {
            open: false,
            collapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            toggle() { this.open = !this.open; },
            close() { this.open = false; },
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebarCollapsed', this.collapsed);
            },
        });
    }

    if (!Alpine.store('notification')) {
        Alpine.store('notification', {
            items: [],
            nextId: 0,
            maxVisible: 5,
            duration: 5000,

            add(type, message, title) {
                const id = this.nextId++;
                const defaultTitle = type === 'success' ? 'Berhasil'
                    : type === 'error' ? 'Error'
                    : type === 'warning' ? 'Peringatan'
                    : 'Informasi';

                this.items.push({ id, type, message, title: title || defaultTitle, timeout: null, startTime: 0, remaining: this.duration, paused: false });

                if (this.items.length > this.maxVisible) {
                    const removed = this.items.shift();
                    if (removed.timeout) clearTimeout(removed.timeout);
                }

                this._startTimer(id);
            },

            _startTimer(id) {
                const item = this.items.find(i => i.id === id);
                if (!item) return;
                item.paused = false;
                item.startTime = Date.now();
                clearTimeout(item.timeout);
                item.timeout = setTimeout(() => this.remove(id), item.remaining);
            },

            pause(id) {
                const item = this.items.find(i => i.id === id);
                if (!item || item.paused) return;
                item.paused = true;
                item.remaining -= Date.now() - item.startTime;
                clearTimeout(item.timeout);
            },

            resume(id) {
                const item = this.items.find(i => i.id === id);
                if (!item || !item.paused) return;
                this._startTimer(id);
            },

            remove(id) {
                const item = this.items.find(i => i.id === id);
                if (item && item.timeout) clearTimeout(item.timeout);
                this.items = this.items.filter(i => i.id !== id);
            }
        });
    }

    if (!Alpine.store('darkMode')) {
        const stored = localStorage.getItem('darkMode');
        Alpine.store('darkMode', {
            dark: stored === 'true' || (stored === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
            toggle() {
                this.dark = !this.dark;
                localStorage.setItem('darkMode', this.dark);
            },
        });
    }

    if (!Alpine.store('locale')) {
        // Read cookie set by SetLocale middleware (server-side) for initial state.
        // Fall back to localStorage, then to <html lang="..."> attribute.
        const htmlLang = document.documentElement.lang;
        const cookieMatch = document.cookie.match(/(?:^|;\s*)locale=([^;]+)/);
        const initial = cookieMatch?.[1] || localStorage.getItem('locale') || htmlLang || 'id';

        Alpine.store('locale', {
            current: initial,
            locales: {
                id: { label: 'Bahasa', short: 'ID' },
                en: { label: 'English', short: 'EN' },
            },
            switch(code) {
                if (!this.locales[code]) return;
                // 1. Persist to localStorage (instant UI feedback, like dark mode)
                localStorage.setItem('locale', code);
                // 2. Set cookie (server-readable on next request, expires in 1 year)
                document.cookie = 'locale=' + code + ';path=/;max-age=31536000;samesite=lax';
                // 3. Update UI state
                this.current = code;
                // 4. Reload page so Blade re-renders translations server-side
                window.location.reload();
            },
        });
    }
}

/**
 * Listen for Livewire notify events IMMEDIATELY (not inside alpine:init).
 * If the notification store isn't registered yet, attempt to register
 * all stores on-the-fly before handling the event.
 */
window.addEventListener('notify', (e) => {
    if (!window.Alpine || typeof Alpine.store !== 'function') return;

    const notification = Array.isArray(e.detail) ? e.detail[0] : e.detail;

    // Ensure notification store exists before calling .add()
    if (!Alpine.store('notification')) {
        registerStores();
    }

    const store = Alpine.store('notification');
    if (store) {
        store.add(notification.type || 'success', notification.message || '', notification.title);
    }
});

// Register stores: immediately if Alpine is already loaded, otherwise on alpine:init
if (window.Alpine && typeof Alpine.store === 'function') {
    registerStores();
} else {
    document.addEventListener('alpine:init', registerStores);
}

/**
 * Dark mode sync — keeps <html> class in sync with localStorage.
 */
function syncDarkMode() {
    const stored = localStorage.getItem('darkMode');
    const dark = stored === 'true' || (stored === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}

syncDarkMode();
document.addEventListener('livewire:navigated', syncDarkMode);
window.addEventListener('storage', function (e) {
    if (e.key === 'darkMode') syncDarkMode();
});

/**
 * Cleanup orphaned x-teleport elements on Livewire SPA navigation.
 * Prevents stuck tooltips/flyouts and layout glitches after wire:navigate.
 */
document.addEventListener('livewire:navigating', () => {
    document.querySelectorAll('body > [x-teleport-target]').forEach(el => el.remove());
    document.querySelectorAll('body > .fixed').forEach(el => {
        if (!el.closest('[wire\\:id]') && !el.closest('.min-h-screen') && !el.matches('[x-data]')) {
            el.remove();
        }
    });
});

/**
 * After Livewire SPA navigation completes, dispatch event to reset Alpine component states.
 */
document.addEventListener('livewire:navigated', () => {
    window.dispatchEvent(new Event('resize'));
});
