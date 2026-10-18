{{-- Partial: Dashboard for Peserta (user with test_attempts_view_own permission) --}}
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ __('dashboard.hello') }}, {{ $authUser->name }}!</h2>
                <p class="mt-1 text-primary-100 text-sm">
                    {{ config('app.name') }} &mdash; {{ __('dashboard.manage_stress_wellbeing') }}
                </p>
                <div class="mt-3 inline-flex items-center whitespace-nowrap gap-2 bg-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                    {{ $authUserRole }}
                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-white opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 12l3.75-3.75M12 12l-3.75 3.75M12 12h7.5" />
                </svg>
            </div>
        </div>
    </div>

    @if($activeAssignment)
        {{-- Current Program Progress --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.active_program') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activeAssignment->program->name }}</p>
                </div>
                <a href="{{ route('my-test.index') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    {{ __('dashboard.view_detail') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Week progress bar --}}
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                    <span>{{ __('dashboard.progress_week_of', ['current' => $currentWeek ?? 0, 'total' => 4]) }}</span>
                    <span>{{ $completedWeeks ?? 0 }}/4 {{ __('dashboard.completed') }}</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all" style="width: {{ (($completedWeeks ?? 0) / 4) * 100 }}%"></div>
                </div>
            </div>

            {{-- Week dots --}}
            <div class="grid grid-cols-4 gap-2">
                @foreach([1, 2, 3, 4] as $weekNum)
                    @php
                        $status = collect($weekStatuses ?? [])->firstWhere('week.week_number', $weekNum);
                        $isCompleted = $status && $status['best_score'] !== null;
                        $isCurrent = $status && $status['is_current'];
                    @endphp
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center font-bold text-sm
                            {{ $isCompleted ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-primary-600 text-white ring-2 ring-primary-200 dark:ring-primary-900' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($isCompleted)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $weekNum }}
                            @endif
                        </div>
                        <p class="mt-1 text-xs {{ $isCurrent ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-400 dark:text-gray-500' }}">{{ __('dashboard.week') }} {{ $weekNum }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Action Cards (4 cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- My Test --}}
            <a href="{{ route('my-test.index') }}" wire:navigate
               class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-primary-100 dark:bg-primary-900/30 rounded-lg group-hover:bg-primary-200 dark:group-hover:bg-primary-900/50 transition-colors flex-shrink-0">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('menu.my_test') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.take_weekly_test') }}</p>
                    </div>
                </div>
            </a>

            {{-- Training Materials --}}
            @can('training_materials_view_public')
            <a href="{{ route('training.index') }}" wire:navigate
               class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-purple-300 dark:hover:border-purple-700 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 transition-colors flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('menu.training_materials') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.audio_video_therapy') }}</p>
                    </div>
                </div>
            </a>
            @endcan

            {{-- History --}}
            <a href="{{ route('history.index') }}" wire:navigate
               class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('menu.history') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.view_trend_score') }}</p>
                    </div>
                </div>
            </a>

            {{-- Consultation --}}
            <a href="{{ route('consultation.index') }}" wire:navigate
               class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md hover:border-green-300 dark:hover:border-green-700 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-green-100 dark:bg-green-900/30 rounded-lg group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('menu.consultation') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.chat_with_counselor') }}</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Latest Training Materials Preview --}}
        @can('training_materials_view_public')
        @if(isset($latestMaterials) && $latestMaterials->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.latest_materials') }}</h3>
                <a href="{{ route('training.index') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    {{ __('dashboard.view_all') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($latestMaterials as $material)
                    <a href="{{ route('training.index') }}" wire:navigate
                       class="group rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all">
                        {{-- Thumbnail --}}
                        <div class="aspect-video flex items-center justify-center overflow-hidden
                            @if($material->is_thumbnail_ready)
                                bg-gray-100 dark:bg-gray-700
                            @else
                                bg-gradient-to-br {{ $material->type->value === 'audio' ? 'from-blue-500 to-blue-700' : 'from-purple-500 to-purple-700' }}
                            @endif">
                            @if($material->is_thumbnail_ready)
                                <img src="{{ route('training.thumbnail', $material) }}" alt="{{ $material->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @elseif($material->type->value === 'audio')
                                <svg class="h-10 w-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/></svg>
                            @else
                                <svg class="h-10 w-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        {{-- Info --}}
                        <div class="p-3">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="px-1.5 py-0.5 text-[10px] font-medium rounded {{ $material->type->badgeClass() }}">
                                    {{ $material->type->label() }}
                                </span>
                                @if($material->category)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ $material->category }}</span>
                                @endif
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $material->title }}
                            </h4>
                            @if($material->description)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $material->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
        @endcan
    @else
        {{-- No active assignment --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('dashboard.no_active_program') }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('dashboard.no_active_program_desc') }}
            </p>
        </div>
    @endif

</div>
