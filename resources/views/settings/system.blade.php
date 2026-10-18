<x-app-layout title="{{ __('settings.system_configuration') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('settings.system_configuration') }}
        </h2>
    </x-slot>

    <livewire:settings.system-configuration />
</x-app-layout>
