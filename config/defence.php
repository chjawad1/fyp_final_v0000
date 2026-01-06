<?php

return [
    // Maximum allowed consecutive sessions per evaluator.
    'consecutive_limit' => env('DEFENCE_CONSECUTIVE_LIMIT', 1),

    // Two sessions are consecutive if the gap between their start times
    // is <= this many minutes. Set to 0 for strict back-to-back.
    'consecutive_break_minutes' => env('DEFENCE_CONSECUTIVE_BREAK_MINUTES', 30),
];