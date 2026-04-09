<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAsset extends Model
{
    protected $fillable = [
        'learning_module_id',
        'asset_type',
        'original_filename',
        'storage_disk',
        'storage_path',
        'extracted_disk',
        'extracted_path',
        'launch_path',
        'mime_type',
        'size_bytes',
        'status',
        'manifest',
        'processing_metadata',
        'error_message',
    ];

    protected $casts = [
        'manifest' => 'array',
        'processing_metadata' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }
}
