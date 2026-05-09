<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
