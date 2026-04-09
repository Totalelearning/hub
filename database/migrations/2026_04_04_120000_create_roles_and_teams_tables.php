<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed existing hardcoded roles
        $roles = [
            ['slug' => 'headteacher_principal', 'name' => 'Headteacher / Principal', 'sort_order' => 1],
            ['slug' => 'deputy_headteacher', 'name' => 'Deputy Headteacher', 'sort_order' => 2],
            ['slug' => 'assistant_headteacher', 'name' => 'Assistant Headteacher', 'sort_order' => 3],
            ['slug' => 'business_manager', 'name' => 'Business Manager', 'sort_order' => 4],
            ['slug' => 'senco', 'name' => 'SENCO', 'sort_order' => 5],
            ['slug' => 'classroom_teacher', 'name' => 'Classroom Teacher', 'sort_order' => 6],
            ['slug' => 'subject_lead', 'name' => 'Subject Lead', 'sort_order' => 7],
            ['slug' => 'ect', 'name' => 'Early Career Teacher (ECT)', 'sort_order' => 8],
            ['slug' => 'ta', 'name' => 'Teaching Assistant (TA)', 'sort_order' => 9],
            ['slug' => 'lsa', 'name' => 'Learning Support Assistant (LSA)', 'sort_order' => 10],
            ['slug' => 'hlta', 'name' => 'HLTA', 'sort_order' => 11],
            ['slug' => 'dsl_deputy_dsl', 'name' => 'DSL / Deputy DSL', 'sort_order' => 12],
            ['slug' => 'pastoral_staff', 'name' => 'Pastoral Staff', 'sort_order' => 13],
            ['slug' => 'admin_staff', 'name' => 'Admin Staff', 'sort_order' => 14],
            ['slug' => 'site_staff', 'name' => 'Site Staff', 'sort_order' => 15],
        ];

        $teams = [
            ['slug' => 'slt', 'name' => 'Senior Leadership Team (SLT)', 'sort_order' => 1],
            ['slug' => 'teaching_staff', 'name' => 'Teaching Staff', 'sort_order' => 2],
            ['slug' => 'teaching_support_staff', 'name' => 'Teaching Support Staff', 'sort_order' => 3],
            ['slug' => 'safeguarding_pastoral_team', 'name' => 'Safeguarding & Pastoral Team', 'sort_order' => 4],
            ['slug' => 'administration_office_staff', 'name' => 'Administration & Office Staff', 'sort_order' => 5],
            ['slug' => 'site_facilities_team', 'name' => 'Site & Facilities Team', 'sort_order' => 6],
        ];

        $now = now();

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, ['created_at' => $now, 'updated_at' => $now]));
        }

        foreach ($teams as $team) {
            DB::table('teams')->insert(array_merge($team, ['created_at' => $now, 'updated_at' => $now]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
        Schema::dropIfExists('roles');
    }
};
