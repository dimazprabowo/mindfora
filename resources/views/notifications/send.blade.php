<x-app-layout title="{{ __('notifications.send_notification') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('notifications.send_notification') }}
        </h2>
    </x-slot>

    <livewire:notifications.send-notification />
</x-app-layout>
