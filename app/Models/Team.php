<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['slug', 'name', 'sort_order'];

    public static function optionsWithAll(): array
    {
        return ['all' => 'All Teams'] + static::ordered()->pluck('name', 'slug')->all();
    }

    public static function options(): array
    {
        return static::ordered()->pluck('name', 'slug')->all();
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
