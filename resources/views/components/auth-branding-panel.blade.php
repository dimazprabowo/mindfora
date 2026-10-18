{{--
    Auth Branding Panel - Left/right side panel for guest auth pages.

    Mindfulness context: 4-week mindfulness program for stress management.
    Visual language: organic, calming — soft gradient, gentle wave shapes,
    lotus illustration, and a mindfulness quote to set a peaceful tone.

    Uses currentColor so the illustration inherits the white/primary-100
    text color of the green gradient panel. Width is fixed for desktop layout.
--}}
<div class="hidden lg:flex lg:w-1/2 xl:w-2/5 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 dark:from-primary-700 dark:via-primary-800 dark:to-gray-900 p-8 lg:p-12 flex-col justify-between relative overflow-hidden text-white">

    {{-- Organic wave shapes overlay (calm/breath) --}}
    <div class="absolute inset-0 opacity-[0.12] pointer-events-none" aria-hidden="true">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            {{-- Top wave --}}
            <path d="M 0 80 Q 25% 40 50% 80 T 100% 80 L 100% 0 L 0 0 Z" fill="currentColor" opacity="0.4"/>
            {{-- Middle soft wave --}}
            <path d="M 0 200 Q 30% 170 60% 210 T 100% 200 L 100% 240 L 0 240 Z" fill="currentColor" opacity="0.25"/>
            {{-- Bottom wave --}}
            <path d="M 0 320 Q 25% 280 50% 310 T 100% 300 L 100% 360 L 0 360 Z" fill="currentColor" opacity="0.3"/>
        </svg>
    </div>

    {{-- Soft radial glow (center focus — meditation) --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[28rem] h-[28rem] rounded-full bg-white/5 blur-3xl pointer-events-none" aria-hidden="true"></div>

    {{-- Top accent line (soft) --}}
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-primary-300/40 to-transparent"></div>

    {{-- Content --}}
    <div class="relative z-10 flex flex-col h-full justify-between">

        {{-- Top Section - Logo & Title --}}
        <div>
            <div class="flex items-center space-x-3 mb-10">
                <div class="w-14 h-14 bg-white/15 backdrop-blur rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V8M9 11l3-3 3 3M9 14l3-3 3 3M9 17l3-3 3 3"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold leading-tight">{{ app_name() }}</h1>
                </div>
            </div>

            {{-- Main headline --}}
            <div class="space-y-5 max-w-lg mb-8">
                <h2 class="text-3xl lg:text-4xl xl:text-[2.75rem] font-bold leading-tight">
                    {{ __('auth.enterprise_platform_line1') }}<br>{{ __('auth.enterprise_platform_line2') }}
                </h2>
                <p class="text-base lg:text-lg text-primary-100 leading-relaxed">
                    {{ __('auth.branding_description') }}
                </p>
                {{-- Mindfulness quote (italic, soft) --}}
                <blockquote class="border-l-2 border-primary-300/50 pl-4 py-1 italic text-primary-100/90 text-sm lg:text-base leading-relaxed">
                    {{ __('auth.mindfulness_quote') }}
                </blockquote>
            </div>
        </div>

        {{-- Middle Section - Mindfulness Illustration --}}
        <div class="my-6 text-primary-50">
            <x-auth-platform-illustration />
        </div>

        {{-- Bottom Section - Key Capabilities (mindfulness features) --}}
        <div class="space-y-3">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-9 h-9 bg-white/15 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">{{ __('auth.feature_auth_title') }}</h3>
                    <p class="text-xs text-primary-200">{{ __('auth.feature_auth_desc') }}</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-9 h-9 bg-white/15 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">{{ __('auth.feature_rbac_title') }}</h3>
                    <p class="text-xs text-primary-200">{{ __('auth.feature_rbac_desc') }}</p>
                </div>
            </div>

            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-9 h-9 bg-white/15 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm text-white">{{ __('auth.feature_management_title') }}</h3>
                    <p class="text-xs text-primary-200">{{ __('auth.feature_management_desc') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="relative z-10 text-primary-200/80 text-xs mt-6">
        <p>&copy; {{ date('Y') }} {{ app_name() }}</p>
    </div>
</div>
