<x-app-layout title="{{ __('menu.history') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('history.test_history') }}
        </h2>
    </x-slot>

    <livewire:history.history-index />
</x-app-layout>
