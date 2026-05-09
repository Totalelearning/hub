<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $locations = [
            'oakwood_primary'     => 'Oakwood Primary Academy',
            'riverside_secondary' => 'Riverside Secondary Academy',
            'hillcrest_primary'   => 'Hillcrest Primary Academy',
            'parklands_secondary' => 'Parklands Secondary Academy',
            'central_hub'         => 'Central Hub (Trust Office)',
            'meadowfield_primary' => 'Meadowfield Primary Academy',
        ];

        foreach (array_values($locations) as $i => $name) {
            $slug = array_keys($locations)[$i];

            DB::table('locations')->insertOrIgnore([
                'slug' => $slug,
                'name' => $name,
                'sort_order' => $i,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('locations')->whereIn('slug', [
            'oakwood_primary', 'riverside_secondary', 'hillcrest_primary',
            'parklands_secondary', 'central_hub', 'meadowfield_primary',
        ])->delete();
    }
};
