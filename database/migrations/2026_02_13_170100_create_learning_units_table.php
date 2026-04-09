<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_module_id')->constrained('learning_modules')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->text('content_text');
            $table->string('content_hash');
            $table->json('metadata')->nullable();
            $table->addColumn('vector', 'embedding', ['dimensions' => 1536])->nullable();
            $table->timestamps();

            $table->index(['learning_module_id', 'position']);
            $table->unique(['learning_module_id', 'content_hash']);
        });

        DB::statement(
            'CREATE INDEX IF NOT EXISTS learning_units_embedding_ivfflat
            ON learning_units
            USING ivfflat (embedding vector_cosine_ops)
            WITH (lists = 100)
            WHERE embedding IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS learning_units_embedding_ivfflat');
        Schema::dropIfExists('learning_units');
    }
};

