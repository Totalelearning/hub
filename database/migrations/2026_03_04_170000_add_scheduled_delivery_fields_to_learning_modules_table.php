<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->timestamp('available_from')->nullable()->after('refresh_interval_days');
            $table->timestamp('available_until')->nullable()->after('available_from');

            $table->index('available_from');
            $table->index('available_until');
        });
    }

    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropIndex(['available_from']);
            $table->dropIndex(['available_until']);
            $table->dropColumn(['available_from', 'available_until']);
        });
    }
};

