<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TABLE = 'saved_learning_modules';
    private const USERS_TABLE = 'users';
    private const DEMO_USER_ID = 1;

    private const LEGACY_UNIQUE_NAME = 'saved_learning_modules_learning_module_id_unique';
    private const COMPOSITE_UNIQUE_NAME = 'saved_learning_modules_user_id_learning_module_id_unique';
    private const USER_ID_INDEX_NAME = 'saved_learning_modules_user_id_index';
    private const USER_ID_FK_NAME = 'saved_learning_modules_user_id_foreign';
    private const LEGACY_MARKER_CONSTRAINT = 'saved_learning_modules_had_lm_unique_before_user_scope_chk';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasTable(self::USERS_TABLE)) {
            return;
        }

        $this->ensureDemoUserExists();
        $this->syncUsersIdSequence();

        if (!Schema::hasColumn(self::TABLE, 'user_id')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                // Temporary default allows safe backfill on existing rows.
                $table->unsignedBigInteger('user_id')->default(self::DEMO_USER_ID);
            });
        }

        // Normalize user_id state for partially edited schemas.
        DB::statement('ALTER TABLE ' . self::TABLE . ' ALTER COLUMN user_id TYPE bigint USING user_id::bigint');
        DB::table(self::TABLE)
            ->whereNull('user_id')
            ->orWhere('user_id', 0)
            ->update(['user_id' => self::DEMO_USER_ID]);
        DB::statement('ALTER TABLE ' . self::TABLE . ' ALTER COLUMN user_id SET NOT NULL');
        DB::statement('ALTER TABLE ' . self::TABLE . ' ALTER COLUMN user_id DROP DEFAULT');

        if (Schema::hasColumn(self::TABLE, 'learning_module_id')) {
            // Remove invalid rows first.
            DB::table(self::TABLE)->whereNull('learning_module_id')->delete();
            DB::table(self::TABLE)->where('learning_module_id', 0)->delete();

            // De-duplicate on (user_id, learning_module_id), keep newest id.
            $duplicateRowsToDelete = (int) DB::scalar("
                SELECT COALESCE(SUM(group_count - 1), 0)
                FROM (
                    SELECT user_id, learning_module_id, COUNT(*) AS group_count
                    FROM " . self::TABLE . "
                    WHERE learning_module_id IS NOT NULL AND learning_module_id <> 0
                    GROUP BY user_id, learning_module_id
                    HAVING COUNT(*) > 1
                ) d
            ");

            DB::statement("DO $$ BEGIN RAISE NOTICE 'saved_learning_modules duplicates to delete before composite uniqueness: {$duplicateRowsToDelete}'; END $$;");

            if ($duplicateRowsToDelete > 0) {
                DB::statement("
                    DELETE FROM " . self::TABLE . " older
                    USING " . self::TABLE . " newer
                    WHERE older.user_id = newer.user_id
                      AND older.learning_module_id = newer.learning_module_id
                      AND older.id < newer.id
                ");
            }
        }

        $hadLegacyUnique = $this->hasLegacyLearningModuleUniqueConstraint() || $this->hasLegacyLearningModuleUniqueStandaloneIndex();

        if ($hadLegacyUnique && !$this->hasConstraint(self::LEGACY_MARKER_CONSTRAINT)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' ADD CONSTRAINT ' . self::LEGACY_MARKER_CONSTRAINT . ' CHECK (TRUE)');
        }

        // Remove legacy uniqueness on learning_module_id only.
        foreach ($this->legacyLearningModuleUniqueConstraintNames() as $constraintName) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP CONSTRAINT IF EXISTS ' . $this->quoteIdentifier($constraintName));
        }

        foreach ($this->legacyLearningModuleUniqueStandaloneIndexNames() as $indexName) {
            DB::statement('DROP INDEX IF EXISTS ' . $this->quoteIdentifier($indexName));
        }

        if (!$this->hasCompositeUniqueConstraint()) {
            DB::statement(
                'ALTER TABLE ' . self::TABLE .
                ' ADD CONSTRAINT ' . self::COMPOSITE_UNIQUE_NAME .
                ' UNIQUE (user_id, learning_module_id)'
            );
        }

        if (!$this->hasForeignKeyOnUserId()) {
            DB::statement(
                'ALTER TABLE ' . self::TABLE .
                ' ADD CONSTRAINT ' . self::USER_ID_FK_NAME .
                ' FOREIGN KEY (user_id) REFERENCES ' . self::USERS_TABLE . '(id) ON DELETE CASCADE'
            );
        }

        if (!$this->hasIndex(self::USER_ID_INDEX_NAME)) {
            DB::statement(
                'CREATE INDEX ' . self::USER_ID_INDEX_NAME .
                ' ON ' . self::TABLE . ' (user_id)'
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE)) {
            return;
        }

        // Drop FK if exists
        if ($this->hasConstraint(self::USER_ID_FK_NAME)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP CONSTRAINT IF EXISTS ' . self::USER_ID_FK_NAME);
        }

        // Drop composite unique if exists
        foreach ($this->compositeUniqueConstraintNames() as $constraintName) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP CONSTRAINT IF EXISTS ' . $this->quoteIdentifier($constraintName));
        }

        // Drop user_id index if exists
        if ($this->hasIndex(self::USER_ID_INDEX_NAME)) {
            DB::statement('DROP INDEX IF EXISTS ' . self::USER_ID_INDEX_NAME);
        }

        $hadLegacyUniqueBeforeUserScope = $this->hasConstraint(self::LEGACY_MARKER_CONSTRAINT);

        // Restore old unique on learning_module_id if it existed before this migration.
        if ($hadLegacyUniqueBeforeUserScope && Schema::hasColumn(self::TABLE, 'learning_module_id')) {
            $duplicateRowsToDelete = (int) DB::scalar("
                SELECT COALESCE(SUM(group_count - 1), 0)
                FROM (
                    SELECT learning_module_id, COUNT(*) AS group_count
                    FROM " . self::TABLE . "
                    WHERE learning_module_id IS NOT NULL AND learning_module_id <> 0
                    GROUP BY learning_module_id
                    HAVING COUNT(*) > 1
                ) d
            ");

            DB::statement("DO $$ BEGIN RAISE NOTICE 'saved_learning_modules duplicates to delete before restoring legacy uniqueness: {$duplicateRowsToDelete}'; END $$;");

            if ($duplicateRowsToDelete > 0) {
                DB::statement("
                    DELETE FROM " . self::TABLE . " older
                    USING " . self::TABLE . " newer
                    WHERE older.learning_module_id = newer.learning_module_id
                      AND older.id < newer.id
                ");
            }

            if (!$this->hasLegacyLearningModuleUniqueConstraint() && !$this->hasLegacyLearningModuleUniqueStandaloneIndex()) {
                DB::statement(
                    'ALTER TABLE ' . self::TABLE .
                    ' ADD CONSTRAINT ' . self::LEGACY_UNIQUE_NAME .
                    ' UNIQUE (learning_module_id)'
                );
            }
        }

        if ($this->hasConstraint(self::LEGACY_MARKER_CONSTRAINT)) {
            DB::statement('ALTER TABLE ' . self::TABLE . ' DROP CONSTRAINT IF EXISTS ' . self::LEGACY_MARKER_CONSTRAINT);
        }

        if (Schema::hasColumn(self::TABLE, 'user_id')) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    private function ensureDemoUserExists(): void
    {
        $exists = DB::table(self::USERS_TABLE)
            ->where('id', self::DEMO_USER_ID)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table(self::USERS_TABLE)->insert([
            'id' => self::DEMO_USER_ID,
            'name' => 'Demo User',
            'email' => 'demo@totalelearning.local',
            'password' => Hash::make(Str::uuid()->toString() . Str::random(40)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function syncUsersIdSequence(): void
    {
        $sequenceName = DB::scalar("SELECT pg_get_serial_sequence('" . self::USERS_TABLE . "', 'id')");

        if (!$sequenceName) {
            return;
        }

        DB::statement(
            "SELECT setval(?, GREATEST((SELECT COALESCE(MAX(id), 1) FROM " . self::USERS_TABLE . "), 1), true)",
            [$sequenceName]
        );
    }

    private function hasConstraint(string $constraintName): bool
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.conname', $constraintName)
            ->exists();
    }

    private function hasIndex(string $indexName): bool
    {
        return DB::table('pg_indexes')
            ->where('schemaname', DB::raw('current_schema()'))
            ->where('tablename', self::TABLE)
            ->where('indexname', $indexName)
            ->exists();
    }

    private function hasLegacyLearningModuleUniqueConstraint(): bool
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.contype', 'u')
            ->whereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (learning_module_id)'")
            ->exists();
    }

    private function hasLegacyLearningModuleUniqueStandaloneIndex(): bool
    {
        return count($this->legacyLearningModuleUniqueStandaloneIndexNames()) > 0;
    }

    private function hasCompositeUniqueConstraint(): bool
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.contype', 'u')
            ->where(function ($query) {
                $query
                    ->whereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (user_id, learning_module_id)'")
                    ->orWhereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (learning_module_id, user_id)'");
            })
            ->exists();
    }

    private function hasForeignKeyOnUserId(): bool
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.contype', 'f')
            ->whereRaw("pg_get_constraintdef(c.oid) LIKE 'FOREIGN KEY (user_id) REFERENCES users(id)%'")
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    private function legacyLearningModuleUniqueConstraintNames(): array
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.contype', 'u')
            ->whereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (learning_module_id)'")
            ->pluck('c.conname')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function compositeUniqueConstraintNames(): array
    {
        return DB::table('pg_constraint as c')
            ->join('pg_class as t', 't.oid', '=', 'c.conrelid')
            ->join('pg_namespace as n', 'n.oid', '=', 't.relnamespace')
            ->whereRaw('n.nspname = current_schema()')
            ->where('t.relname', self::TABLE)
            ->where('c.contype', 'u')
            ->where(function ($query) {
                $query
                    ->whereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (user_id, learning_module_id)'")
                    ->orWhereRaw("pg_get_constraintdef(c.oid) = 'UNIQUE (learning_module_id, user_id)'");
            })
            ->pluck('c.conname')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function legacyLearningModuleUniqueStandaloneIndexNames(): array
    {
        return array_map(
            fn ($row) => (string) $row->index_name,
            DB::select("
                SELECT idx.relname AS index_name
                FROM pg_class tbl
                JOIN pg_namespace ns ON ns.oid = tbl.relnamespace
                JOIN pg_index i ON i.indrelid = tbl.oid
                JOIN pg_class idx ON idx.oid = i.indexrelid
                JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS keycols(attnum, ord) ON TRUE
                JOIN pg_attribute a ON a.attrelid = tbl.oid AND a.attnum = keycols.attnum
                WHERE ns.nspname = current_schema()
                  AND tbl.relname = ?
                  AND i.indisunique = TRUE
                  AND i.indisprimary = FALSE
                  AND NOT EXISTS (
                      SELECT 1
                      FROM pg_constraint c
                      WHERE c.conindid = idx.oid
                  )
                GROUP BY idx.relname, i.indnatts
                HAVING i.indnatts = 1
                   AND array_agg(a.attname::text ORDER BY keycols.ord) = ARRAY['learning_module_id']::text[]
            ", [self::TABLE])
        );
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
};
