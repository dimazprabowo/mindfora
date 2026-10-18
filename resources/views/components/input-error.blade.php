@props(['for' => null, 'messages' => null])

@php
    // If "for" is provided, pull messages from the error bag; otherwise use passed "messages"
    if ($for !== null) {
        $messages = $errors->get($for);
    }

    // Normalize to array
    $messages = is_iterable($messages) ? $messages : (array) $messages;
@endphp

@if (! empty($messages))
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ($messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
