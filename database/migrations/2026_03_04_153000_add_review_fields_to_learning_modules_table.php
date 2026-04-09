<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->foreignId('owner_user_id')->nullable()->after('target_roles')->constrained('users')->nullOnDelete();
            $table->string('review_status', 30)->default('draft')->after('requires_acknowledgement');
            $table->foreignId('approved_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['review_status', 'approved_at']);
        });
    }
};
