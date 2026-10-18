<x-app-layout title="{{ __('test_attempts.page_title') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('test_attempts.page_title') }}
        </h2>
    </x-slot>

    <livewire:test-attempts.test-attempt-index />
</x-app-layout>
