@props([
    'deadline' => null,
    'daysRemaining' => null,
    'isOverdue' => false,
    'daysOverdue' => 0,
    'allowLate' => false,
    'phaseName' => 'Current Phase',
])

@if($deadline)
    @php
        $alertType = 'info';
        $message = '';
        $icon = 'info';

        if ($isOverdue) {
            if ($allowLate) {
                $alertType = 'warning';
                $message = "Deadline passed {$daysOverdue} day(s) ago. Late submissions allowed but will be marked as LATE.";
                $icon = 'warning';
            } else {
                $alertType = 'danger';
                $message = "Deadline passed {$daysOverdue} day(s) ago. Submissions are closed.";
                $icon = 'danger';
            }
        } elseif ($daysRemaining !== null) {
            if ($daysRemaining <= 0) {
                $alertType = 'danger';
                $message = "Today is the deadline for {$phaseName}! ";
                $icon = 'danger';
            } elseif ($daysRemaining <= 3) {
                $alertType = 'warning';
                $message = "Only {$daysRemaining} day(s) remaining until the {$phaseName} deadline! ";
                $icon = 'warning';
            } elseif ($daysRemaining <= 7) {
                $alertType = 'info';
                $message = "{$daysRemaining} days remaining until the {$phaseName} deadline.";
                $icon = 'info';
            } else {
                $alertType = 'success';
                $message = "Deadline:  " . $deadline->format('M d, Y') . " ({$daysRemaining} days remaining)";
                $icon = 'success';
            }
        }

        $alertClasses = [
            'info' => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/50 dark:border-blue-800 dark:text-blue-300',
            'success' => 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/50 dark:border-green-800 dark:text-green-300',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/50 dark:border-yellow-800 dark:text-yellow-300',
            'danger' => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-800 dark:text-red-300',
        ];

        $alertClass = $alertClasses[$alertType] ?? $alertClasses['info'];
    @endphp

    <div {{ $attributes->merge(['class' => "flex items-center p-4 border rounded-lg {$alertClass}"]) }}>
        <div class="flex-1">
            <p class="text-sm font-medium">{{ $message }}</p>
            @if($deadline && ! $isOverdue)
                <p class="text-xs mt-1 opacity-75">Deadline: {{ $deadline->format('l, F d, Y') }}</p>
            @endif
        </div>

        @if($isOverdue && $allowLate)
            <span class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200">
                LATE OK
            </span>
        @elseif($isOverdue && !$allowLate)
            <span class="ml-3 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-200">
                CLOSED
            </span>
        @endif
    </div>
@endif