<x-app-layout title="{{ __('settings.user_management') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('settings.user_management') }}
        </h2>
    </x-slot>

    <livewire:settings.user-management />
</x-app-layout>
