<x-app-layout title="{{ __('menu.training_materials') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('menu.training_materials') }}
        </h2>
    </x-slot>

    <livewire:training.training-manage />
</x-app-layout>
