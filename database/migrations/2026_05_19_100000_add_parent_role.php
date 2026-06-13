<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'Parent' to the roles table so it appears in course target_roles
        $maxSort = DB::table('roles')->max('sort_order') ?? 0;

        DB::table('roles')->insertOrIgnore([
            'slug' => 'parent',
            'name' => 'Parent',
            'sort_order' => $maxSort + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'parent')->delete();
    }
};
