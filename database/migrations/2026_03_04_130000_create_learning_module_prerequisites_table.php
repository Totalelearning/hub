<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_module_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->foreignId('prerequisite_learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['learning_module_id', 'prerequisite_learning_module_id'], 'learning_module_prereq_unique');
            $table->index('learning_module_id');
            $table->index('prerequisite_learning_module_id', 'learning_module_prereq_prereq_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_module_prerequisites');
    }
};
