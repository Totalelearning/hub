<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('system_role', 20)->default('learner')->after('is_admin')->index();
            $table->json('managed_teams')->nullable()->after('system_role');
        });

        // Migrate existing is_admin users to site_admin role
        DB::table('users')->where('is_admin', true)->update(['system_role' => 'site_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['system_role']);
            $table->dropColumn(['system_role', 'managed_teams']);
        });
    }
};
