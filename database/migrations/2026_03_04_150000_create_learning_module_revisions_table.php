<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_module_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('change_type', 50);
            $table->string('status', 50);
            $table->json('snapshot');
            $table->timestamps();

            $table->unique(['learning_module_id', 'revision_number']);
            $table->index(['learning_module_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_module_revisions');
    }
};
