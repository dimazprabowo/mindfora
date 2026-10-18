<x-app-layout title="{{ __('menu.consultation') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('menu.consultation') }}
        </h2>
    </x-slot>

    <livewire:consultation.consultation-index />
</x-app-layout>
