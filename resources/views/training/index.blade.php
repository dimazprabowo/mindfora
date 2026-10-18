<x-app-layout title="{{ __('menu.training') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('menu.training') }}
        </h2>
    </x-slot>

    <livewire:training.training-library />
</x-app-layout>
