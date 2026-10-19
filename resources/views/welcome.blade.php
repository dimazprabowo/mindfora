<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data :class="{ 'dark': $store.darkMode.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('landing.Hero Subtitle') }}">

    <title>{{ app_name() }} - {{ __('landing.Tagline') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <x-anti-fouc-script />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles (kept in <head> — matches auto-injected placement) --}}
    @livewireStyles
</head>
<body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-950 dark:text-gray-100 overflow-x-hidden">

    {{-- Landing page animations --}}
    <style>
        {{-- Smooth scroll for anchor links + offset for fixed navbar --}}
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 4rem;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-12px); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50%      { opacity: 0.6; transform: scale(1.08); }
        }
        @keyframes gradientPan {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes shimmer {
            0%   { transform: translateX(-150%) skewX(-20deg); }
            100% { transform: translateX(150%) skewX(-20deg); }
        }
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        @keyframes arrowPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50%      { transform: scale(1.08); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        }

        /* Scroll reveal — elements start hidden, become visible when intersecting */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Continuous animations */
        .animate-float       { animation: float 6s ease-in-out infinite; }
        .animate-pulse-glow  { animation: pulseGlow 8s ease-in-out infinite; }
        .animate-spin-slow   { animation: spinSlow 40s linear infinite; }
        .animate-arrow-pulse { animation: arrowPulse 2.4s ease-in-out infinite; }

        /* Animated gradient text */
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradientPan 6s ease infinite;
        }

        /* Shimmer effect on hover for primary CTA buttons */
        .shimmer-btn { position: relative; overflow: hidden; }
        .shimmer-btn::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transform: translateX(-150%) skewX(-20deg);
            pointer-events: none;
        }
        .shimmer-btn:hover::after {
            animation: shimmer 1.2s ease;
        }

        /* Hide elements with x-cloak before Alpine initializes */
        [x-cloak] { display: none !important; }

        /* Hide scrollbar for horizontal nav scroll container */
        .navbar-scroll { scrollbar-width: none; -ms-overflow-style: none; }
        .navbar-scroll::-webkit-scrollbar { display: none; }

        /* Respect reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1; transform: none; transition: none; }
            .animate-float, .animate-pulse-glow, .animate-spin-slow, .animate-gradient { animation: none; }
            .animate-arrow-pulse { animation: none; }
            .shimmer-btn::after { display: none; }
        }
    </style>

    {{-- ===== Navbar ===== --}}
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/70 dark:bg-gray-950/70 backdrop-blur-xl border-b border-gray-200/60 dark:border-gray-800/60 transition-all duration-300"
         x-data="{ mobileMenuOpen: false }"
         x-on:keydown.escape.window="mobileMenuOpen = false">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('welcome') }}" wire:navigate class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30 group-hover:shadow-primary-500/50 transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V8M9 11l3-3 3 3M9 14l3-3 3 3M9 17l3-3 3 3"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg text-gray-900 dark:text-white tracking-tight">{{ app_name() }}</span>
                </a>

                {{-- Desktop/Tablet Nav: Horizontal scrollable menu with arrow buttons (mirrors admin navbar pattern) --}}
                <div class="hidden md:flex items-center flex-1 min-w-0 mx-4 relative"
                     x-data="{
                         showLeftArrow: false,
                         showRightArrow: false,
                         checkScroll() {
                             const container = this.$refs.menuContainer;
                             if (container) {
                                 const hasOverflow = container.scrollWidth > (container.clientWidth + 1);
                                 this.showLeftArrow = hasOverflow && container.scrollLeft > 5;
                                 this.showRightArrow = hasOverflow && (Math.ceil(container.scrollLeft + container.clientWidth) < container.scrollWidth - 5);
                             }
                         },
                         scrollLeft() {
                             this.$refs.menuContainer.scrollBy({ left: -150, behavior: 'smooth' });
                         },
                         scrollRight() {
                             this.$refs.menuContainer.scrollBy({ left: 150, behavior: 'smooth' });
                         }
                     }"
                     x-init="
                         $nextTick(() => {
                             checkScroll();
                             const resizeObserver = new ResizeObserver(() => checkScroll());
                             resizeObserver.observe($refs.menuContainer);
                             window.addEventListener('resize', () => checkScroll());
                         });
                     ">
                    {{-- Left Arrow --}}
                    <button x-show="showLeftArrow"
                            x-cloak
                            x-on:click="scrollLeft()"
                            type="button"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4"
                            class="absolute left-0 top-1/2 -translate-y-1/2 z-30 p-1.5 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-full shadow-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:scale-110 active:scale-95 transition-all focus:outline-none"
                            aria-label="Scroll left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>

                    {{-- Menu Container with Scroll --}}
                    <div class="relative flex items-center flex-1 overflow-hidden">
                        <div x-ref="menuContainer"
                             x-on:scroll="checkScroll()"
                             class="flex items-center overflow-x-auto navbar-scroll px-2 w-full">
                            <div class="flex items-center gap-6 min-w-max py-1 mx-auto">
                                <a href="#about" class="text-sm font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors whitespace-nowrap">{{ __('landing.Nav About') }}</a>
                                <a href="#features" class="text-sm font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors whitespace-nowrap">{{ __('landing.Nav Features') }}</a>
                                <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors whitespace-nowrap">{{ __('landing.Nav HowItWorks') }}</a>
                                <a href="#benefits" class="text-sm font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors whitespace-nowrap">{{ __('landing.Nav Benefits') }}</a>
                                <a href="#testimonials" class="text-sm font-medium text-gray-600 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors whitespace-nowrap">{{ __('landing.Nav Testimonials') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- Right Arrow --}}
                    <button x-show="showRightArrow"
                            x-cloak
                            x-on:click="scrollRight()"
                            type="button"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 translate-x-4"
                            class="absolute right-0 top-1/2 -translate-y-1/2 z-30 p-1.5 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-full shadow-lg text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:scale-110 active:scale-95 transition-all focus:outline-none"
                            aria-label="Scroll right">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-3">
                    {{-- Dark mode toggle --}}
                    <button
                        type="button"
                        x-on:click="$store.darkMode.toggle()"
                        class="p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
                        aria-label="Toggle dark mode"
                    >
                        <svg x-show="!$store.darkMode.dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <svg x-show="$store.darkMode.dark" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <x-language-switcher />

                    @if (auth()->check())
                        {{-- Compact icon-only dashboard button (mobile) --}}
                        <a href="{{ route('dashboard') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="sm:hidden p-2 rounded-lg text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors"
                           aria-label="{{ __('landing.Dashboard') }}"
                           title="{{ __('landing.Dashboard') }}">
                            <svg x-show="!loading" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <svg x-cloak x-show="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                        {{-- Full dashboard button (desktop) --}}
                        <a href="{{ route('dashboard') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-500/30 hover:shadow-lg hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all">
                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span :class="{ 'hidden': loading }">{{ __('landing.Dashboard') }}</span>
                            <svg x-cloak x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="hidden sm:inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 transition-colors">
                            <span :class="{ 'hidden': loading }">{{ __('landing.Nav Login') }}</span>
                            <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                        <a href="{{ route('register') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary-500/30 hover:shadow-lg hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all">
                            <span :class="{ 'hidden': loading }">{{ __('landing.Nav Register') }}</span>
                            <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                    @endif

                    {{-- Mobile hamburger toggle --}}
                    <button
                        type="button"
                        x-on:click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
                        aria-label="Toggle menu"
                        :aria-expanded="mobileMenuOpen"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu panel --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-gray-200/60 dark:border-gray-800/60 bg-white/95 dark:bg-gray-950/95 backdrop-blur-xl"
        >
            <div class="px-4 py-4 space-y-1">
                <a href="#about" x-on:click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-300 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors">{{ __('landing.Nav About') }}</a>
                <a href="#features" x-on:click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-300 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors">{{ __('landing.Nav Features') }}</a>
                <a href="#how-it-works" x-on:click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-300 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors">{{ __('landing.Nav HowItWorks') }}</a>
                <a href="#benefits" x-on:click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-300 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors">{{ __('landing.Nav Benefits') }}</a>
                <a href="#testimonials" x-on:click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-primary-50 dark:text-gray-300 dark:hover:text-primary-400 dark:hover:bg-gray-800 transition-colors">{{ __('landing.Nav Testimonials') }}</a>

                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-800 flex flex-col gap-2">
                    @if (auth()->check())
                        <a href="{{ route('dashboard') }}" wire:navigate
                           x-on:click="mobileMenuOpen = false"
                           class="block w-full text-center rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-2.5 text-base font-semibold text-white shadow-md shadow-primary-500/30 transition-all">
                            {{ __('landing.Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           x-on:click="mobileMenuOpen = false"
                           class="block w-full text-center rounded-lg border border-gray-300 dark:border-gray-700 px-4 py-2.5 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            {{ __('landing.Nav Login') }}
                        </a>
                        <a href="{{ route('register') }}" wire:navigate
                           x-on:click="mobileMenuOpen = false"
                           class="block w-full text-center rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-2.5 text-base font-semibold text-white shadow-md shadow-primary-500/30 transition-all">
                            {{ __('landing.Nav Register') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- ===== Hero Section ===== --}}
    <section class="relative pt-20 pb-20 sm:pt-24 sm:pb-32 overflow-hidden">
        {{-- Layered background gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-primary-50/40 dark:from-gray-950 dark:via-gray-900 dark:to-primary-950/30"></div>

        {{-- Decorative glows --}}
        <div class="absolute top-0 right-0 w-1/2 h-full opacity-40 dark:opacity-20 pointer-events-none">
            <div class="absolute top-20 right-10 w-96 h-96 bg-primary-300 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="absolute bottom-10 right-40 w-80 h-80 bg-primary-200 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: -3s;"></div>
        </div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/2 opacity-30 dark:opacity-10 pointer-events-none">
            <div class="absolute bottom-10 left-10 w-72 h-72 bg-primary-200 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: -5s;"></div>
        </div>

        <div class="relative px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                {{-- Text --}}
                <div class="text-center lg:text-left">
                    {{-- Badge --}}
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-primary-200 dark:border-primary-800 px-4 py-1.5 text-sm font-medium text-primary-700 dark:text-primary-300 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                        </svg>
                        {{ __('landing.Tagline') }}
                    </span>

                    {{-- Headline --}}
                    <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">
                        {{ __('landing.Hero Title') }}
                    </h1>

                    {{-- Subtitle --}}
                    <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        {{ __('landing.Hero Subtitle') }}
                    </p>

                    {{-- CTA buttons --}}
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @if (auth()->check())
                            <a href="{{ route('dashboard') }}" wire:navigate
                               x-data="{ loading: false }"
                               x-on:click="$nextTick(() => loading = true)"
                               x-on:livewire:navigate:end.window="loading = false"
                               :class="{ 'opacity-75 cursor-wait': loading }"
                               class="shimmer-btn group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 px-7 py-3.5 text-base font-semibold text-white shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all">
                                <span :class="{ 'hidden': loading }" class="inline-flex items-center gap-2">
                                    {{ __('landing.Dashboard') }}
                                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                                <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" wire:navigate
                               x-data="{ loading: false }"
                               x-on:click="$nextTick(() => loading = true)"
                               x-on:livewire:navigate:end.window="loading = false"
                               :class="{ 'opacity-75 cursor-wait': loading }"
                               class="shimmer-btn group inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 px-7 py-3.5 text-base font-semibold text-white shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40 hover:-translate-y-0.5 transition-all">
                                <span :class="{ 'hidden': loading }" class="inline-flex items-center gap-2">
                                    {{ __('landing.Hero CTA') }}
                                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                                <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('login') }}" wire:navigate
                               x-data="{ loading: false }"
                               x-on:click="$nextTick(() => loading = true)"
                               x-on:livewire:navigate:end.window="loading = false"
                               :class="{ 'opacity-75 cursor-wait': loading }"
                               class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm px-7 py-3.5 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 hover:border-primary-300 dark:hover:border-primary-700 transition-all">
                                <span :class="{ 'hidden': loading }">{{ __('landing.Hero CTA Login') }}</span>
                                <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </a>
                        @endif
                    </div>

                    {{-- Stats row: 3 mini cards — vertical stack on mobile, horizontal on sm+ --}}
                    <div class="mt-10 sm:mt-12 grid grid-cols-3 gap-2 sm:gap-4 max-w-lg mx-auto lg:mx-0">
                        {{-- Stat 1: 4 Minggu --}}
                        <div class="flex flex-col items-center lg:items-start text-center lg:text-left p-2.5 sm:p-4 rounded-xl bg-white/60 dark:bg-gray-800/40 border border-gray-200/70 dark:border-gray-700/60 backdrop-blur-sm hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md hover:shadow-primary-500/5 transition-all">
                            <div class="flex flex-col sm:flex-row items-center sm:gap-2 mb-1 sm:mb-2">
                                <div class="flex items-center justify-center w-8 h-8 sm:w-8 sm:h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex-shrink-0 mb-1 sm:mb-0">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </div>
                                <div class="text-base sm:text-xl font-extrabold bg-gradient-to-br from-primary-600 to-primary-800 bg-clip-text text-transparent animate-gradient whitespace-nowrap leading-tight">{{ __('landing.Hero Stat 1 Value') }}</div>
                            </div>
                            <div class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 leading-tight">{{ __('landing.Hero Stat 1 Label') }}</div>
                        </div>

                        {{-- Stat 2: Ilmiah --}}
                        <div class="flex flex-col items-center lg:items-start text-center lg:text-left p-2.5 sm:p-4 rounded-xl bg-white/60 dark:bg-gray-800/40 border border-gray-200/70 dark:border-gray-700/60 backdrop-blur-sm hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md hover:shadow-primary-500/5 transition-all">
                            <div class="flex flex-col sm:flex-row items-center sm:gap-2 mb-1 sm:mb-2">
                                <div class="flex items-center justify-center w-8 h-8 sm:w-8 sm:h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex-shrink-0 mb-1 sm:mb-0">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <div class="text-base sm:text-xl font-extrabold bg-gradient-to-br from-primary-600 to-primary-800 bg-clip-text text-transparent animate-gradient whitespace-nowrap leading-tight" style="animation-delay: -2s;">{{ __('landing.Hero Stat 2 Value') }}</div>
                            </div>
                            <div class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 leading-tight">{{ __('landing.Hero Stat 2 Label') }}</div>
                        </div>

                        {{-- Stat 3: Personal --}}
                        <div class="flex flex-col items-center lg:items-start text-center lg:text-left p-2.5 sm:p-4 rounded-xl bg-white/60 dark:bg-gray-800/40 border border-gray-200/70 dark:border-gray-700/60 backdrop-blur-sm hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-md hover:shadow-primary-500/5 transition-all">
                            <div class="flex flex-col sm:flex-row items-center sm:gap-2 mb-1 sm:mb-2">
                                <div class="flex items-center justify-center w-8 h-8 sm:w-8 sm:h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex-shrink-0 mb-1 sm:mb-0">
                                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <div class="text-base sm:text-xl font-extrabold bg-gradient-to-br from-primary-600 to-primary-800 bg-clip-text text-transparent animate-gradient whitespace-nowrap leading-tight" style="animation-delay: -4s;">{{ __('landing.Hero Stat 3 Value') }}</div>
                            </div>
                            <div class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 leading-tight">{{ __('landing.Hero Stat 3 Label') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Hero Visual: Real photo with floating quote badge --}}
                <div class="relative animate-float">
                    <div class="relative aspect-square sm:aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl shadow-primary-500/20 border border-primary-200/50 dark:border-primary-800/50">
                        <img src="{{ asset('images/landing/hero-meditation.jpg') }}"
                             alt="{{ __('landing.Hero Title') }}"
                             class="w-full h-full object-cover"
                             loading="eager" />

                        {{-- Subtle gradient overlay for text readability --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    </div>

                    {{-- Floating quote badge --}}
                    <div class="absolute bottom-6 left-6 right-6 rounded-2xl bg-white/85 dark:bg-gray-900/85 backdrop-blur-md border border-white/50 dark:border-gray-700/50 px-5 py-4 shadow-xl">
                        <p class="text-sm italic text-gray-700 dark:text-gray-300 text-center leading-relaxed">"{{ __('landing.Hero Quote') }}"</p>
                    </div>

                    {{-- Decorative floating dots around card --}}
                    <div class="absolute -top-3 -left-3 w-6 h-6 rounded-full bg-primary-400/30 blur-sm"></div>
                    <div class="absolute -bottom-4 -right-4 w-8 h-8 rounded-full bg-primary-300/30 blur-sm"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== About Section ===== --}}
    <section id="about" class="pt-6 sm:pt-8 pb-24 sm:pb-32 bg-white dark:bg-gray-950 relative overflow-hidden">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-primary-200 dark:via-primary-800 to-transparent"></div>

        <div class="px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 items-center reveal">
                    {{-- Text (3/5) --}}
                    <div class="lg:col-span-3 text-center lg:text-left">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                            <span class="w-8 h-px bg-primary-500"></span>
                            {{ __('landing.Nav About') }}
                        </span>
                        <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight">
                            {{ __('landing.About Title') }}
                        </h2>
                        <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ __('landing.About Description', ['app' => app_name()]) }}
                        </p>
                    </div>

                    {{-- Visual: Real zen garden photo (2/5) --}}
                    <div class="lg:col-span-2">
                        <div class="relative aspect-square max-w-sm mx-auto rounded-3xl overflow-hidden shadow-xl shadow-primary-500/10 border border-primary-200/50 dark:border-primary-800/50">
                            <img src="{{ asset('images/landing/about-zen.jpg') }}"
                                 alt="{{ __('landing.About Title') }}"
                                 class="w-full h-full object-cover"
                                 loading="lazy" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== How It Works Section ===== --}}
    <section id="how-it-works" class="pt-6 sm:pt-8 pb-24 sm:pb-32 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40rem] h-[40rem] bg-primary-100 dark:bg-primary-950/20 rounded-full blur-3xl opacity-50 dark:opacity-30 pointer-events-none"></div>

        <div class="relative px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                    <span class="w-8 h-px bg-primary-500"></span>
                    {{ __('landing.Nav HowItWorks') }}
                    <span class="w-8 h-px bg-primary-500"></span>
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ __('landing.How It Works Title') }}</h2>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">{{ __('landing.How It Works Subtitle') }}</p>
            </div>

            <div class="mt-16 sm:mt-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="group relative reveal {{ [1 => 'z-40', 2 => 'z-30', 3 => 'z-20', 4 => 'z-10'][$i] }}" style="transition-delay: {{ ($i - 1) * 120 }}ms;">
                        <div class="relative rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 text-center">
                            <div class="relative inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-white font-extrabold text-lg shadow-lg shadow-primary-500/30 mb-4">
                                {{ $i }}
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('landing.Week ' . $i) }}</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ __('landing.Week ' . $i . ' Desc') }}
                            </p>
                        </div>

                        @if ($i < 4)
                            {{-- Mobile (1-col): vertical down-arrow with gradient badge + pulse glow --}}
                            <div class="sm:hidden absolute -bottom-6 left-1/2 -translate-x-1/2 z-20" aria-hidden="true">
                                <span class="animate-arrow-pulse flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white shadow-lg shadow-primary-500/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                            </div>

                            {{-- Tablet (2-col): horizontal arrow only between same-row cards (after card 1 & 3) --}}
                            @if ($i % 2 === 1)
                                <div class="hidden sm:flex lg:hidden absolute top-[52px] -right-5 -translate-y-1/2 z-20 items-center transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">
                                    <svg class="w-5 h-8 text-primary-300 dark:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-dasharray="0.1 4" stroke-linecap="round">
                                        <path d="M0 12 H18" />
                                    </svg>
                                    <span class="animate-arrow-pulse -ml-1 flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white shadow-lg shadow-primary-500/40">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </span>
                                </div>
                            @endif

                            {{-- Desktop (4-col): curved SVG connector + gradient arrow badge aligned with number badge --}}
                            <div class="hidden lg:flex absolute top-[52px] -right-6 -translate-y-1/2 z-20 items-center transition-transform duration-300 group-hover:translate-x-1" aria-hidden="true">
                                <svg class="w-8 h-8 text-primary-300 dark:text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 32 32" stroke-width="2" stroke-dasharray="0.1 5" stroke-linecap="round">
                                    <path d="M0 16 Q16 8 28 16" />
                                </svg>
                                <span class="animate-arrow-pulse -ml-1.5 flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white shadow-lg shadow-primary-500/40">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </span>
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- ===== Features Section ===== --}}
    <section id="features" class="pt-6 sm:pt-8 pb-24 sm:pb-32 bg-white dark:bg-gray-950 relative overflow-hidden">
        <div class="px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                    <span class="w-8 h-px bg-primary-500"></span>
                    {{ __('landing.Nav Features') }}
                    <span class="w-8 h-px bg-primary-500"></span>
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ __('landing.Features Title') }}</h2>
            </div>

            <div class="mt-16 sm:mt-20 grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8 max-w-5xl mx-auto">
                {{-- Training --}}
                <div class="group relative rounded-3xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 reveal">
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('images/landing/feature-training.jpg') }}"
                             alt="{{ __('landing.Feature Training Title') }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <div class="w-12 h-12 rounded-xl bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l10.5-3m0 6.553v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 11-.99-3.467l2.31-.66a2.25 2.25 0 001.632-2.163zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 01-1.632 2.163l-1.32.377a1.803 1.803 0 01-.99-3.467l2.31-.66A2.25 2.25 0 009 15.553z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.Feature Training Title') }}</h3>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ __('landing.Feature Training Desc') }}</p>
                    </div>
                </div>

                {{-- Test --}}
                <div class="group relative rounded-3xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 reveal" style="transition-delay: 120ms;">
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('images/landing/feature-test.jpg') }}"
                             alt="{{ __('landing.Feature Test Title') }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <div class="w-12 h-12 rounded-xl bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.68.227-.815.503" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.Feature Test Title') }}</h3>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ __('landing.Feature Test Desc') }}</p>
                    </div>
                </div>

                {{-- Consultation --}}
                <div class="group relative rounded-3xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 reveal" style="transition-delay: 240ms;">
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('images/landing/feature-consultation.jpg') }}"
                             alt="{{ __('landing.Feature Consultation Title') }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <div class="w-12 h-12 rounded-xl bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.Feature Consultation Title') }}</h3>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ __('landing.Feature Consultation Desc') }}</p>
                    </div>
                </div>

                {{-- History --}}
                <div class="group relative rounded-3xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 reveal" style="transition-delay: 360ms;">
                    <div class="relative aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('images/landing/feature-history.jpg') }}"
                             alt="{{ __('landing.Feature History Title') }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-4 left-4">
                            <div class="w-12 h-12 rounded-xl bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('landing.Feature History Title') }}</h3>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ __('landing.Feature History Desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Benefits Section ===== --}}
    <section id="benefits" class="pt-6 sm:pt-8 pb-24 sm:pb-32 bg-gradient-to-b from-primary-50 to-white dark:from-gray-900 dark:to-gray-950 relative overflow-hidden">
        <div class="absolute top-1/3 right-0 w-96 h-96 bg-primary-200/30 dark:bg-primary-900/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                    <span class="w-8 h-px bg-primary-500"></span>
                    {{ __('landing.Benefits Title') }}
                    <span class="w-8 h-px bg-primary-500"></span>
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ __('landing.Benefits Title') }}</h2>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">{{ __('landing.Benefits Subtitle') }}</p>
            </div>

            <div class="mt-16 sm:mt-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="group relative rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl hover:shadow-primary-500/10 hover:-translate-y-1 hover:border-primary-300 dark:hover:border-primary-700 transition-all duration-300 text-center reveal" style="transition-delay: {{ ($i - 1) * 120 }}ms;">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/50 dark:to-primary-800/50 flex items-center justify-center mb-4 mx-auto">
                            @if ($i === 1)
                                <svg class="h-7 w-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            @elseif ($i === 2)
                                <svg class="h-7 w-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V12m0 0v6m0-6h6m-6 0H6" /></svg>
                            @elseif ($i === 3)
                                <svg class="h-7 w-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                            @else
                                <svg class="h-7 w-7 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('landing.Benefit ' . $i . ' Title') }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ __('landing.Benefit ' . $i . ' Desc') }}</p>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- ===== Testimonials Section ===== --}}
    <section id="testimonials" class="pt-6 sm:pt-8 pb-24 sm:pb-32 bg-white dark:bg-gray-950 relative overflow-hidden">
        <div class="px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                    <span class="w-8 h-px bg-primary-500"></span>
                    {{ __('landing.Testimonials Title') }}
                    <span class="w-8 h-px bg-primary-500"></span>
                </span>
                <h2 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ __('landing.Testimonials Title') }}</h2>
                <p class="mt-4 text-base sm:text-lg text-gray-500 dark:text-gray-400">{{ __('landing.Testimonials Subtitle') }}</p>
            </div>

            <div class="mt-16 sm:mt-20 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="relative rounded-3xl bg-gradient-to-br from-primary-50 to-white dark:from-gray-800 dark:to-gray-900 border border-primary-200/50 dark:border-gray-700 p-8 shadow-sm hover:shadow-xl hover:shadow-primary-500/10 transition-all duration-300 reveal" style="transition-delay: {{ ($i - 1) * 150 }}ms;">
                        {{-- Quote icon --}}
                        <svg class="w-10 h-10 text-primary-300 dark:text-primary-700 mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>

                        {{-- Quote text --}}
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed italic">"{{ __('landing.Testimonial ' . $i . ' Quote') }}"</p>

                        {{-- Author --}}
                        <div class="mt-6 flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                {{ strtoupper(substr(__('landing.Testimonial ' . $i . ' Name'), 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ __('landing.Testimonial ' . $i . ' Name') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.Testimonial ' . $i . ' Role') }}</div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    {{-- ===== CTA Section ===== --}}
    <section class="py-24 sm:py-32 relative overflow-hidden">
        {{-- Background image with overlay --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/landing/cta-background.jpg') }}"
                 alt=""
                 class="w-full h-full object-cover"
                 loading="lazy" />
            <div class="absolute inset-0 bg-gradient-to-br from-primary-800/95 via-primary-900/95 to-primary-950/95"></div>
        </div>

        {{-- Decorative glows --}}
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="max-w-3xl mx-auto text-center reveal">
                {{-- Lotus icon --}}
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-sm mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V8M9 11l3-3 3 3M9 14l3-3 3 3M9 17l3-3 3 3"/>
                    </svg>
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">{{ __('landing.CTA Title') }}</h2>
                <p class="mt-5 text-lg text-primary-100 max-w-xl mx-auto">{{ __('landing.CTA Subtitle') }}</p>
                <div class="mt-10">
                    @if (auth()->check())
                        <a href="{{ route('dashboard') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="shimmer-btn group inline-flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-primary-700 shadow-2xl shadow-primary-900/30 hover:shadow-primary-900/50 hover:-translate-y-0.5 transition-all">
                            <span :class="{ 'hidden': loading }" class="inline-flex items-center gap-2">
                                {{ __('landing.Dashboard') }}
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                            <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" wire:navigate
                           x-data="{ loading: false }"
                           x-on:click="$nextTick(() => loading = true)"
                           x-on:livewire:navigate:end.window="loading = false"
                           :class="{ 'opacity-75 cursor-wait': loading }"
                           class="shimmer-btn group inline-flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-bold text-primary-700 shadow-2xl shadow-primary-900/30 hover:shadow-primary-900/50 hover:-translate-y-0.5 transition-all">
                            <span :class="{ 'hidden': loading }" class="inline-flex items-center gap-2">
                                {{ __('landing.CTA Button') }}
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                            <svg x-cloak :class="{ 'hidden': !loading }" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Footer ===== --}}
    <footer class="bg-gray-950 dark:bg-black text-gray-400 py-16 relative overflow-hidden">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-primary-700 to-transparent"></div>

        <div class="px-4 sm:px-6 lg:px-10 xl:px-16">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-12 max-w-5xl mx-auto">
                {{-- Brand --}}
                <div class="sm:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-lg shadow-primary-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V8M9 11l3-3 3 3M9 14l3-3 3 3M9 17l3-3 3 3"/>
                            </svg>
                        </div>
                        <span class="font-bold text-white text-lg tracking-tight">{{ app_name() }}</span>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed">{{ __('landing.Tagline') }}</p>
                </div>

                {{-- Features --}}
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider">{{ __('landing.Footer Features') }}</h4>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="#features" class="hover:text-primary-400 transition-colors">{{ __('landing.Feature Training Title') }}</a></li>
                        <li><a href="#features" class="hover:text-primary-400 transition-colors">{{ __('landing.Feature Test Title') }}</a></li>
                        <li><a href="#features" class="hover:text-primary-400 transition-colors">{{ __('landing.Feature Consultation Title') }}</a></li>
                        <li><a href="#features" class="hover:text-primary-400 transition-colors">{{ __('landing.Feature History Title') }}</a></li>
                    </ul>
                </div>

                {{-- About --}}
                <div>
                    <h4 class="font-semibold text-white text-sm uppercase tracking-wider">{{ __('landing.Footer About') }}</h4>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="#about" class="hover:text-primary-400 transition-colors">{{ __('landing.Nav About') }}</a></li>
                        <li><a href="#how-it-works" class="hover:text-primary-400 transition-colors">{{ __('landing.Nav HowItWorks') }}</a></li>
                        <li><a href="#benefits" class="hover:text-primary-400 transition-colors">{{ __('landing.Benefits Title') }}</a></li>
                        <li><a href="#testimonials" class="hover:text-primary-400 transition-colors">{{ __('landing.Testimonials Title') }}</a></li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm">
                {{ __('landing.Footer Copyright', ['year' => date('Y'), 'app' => app_name()]) }}
            </div>
        </div>
    </footer>

    {{-- Scroll reveal: IntersectionObserver adds .is-visible when sections enter viewport --}}
    <script>
        (function() {
            var reveals = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window) || reveals.length === 0) {
                reveals.forEach(function(el) { el.classList.add('is-visible'); });
                return;
            }
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
            reveals.forEach(function(el) { observer.observe(el); });
        })();
    </script>

    <x-toast />
    <x-font-scale-control />

    {{-- Livewire scripts (Alpine.js bundled in): placed at end of <body> to match the
         placement Livewire's auto-injection uses on all other pages. This keeps the
         script tag's position consistent across pages so wire:navigate's body-script
         hash tracking correctly recognizes it as unchanged and skips re-execution
         (a mismatch here previously caused "Alpine.navigate is not a function" after
         navigating from this page to another page, e.g. clicking Login/Register). --}}
    @livewireScripts
</body>
</html>
