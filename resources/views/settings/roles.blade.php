<x-app-layout title="{{ __('settings.roles_permissions') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('settings.roles_permissions') }}
        </h2>
    </x-slot>

    <livewire:settings.role-permission />
</x-app-layout>
