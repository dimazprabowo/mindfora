{{-- Partial: Dashboard Statistics View (requires dashboard_view permission) --}}
<div class="space-y-6">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ __('dashboard.welcome_admin') }}, {{ $authUser->name }}!</h2>
                <p class="mt-1 text-primary-100 text-sm">
                    {{ app_name() }} &mdash; {{ __('dashboard.admin_panel') }}
                </p>
                <div class="mt-3 inline-flex items-center whitespace-nowrap gap-2 bg-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                    {{ $authUserRole }}
                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-white opacity-20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Stat Cards: User, Company & Role (gated by respective entity permissions) --}}
    @canany(['users_view', 'companies_view', 'roles_view'])
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        @can('users_view')
        {{-- Total Users --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.total_users') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('dashboard.users_registered') }}</p>
                </div>
                <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('companies_view')
        {{-- Total Companies --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.total_companies') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCompanies) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('dashboard.companies_registered') }}</p>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('roles_view')
        {{-- Total Roles --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.total_roles') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRoles) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('dashboard.roles_configured') }}</p>
                </div>
                <div class="p-3 bg-violet-50 dark:bg-violet-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

    </div>
    @endcanany

    {{-- Mindfulness Program Stats --}}
    @canany(['test_programs_view', 'test_assignments_view', 'test_attempts_view', 'training_materials_view'])
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        @can('test_programs_view')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.test_programs') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalPrograms ?? 0 }}</p>
                    <p class="mt-1 text-xs text-primary-600 dark:text-primary-400">{{ $activePrograms ?? 0 }} {{ __('dashboard.active') }}</p>
                </div>
                <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('test_assignments_view')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.scheduling') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAssignments ?? 0 }}</p>
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">{{ $activeAssignmentCount ?? 0 }} {{ __('dashboard.active') }}</p>
                </div>
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('test_attempts_view')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.total_test_submit') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalAttempts ?? 0) }}</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('dashboard.attempt_submitted') }}</p>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

        @can('training_materials_view')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('dashboard.training_materials') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalMaterials ?? 0 }}</p>
                    <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">{{ $publishedMaterials ?? 0 }} {{ __('dashboard.published') }}</p>
                </div>
                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endcan

    </div>
    @endcanany

    {{-- Stress Distribution Chart --}}
    @can('test_attempts_view')
    @if(isset($stressDistribution) && ($stressDistribution['rendah'] + $stressDistribution['sedang'] + $stressDistribution['tinggi']) > 0)
    @php
        $totalStress = $stressDistribution['rendah'] + $stressDistribution['sedang'] + $stressDistribution['tinggi'];
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">{{ __('dashboard.stress_distribution') }}</h3>
        <div class="space-y-3">
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-green-700 dark:text-green-400">{{ __('dashboard.stress_low') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stressDistribution['rendah'] }} ({{ round(($stressDistribution['rendah'] / $totalStress) * 100) }}%)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-green-500 h-2.5 rounded-full transition-all" style="width: {{ ($stressDistribution['rendah'] / $totalStress) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-amber-700 dark:text-amber-400">{{ __('dashboard.stress_medium') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stressDistribution['sedang'] }} ({{ round(($stressDistribution['sedang'] / $totalStress) * 100) }}%)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-amber-500 h-2.5 rounded-full transition-all" style="width: {{ ($stressDistribution['sedang'] / $totalStress) * 100 }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-red-700 dark:text-red-400">{{ __('dashboard.stress_high') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $stressDistribution['tinggi'] }} ({{ round(($stressDistribution['tinggi'] / $totalStress) * 100) }}%)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-red-500 h-2.5 rounded-full transition-all" style="width: {{ ($stressDistribution['tinggi'] / $totalStress) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endcan

    {{-- Quick Actions (only render if user has at least one relevant permission) --}}
    @canany(['users_view', 'roles_view', 'configuration_view', 'companies_view'])
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">{{ __('dashboard.quick_actions') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            @can('users_view')
            <a href="{{ route('settings.users') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-primary-100 dark:bg-primary-900/30 rounded-lg group-hover:bg-primary-200 dark:group-hover:bg-primary-900/50 transition-colors">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.manage_users') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.manage_user_accounts') }}</p>
                </div>
            </a>
            @endcan

            @can('roles_view')
            <a href="{{ route('settings.roles') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-violet-400 dark:hover:border-violet-500 hover:bg-violet-50 dark:hover:bg-violet-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-violet-100 dark:bg-violet-900/30 rounded-lg group-hover:bg-violet-200 dark:group-hover:bg-violet-900/50 transition-colors">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.roles_permissions') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.manage_access_rights') }}</p>
                </div>
            </a>
            @endcan

            @can('configuration_view')
            <a href="{{ route('settings.system') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.system_configuration') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.app_settings') }}</p>
                </div>
            </a>
            @endcan

            @can('companies_view')
            <a href="{{ route('master-data.companies') }}" wire:navigate
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/10 transition-all group">
                <div class="flex-shrink-0 p-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-lg group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition-colors">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.master_data') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.manage_companies') }}</p>
                </div>
            </a>
            @endcan

        </div>
    </div>
    @endcanany

    {{-- System Info (gated by configuration_view â€” internal app info) --}}
    @can('configuration_view')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">{{ __('dashboard.system_info') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Laravel {{ app()->version() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.framework') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">PHP {{ phpversion() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.runtime') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-primary-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('app.env') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Environment</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="flex-shrink-0 w-2 h-2 bg-primary-500 rounded-full"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ config('app.timezone') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Timezone</p>
                </div>
            </div>
        </div>
    </div>
    @endcan

</div>
