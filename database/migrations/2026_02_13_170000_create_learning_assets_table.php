<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('storage_disk')->default('local');
            $table->text('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status')->default('uploaded');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('learning_module_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_assets');
    }
};

