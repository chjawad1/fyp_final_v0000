@props(['status' => 'pending', 'size' => 'sm'])

@php
    $statuses = [
        'pending' => ['label' => 'Pending Review', 'color' => 'yellow', 'icon' => 'clock'],
        'approved' => ['label' => 'Approved', 'color' => 'green', 'icon' => 'check'],
        'rejected' => ['label' => 'Rejected', 'color' => 'red', 'icon' => 'x'],
        'revision_required' => ['label' => 'Revision Required', 'color' => 'orange', 'icon' => 'refresh'],
    ];

    $statusData = $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'gray', 'icon' => 'question'];

    $colors = [
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-0.5',
        'sm' => 'text-xs px-2.5 py-0.5',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-1.5',
    ];

    $colorClass = $colors[$statusData['color']] ??  $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full {$colorClass} {$sizeClass}"]) }}>
    <!-- @if($statusData['icon'] === 'clock')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'check')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16. 707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'x')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
    @elseif($statusData['icon'] === 'refresh')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2. 101a7. 002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005. 999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm. 008 9.057a1 1 0 011.276. 61A5.002 5.002 0 0014. 001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
        </svg>
    @else
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-. 867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
        </svg>
    @endif -->
    {{ $statusData['label'] }}
</span>