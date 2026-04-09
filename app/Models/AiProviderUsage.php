<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProviderUsage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'provider',
        'capability',
        'model',
        'request_id',
        'input_tokens_est',
        'output_tokens_est',
        'latency_ms',
        'success',
        'error_type',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'success' => 'boolean',
        'metadata' => 'array',
    ];
}

