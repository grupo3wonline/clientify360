@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs text-danger-600 dark:text-danger-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center"><i class="fa-solid fa-circle-exclamation mr-1 text-[10px]"></i>{{ $message }}</li>
        @endforeach
    </ul>
@endif
