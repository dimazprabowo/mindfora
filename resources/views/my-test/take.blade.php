<x-app-layout title="{{ __('my_test.take_test') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('my_test.take_test') }}
        </h2>
    </x-slot>

    <livewire:my-test.take-test :week="$week" />
</x-app-layout>
