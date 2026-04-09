<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_usages', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('capability');
            $table->string('model')->nullable();
            $table->string('request_id')->nullable();
            $table->unsignedInteger('input_tokens_est')->nullable();
            $table->unsignedInteger('output_tokens_est')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('success');
            $table->string('error_type')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['provider', 'capability']);
            $table->index('success');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_usages');
    }
};

