<?php

return [
    'ai_ops_retention_days' => (int) env('AI_OPS_RETENTION_DAYS', 30),
    'learning_events_retention_days' => (int) env('LEARNING_EVENTS_RETENTION_DAYS', 90),
    'mentor_traces_retention_days' => (int) env('MENTOR_TRACES_RETENTION_DAYS', 90),
    'assignment_audit_retention_days' => (int) env('ASSIGNMENT_AUDIT_RETENTION_DAYS', 180),
    'assignment_reminders_retention_days' => (int) env('ASSIGNMENT_REMINDERS_RETENTION_DAYS', 180),
];
