<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('learning_modules', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('source_type')->default('manual'); // pdf, doc, manual
            $table->text('source_uri')->nullable();

            $table->longText('content_text')->nullable();

            $table->string('status')->default('draft'); // draft, published, archived

            // Vector embeddings, adjust dimensions later if needed
            $table->addColumn('vector', 'embedding', ['dimensions' => 1536])->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('source_type');
        });

        DB::statement(
            'CREATE INDEX IF NOT EXISTS learning_modules_embedding_ivfflat
             ON learning_modules
             USING ivfflat (embedding vector_cosine_ops)
             WITH (lists = 100)'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS learning_modules_embedding_ivfflat');
        Schema::dropIfExists('learning_modules');
    }
};
