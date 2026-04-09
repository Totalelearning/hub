<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reinforcement_touchpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->foreignId('module_progress_id')->nullable()->constrained('module_progress')->nullOnDelete();
            $table->string('touchpoint_key', 120)->unique();
            $table->unsignedInteger('interval_days');
            $table->string('title');
            $table->text('prompt');
            $table->string('proof_type', 50)->default('knowledge_check');
            $table->date('due_on');
            $table->string('status', 30)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->string('proof_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'due_on'], 'reinforcement_touchpoints_user_status_due_on');
            $table->index(['learning_module_id', 'status'], 'reinforcement_touchpoints_module_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reinforcement_touchpoints');
    }
};
