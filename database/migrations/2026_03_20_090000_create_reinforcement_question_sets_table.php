<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reinforcement_question_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->foreignId('learning_asset_id')->nullable()->constrained('learning_assets')->nullOnDelete();
            $table->string('status', 40)->default('draft');
            $table->string('generation_mode', 40)->default('ai_draft');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('draft_source_excerpt')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['learning_module_id', 'status'], 'reinforcement_q_sets_module_status');
        });

        Schema::create('reinforcement_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reinforcement_question_set_id')->constrained('reinforcement_question_sets')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1);
            $table->string('question_type', 40)->default('multiple_choice');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->string('correct_answer', 10)->nullable();
            $table->text('explanation')->nullable();
            $table->foreignId('remediation_learning_module_id')->nullable()->constrained('learning_modules')->nullOnDelete();
            $table->string('status', 40)->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['reinforcement_question_set_id', 'position'], 'reinforcement_questions_set_position');
        });

        Schema::table('reinforcement_touchpoints', function (Blueprint $table) {
            $table->foreignId('reinforcement_question_set_id')
                ->nullable()
                ->after('module_progress_id')
                ->constrained('reinforcement_question_sets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reinforcement_touchpoints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reinforcement_question_set_id');
        });

        Schema::dropIfExists('reinforcement_questions');
        Schema::dropIfExists('reinforcement_question_sets');
    }
};
