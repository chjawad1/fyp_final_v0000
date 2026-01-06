<?php

return [
    // Retention window for trashed templates before automatic purge (in days).
    // Can be overridden via env: TEMPLATES_RETENTION_DAYS=45
    'retention_days' => (int) env('TEMPLATES_RETENTION_DAYS', 30),
];