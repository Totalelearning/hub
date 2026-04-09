<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('topic')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('status')->default('draft');
            $table->integer('estimated_minutes')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('course_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_module_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'learning_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_module');
        Schema::dropIfExists('courses');
    }
};
