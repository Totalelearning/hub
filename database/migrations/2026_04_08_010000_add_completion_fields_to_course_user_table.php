<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->string('status', 30)->default('assigned')->after('user_id');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->timestamp('reinforcement_sent_at')->nullable()->after('completed_at');
            $table->string('reinforcement_status', 30)->nullable()->after('reinforcement_sent_at');

            $table->index(['status', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->dropIndex(['status', 'completed_at']);
            $table->dropColumn(['status', 'completed_at', 'reinforcement_sent_at', 'reinforcement_status']);
        });
    }
};
