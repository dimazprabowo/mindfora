<x-app-layout title="{{ __('assignments.page_title') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('assignments.page_title') }}
        </h2>
    </x-slot>

    <livewire:assignments.test-program-assignment-index />
</x-app-layout>
