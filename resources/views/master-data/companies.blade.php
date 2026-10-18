<x-app-layout title="{{ __('master_data.companies') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('master_data.companies') }}
        </h2>
    </x-slot>

    <livewire:master-data.company-management />
</x-app-layout>
