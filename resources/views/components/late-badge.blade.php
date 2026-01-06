@props(['show' => false, 'size' => 'sm', 'tooltip' => 'Submitted after deadline'])

@if($show)
    @php
        $sizes = [
            'xs' => 'text-xs px-1. 5 py-0.5',
            'sm' => 'text-xs px-2 py-0.5',
            'md' => 'text-sm px-2.5 py-1',
            'lg' => 'text-base px-3 py-1',
        ];

        $sizeClass = $sizes[$size] ?? $sizes['sm'];
    @endphp

    <span 
        {{ $attributes->merge(['class' => "inline-flex items-center font-bold rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 {$sizeClass}"]) }}
        title="{{ $tooltip }}"
    >
        <!-- <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2. 828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg> -->
        LATE
    </span>
@endif