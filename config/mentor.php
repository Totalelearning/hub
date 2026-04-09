<?php

return [
    'enabled' => filter_var(env('MENTOR_PROVIDER_ENABLED', true), FILTER_VALIDATE_BOOL),
    'provider' => env('MENTOR_PROVIDER', 'local'),
];

