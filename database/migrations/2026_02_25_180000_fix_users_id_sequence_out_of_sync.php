<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sequenceName = DB::scalar("SELECT pg_get_serial_sequence('users', 'id')");

        // If users.id is not backed by a PostgreSQL sequence (unusual/manual schema),
        // there is nothing to sync and this migration should safely no-op.
        if (!$sequenceName) {
            return;
        }

        DB::selectOne(
            'SELECT setval(?, (SELECT COALESCE(MAX(id), 1) FROM users), true)',
            [$sequenceName]
        );
    }

    public function down(): void
    {
        // Sequence position changes are operational state and are not safely reversible.
    }
};
