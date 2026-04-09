<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedScoringSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }
}
