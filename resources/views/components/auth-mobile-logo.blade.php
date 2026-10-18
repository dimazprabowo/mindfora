{{-- Auth Mobile Logo - Shown on mobile devices --}}
{{-- Text colors: white/primary-100 on mobile (sits on soft green gradient bg from auth-shell) --}}
<div class="lg:hidden flex flex-col items-center mb-8">
    <div class="flex items-center space-x-3 mb-4">
        <div class="w-14 h-14 bg-white/15 backdrop-blur rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97 0-9-3.582-9-8 0-3.866 3-7 6.5-7.5C10 3 11 2 12 2s2 1 2.5 3.5C18 6 21 9.134 21 13c0 4.418-4.03 8-9 8z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V8M9 11l3-3 3 3M9 14l3-3 3 3M9 17l3-3 3 3"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white">{{ app_name() }}</h1>
        </div>
    </div>
</div>
