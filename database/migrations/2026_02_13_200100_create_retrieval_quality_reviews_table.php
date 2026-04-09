<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retrieval_quality_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mentor_message_id')->constrained('mentor_messages')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->smallInteger('rating')->nullable();
            $table->json('flags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('mentor_message_id');
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retrieval_quality_reviews');
    }
};

