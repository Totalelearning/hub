<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_role_rules', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('compliance_area');
            $table->timestamps();

            $table->unique(['role', 'compliance_area']);
            $table->index('role');
            $table->index('compliance_area');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_role_rules');
    }
};
