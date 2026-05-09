<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('bi-trophy');
            $table->string('category', 30)->default('achievement'); // achievement, streak, mastery, topic
            $table->string('criteria_type', 50); // courses_completed, perfect_scores, streak_days, topic_courses_completed, total_xp
            $table->integer('criteria_value');
            $table->json('criteria_meta')->nullable(); // e.g. {"topic":"safeguarding"}
            $table->integer('xp_reward')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('criteria_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
