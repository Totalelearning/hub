<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_path_steps', function (Blueprint $table) {
            $table->unsignedInteger('delay_days')->default(0)->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('learning_path_steps', function (Blueprint $table) {
            $table->dropColumn('delay_days');
        });
    }
};
