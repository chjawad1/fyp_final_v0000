@props(['status' => 'pending', 'size' => 'sm'])

@php
    $statuses = [
        'pending' => ['label' => 'Pending', 'color' => 'yellow'],
        'approved' => ['label' => 'Approved', 'color' => 'green'],
        'rejected' => ['label' => 'Rejected', 'color' => 'red'],
        'completed' => ['label' => 'Completed', 'color' => 'blue'],
    ];

    $statusData = $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'gray'];

    $colors = [
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-0.5',
        'sm' => 'text-xs px-2.5 py-0.5',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-1.5',
    ];

    $colorClass = $colors[$statusData['color']] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full {$colorClass} {$sizeClass}"]) }}>
    {{ $statusData['label'] }}
</span>