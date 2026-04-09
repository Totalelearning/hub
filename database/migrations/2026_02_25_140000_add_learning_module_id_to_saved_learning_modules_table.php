<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Ensure the column exists (if it doesn't, add it safely)
        if (!Schema::hasColumn('saved_learning_modules', 'learning_module_id')) {
            Schema::table('saved_learning_modules', function (Blueprint $table) {
                // Temporary default lets the column be created without failing on existing rows.
                $table->unsignedBigInteger('learning_module_id')->default(0);
            });

            // Existing rows from the old schema cannot be mapped to a module id reliably.
            DB::table('saved_learning_modules')->where('learning_module_id', 0)->delete();

            // Drop the temporary default to avoid masking future bugs.
            DB::statement('ALTER TABLE saved_learning_modules ALTER COLUMN learning_module_id DROP DEFAULT');
        }

        // 2) Normalize any manually-added column state
        // Delete invalid rows that cannot be reliably mapped
        DB::table('saved_learning_modules')->whereNull('learning_module_id')->delete();
        DB::table('saved_learning_modules')->where('learning_module_id', 0)->delete();

        // Enforce type + NOT NULL at the database level (handles manual schema drift)
        DB::statement('ALTER TABLE saved_learning_modules ALTER COLUMN learning_module_id TYPE bigint USING learning_module_id::bigint');
        DB::statement('ALTER TABLE saved_learning_modules ALTER COLUMN learning_module_id SET NOT NULL');

        // 3) Ensure unique index exists
        if (!$this->hasIndex('saved_learning_modules', 'saved_learning_modules_learning_module_id_unique')) {
            Schema::table('saved_learning_modules', function (Blueprint $table) {
                $table->unique('learning_module_id', 'saved_learning_modules_learning_module_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('saved_learning_modules', 'learning_module_id')) {
            return;
        }

        Schema::table('saved_learning_modules', function (Blueprint $table) {
            if ($this->hasIndex('saved_learning_modules', 'saved_learning_modules_learning_module_id_unique')) {
                $table->dropUnique('saved_learning_modules_learning_module_id_unique');
            }
            $table->dropColumn('learning_module_id');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::selectOne(
            'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ? LIMIT 1',
            [$table, $indexName]
        );

        return $result !== null;
    }
};
