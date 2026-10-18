<x-app-layout title="{{ __('notifications.title') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('notifications.title') }}
        </h2>
    </x-slot>

    <livewire:notifications.notification-index />
</x-app-layout>
