@props(['status' => 'inactive', 'size' => 'sm'])

@php
    $statuses = [
        'upcoming' => ['label' => 'Upcoming', 'color' => 'blue', 'icon' => 'calendar'],
        'active' => ['label' => 'Active', 'color' => 'green', 'icon' => 'play'],
        'ended' => ['label' => 'Ended', 'color' => 'gray', 'icon' => 'stop'],
        'inactive' => ['label' => 'Inactive', 'color' => 'red', 'icon' => 'pause'],
    ];

    $statusData = $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'gray', 'icon' => 'question'];

    $colors = [
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-0.5',
        'sm' => 'text-xs px-2.5 py-0.5',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-1.5',
    ];

    $colorClass = $colors[$statusData['color']] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ??  $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full {$colorClass} {$sizeClass}"]) }}>
    <!-- @if($statusData['icon'] === 'calendar')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'play')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9. 555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'stop')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'pause')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
    @endif -->
    {{ $statusData['label'] }}
</span>