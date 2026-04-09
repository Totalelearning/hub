<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_retrieval_traces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mentor_message_id')->constrained('mentor_messages')->cascadeOnDelete();
            $table->text('query_text');
            $table->json('retrieved_unit_ids');
            $table->json('retrieval_scores');
            $table->string('retrieval_strategy')->default('vector_cosine');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_retrieval_traces');
    }
};

