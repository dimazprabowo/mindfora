<x-app-layout title="{{ __('programs.edit_title') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('programs.edit_title') }}
        </h2>
    </x-slot>

    <livewire:programs.test-program-form :program="$program" />
</x-app-layout>
