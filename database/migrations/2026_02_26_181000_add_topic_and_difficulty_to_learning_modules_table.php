<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('description');
            $table->string('difficulty')->nullable()->after('topic');
            $table->index('topic');
            $table->index('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('learning_modules', function (Blueprint $table) {
            $table->dropIndex(['topic']);
            $table->dropIndex(['difficulty']);
            $table->dropColumn(['topic', 'difficulty']);
        });
    }
};

