@props(['phase' => null, 'size' => 'sm'])

@php
    $phases = [
        'idea' => ['label' => 'Idea Approval', 'color' => 'blue'],
        'scope' => ['label' => 'Scope Approval', 'color' => 'yellow'],
        'defence' => ['label' => 'Defence', 'color' => 'purple'],
        'completed' => ['label' => 'Completed', 'color' => 'green'],
    ];

    $phaseData = $phases[$phase] ?? ['label' => 'Unknown', 'color' => 'gray'];

    $colors = [
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-0.5',
        'sm' => 'text-xs px-2.5 py-0.5',
        'md' => 'text-sm px-3 py-1',
        'lg' => 'text-base px-4 py-1.5',
    ];

    $colorClass = $colors[$phaseData['color']] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-medium rounded-full {$colorClass} {$sizeClass}"]) }}>
    <!-- @if($phase === 'idea')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-. 707.707a1 1 0 001.414 1.414l. 707-. 707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-. 707-. 707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c. 015-. 34.208-.646.477-.859a4 4 0 10-4.954 0c. 27. 213.462.519.476.859h4.002z"/>
        </svg>
    @elseif($phase === 'scope')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4. 586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
        </svg>
    @elseif($phase === 'defence')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
        </svg>
    @elseif($phase === 'completed')
        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
    @endif -->
    {{ $phaseData['label'] }}
</span>