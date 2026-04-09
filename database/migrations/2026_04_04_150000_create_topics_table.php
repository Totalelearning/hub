<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed from existing module topics
        $existing = DB::table('learning_modules')
            ->whereNotNull('topic')
            ->pluck('topic')
            ->filter()
            ->map(fn ($t) => strtolower(trim((string) $t)))
            ->unique()
            ->values();

        foreach ($existing as $i => $topic) {
            DB::table('topics')->insert([
                'slug' => \Illuminate\Support\Str::slug($topic),
                'name' => $topic,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
