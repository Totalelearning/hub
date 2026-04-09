<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('difficulty');
            $table->string('compliance_area')->nullable()->after('is_required');
            $table->unsignedInteger('refresh_interval_days')->nullable()->after('compliance_area');

            $table->index('is_required');
            $table->index('compliance_area');
        });
    }

    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropIndex(['is_required']);
            $table->dropIndex(['compliance_area']);
            $table->dropColumn(['is_required', 'compliance_area', 'refresh_interval_days']);
        });
    }
};
