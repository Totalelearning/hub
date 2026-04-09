<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reinforcement_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_reinforcement_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reinforcement_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('selected_answer', 10)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['course_reinforcement_attempt_id', 'reinforcement_question_id'], 'cra_rq_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_reinforcement_responses');
    }
};
