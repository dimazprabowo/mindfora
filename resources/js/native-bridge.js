/**
 * Native bridge for the Mindfora Android app (Capacitor WebView, repo: mindfora-webapps).
 * No-op in regular browsers. Capacitor injects window.Capacitor (incl. Plugins) into every page it loads,
 * so this file must NOT import any @capacitor/* npm package.
 */
const Capacitor = window.Capacitor;

if (Capacitor?.isNativePlatform?.()) {
    const { App, Haptics, SystemBars } = Capacitor.Plugins;

    syncSystemBars(SystemBars);
    handleBackButton(App);
    initPullToRefresh(Haptics);
    initTapHaptics(Haptics);
}

/** Status/navigation bar icon color follows the page background (dark mode or dark auth gradient). */
function syncSystemBars(SystemBars) {
    const mobile = window.matchMedia('(max-width: 1023.98px)');
    const apply = () => {
        const darkBackground = document.documentElement.classList.contains('dark')
            || (mobile.matches && document.body?.dataset.systemBars === 'dark');
        // 'DARK' = light icons for dark backgrounds, 'LIGHT' = dark icons for light backgrounds.
        SystemBars.setStyle({ style: darkBackground ? 'DARK' : 'LIGHT' });
    };

    new MutationObserver(apply).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    document.addEventListener('livewire:navigated', apply);
    mobile.addEventListener('change', apply);
    apply();
}

/** Pages where "back" leaves the app instead of walking history (avoids login <-> dashboard redirect loops). */
const ROOT_PATHS = ['/', '/dashboard', '/login'];

function handleBackButton(App) {
    App.addListener('backButton', ({ canGoBack }) => {
        if (closeTopOverlay()) return;
        if (canGoBack && !ROOT_PATHS.includes(window.location.pathname)) {
            window.history.back();
            return;
        }
        App.minimizeApp();
    });
}

/**
 * Every modal, drawer overlay and the mobile sidebar in this app close when their `.fixed.inset-0` backdrop is clicked.
 * The last visible one in document order is the top-most (backdrops are nested after their container).
 */
function closeTopOverlay() {
    const backdrop = [...document.querySelectorAll('.fixed.inset-0')].reverse().find((el) => el.getClientRects().length > 0);
    backdrop?.click();
    return Boolean(backdrop);
}

function initPullToRefresh(Haptics) {
    const THRESHOLD = 72;
    const MAX_PULL = 120;
    let startY = null;
    let armed = false;
    let indicator = null;

    const hasOpenOverlay = () => [...document.querySelectorAll('.fixed.inset-0')].some((el) => el.getClientRects().length > 0);
    const insideScrolledContainer = (el) => {
        for (; el && el !== document.body; el = el.parentElement) {
            if (el.scrollTop > 0) return true;
        }
        return false;
    };

    const show = (pull, refreshing = false) => {
        if (!indicator?.isConnected) indicator = createIndicator();
        const progress = Math.min(pull / THRESHOLD, 1);
        indicator.style.opacity = String(progress);
        indicator.style.transform = `translate(-50%, ${pull - 48}px) rotate(${progress * 270}deg)`;
        indicator.dataset.state = refreshing ? 'refreshing' : armed ? 'armed' : 'pulling';
    };

    const hide = () => {
        if (!indicator) return;
        indicator.style.opacity = '0';
        indicator.style.transform = 'translate(-50%, -48px)';
    };

    document.addEventListener('touchstart', (e) => {
        const canStart = e.touches.length === 1
            && window.scrollY <= 0
            && !hasOpenOverlay()
            && !insideScrolledContainer(e.target);
        startY = canStart ? e.touches[0].clientY : null;
        armed = false;
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (startY === null) return;
        const distance = e.touches[0].clientY - startY;
        if (distance <= 0 || window.scrollY > 0) {
            startY = null;
            hide();
            return;
        }
        const pull = Math.min(MAX_PULL, distance * 0.5);
        if ((pull >= THRESHOLD) !== armed) {
            armed = !armed;
            if (armed) Haptics.impact({ style: 'LIGHT' });
        }
        show(pull);
    }, { passive: true });

    document.addEventListener('touchend', () => {
        if (startY === null) return;
        startY = null;
        if (!armed) {
            hide();
            return;
        }
        show(THRESHOLD, true);
        window.location.reload();
    });
}

function createIndicator() {
    const el = document.createElement('div');
    el.setAttribute('aria-hidden', 'true');
    el.className = 'group fixed left-1/2 z-[70] flex h-10 w-10 items-center justify-center rounded-full bg-white text-primary-600 shadow-lg ring-1 ring-black/5 transition-opacity dark:bg-gray-800 dark:text-primary-400 dark:ring-white/10 pointer-events-none';
    el.style.top = 'calc(env(safe-area-inset-top, 0px) + 0.75rem)';
    el.style.opacity = '0';
    el.innerHTML = `
        <svg class="h-5 w-5 group-data-[state=refreshing]:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <svg class="hidden h-5 w-5 animate-spin group-data-[state=refreshing]:block" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>`;
    document.body.appendChild(el);
    return el;
}

/** Subtle haptic tick on elements marked with data-haptic (e.g. bottom navigation tabs). */
function initTapHaptics(Haptics) {
    document.addEventListener('click', (e) => {
        if (e.target.closest?.('[data-haptic]')) Haptics.impact({ style: 'LIGHT' });
    });
}
