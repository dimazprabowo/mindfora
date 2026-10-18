<x-app-layout title="{{ __('master_data.departments') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('master_data.department_management') }}
        </h2>
    </x-slot>

    <livewire:master-data.department-management />
</x-app-layout>
