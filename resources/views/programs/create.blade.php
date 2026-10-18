<x-app-layout title="{{ __('programs.create_title') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('programs.create_title') }}
        </h2>
    </x-slot>

    <livewire:programs.test-program-form />
</x-app-layout>
